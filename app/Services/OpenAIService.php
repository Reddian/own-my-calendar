<?php
namespace App\Services;

use App\Models\UserProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException; // Import RequestException

class OpenAIService
{
    protected $apiKey;
    
    public function __construct()
    {
        $this->apiKey = config("services.openai.api_key");
        if (empty($this->apiKey)) {
            Log::error("OpenAI API key is not configured.");
            // Optionally throw an exception or handle appropriately
        }
    }
    
    /**
     * Grade a calendar based on events and user profile
     *
     * @param array $events
     * @param UserProfile $userProfile
     * @param string $startDate
     * @param string $endDate
     * @return array
     * @throws \Exception If grading fails critically
     */
    public function gradeCalendar($events, UserProfile $userProfile, $startDate, $endDate)
    {
        $userId = $userProfile->user_id; // Get user ID for logging
        Log::info("Starting OpenAI calendar grading process", ["user_id" => $userId]);
        try {
            // Format events for the prompt
            $formattedEvents = $this->formatEventsForPrompt($events);
            Log::debug("Formatted events for prompt", ["user_id" => $userId, "event_count" => count($events)]);
            
            // Get user preferences from profile
            $userPreferences = $this->getUserPreferencesFromProfile($userProfile);
            Log::debug("Formatted user preferences for prompt", ["user_id" => $userId]);
            
            // Create the prompt
            $prompt = $this->createGradingPrompt($formattedEvents, $userPreferences, $startDate, $endDate);
            // Log prompt length instead of full prompt for brevity/security
            Log::info("Generated OpenAI prompt", ["user_id" => $userId, "prompt_length" => strlen($prompt)]); 

            // Call OpenAI API
            Log::info("Sending request to OpenAI API", ["user_id" => $userId, "model" => "gpt-4"]);
            $response = null; // Initialize response
            try {
                $response = Http::withHeaders([
                    "Authorization" => "Bearer " . $this->apiKey,
                    "Content-Type" => "application/json",
                ])->timeout(120) // Increase timeout to 120 seconds
                  ->post("https://api.openai.com/v1/chat/completions", [
                    "model" => "gpt-4",
                    "messages" => [
                        [
                            "role" => "system",
                            "content" => "You are a calendar grading assistant that helps users improve their time management and productivity."
                        ],
                        [
                            "role" => "user",
                            "content" => $prompt
                        ]
                    ],
                    "temperature" => 0.7,
                    "max_tokens" => 1500, // Consider adjusting if responses are truncated
                ]);

                // Check for HTTP errors
                $response->throw(); // Throws RequestException on 4xx or 5xx
                Log::info("Received successful response from OpenAI API", ["user_id" => $userId]);

            } catch (RequestException $e) {
                // Log detailed HTTP client errors
                Log::error("OpenAI API HTTP request failed", [
                    "user_id" => $userId,
                    "status_code" => $e->response ? $e->response->status() : null,
                    "response_body" => $e->response ? $e->response->body() : null,
                    "exception_message" => $e->getMessage(),
                    // "exception_trace" => $e->getTraceAsString() // Optional: trace can be very long
                ]);
                // Re-throw a more specific exception or handle as needed
                throw new \Exception("Failed to communicate with OpenAI API: " . ($e->response ? $e->response->status() : "Network Error"), $e->getCode(), $e);
            }
            
            $result = $response->json();
            
            // Check for errors within the OpenAI response structure
            if (isset($result["error"])) {
                Log::error("OpenAI API returned an error in the response", [
                    "user_id" => $userId,
                    "openai_error" => $result["error"]
                ]);
                throw new \Exception("OpenAI API Error: " . ($result["error"]["message"] ?? "Unknown error"));
            }
            
            // Ensure the expected structure exists
            if (!isset($result["choices"][0]["message"]["content"])) {
                 Log::error("Unexpected OpenAI API response structure", ["user_id" => $userId, "response" => $result]);
                 throw new \Exception("Invalid response structure from OpenAI API.");
            }
            
            $gradingText = $result["choices"][0]["message"]["content"];
            Log::info("Received grading content from OpenAI", ["user_id" => $userId, "content_length" => strlen($gradingText)]);
            
            // Parse the grading text into structured data
            $parsedResult = $this->parseGradingResponse($gradingText, $userId);
            Log::info("Successfully parsed grading response", ["user_id" => $userId]);
            
            return $parsedResult;
            
        } catch (\Exception $e) {
            // Log the exception that occurred anywhere in the process
            Log::error("Error during OpenAI calendar grading process", [
                "user_id" => $userId,
                "exception_message" => $e->getMessage(),
                "exception_code" => $e->getCode(),
                "exception_trace" => $e->getTraceAsString() // Include stack trace for detailed debugging
            ]);
            
            // Re-throw the exception to be caught by the controller
            throw $e; 
            // Removed default result return, let controller handle the exception
        }
    }
    
    // ... (formatEventsForPrompt, getUserPreferencesFromProfile, createGradingPrompt remain the same) ...
    protected function formatEventsForPrompt($events)
    {
        $formattedEvents = ""; // Corrected: Use equals sign for initialization
        
        foreach ($events as $index => $event) {
            $formattedEvents .= ($index + 1) . ". ";
            $formattedEvents .= ($event["title"] ?? "No Title") . " - "; // Handle missing title
            
            if ($event["all_day"]) {
                $formattedEvents .= "All day on " . date("D, M j", strtotime($event["start"] ?? "now")); // Add date for all-day
            } else {
                $startStr = $event["start"] ? date("D, M j, g:i A", strtotime($event["start"])) : "[Unknown Start]";
                $endStr = $event["end"] ? date("g:i A", strtotime($event["end"])) : "[Unknown End]";
                $formattedEvents .= "From " . $startStr . " to " . $endStr;
            }
            
            if (!empty($event["calendar_name"])) {
                $formattedEvents .= " (Calendar: " . $event["calendar_name"] . ")";
            }
            
            $formattedEvents .= "\n";
        }
        
        return $formattedEvents;
    }
    
    protected function getUserPreferencesFromProfile(UserProfile $userProfile)
    {
        $preferences = ""; // Corrected: Use equals sign for initialization
        
        // Add profile data safely, checking for nulls
        $preferences .= "Mt Everest Goal: " . ($userProfile->mt_everest ?? "Not specified") . "\n";
        $preferences .= "Money Making Activities: " . ($userProfile->money_making_activities ?? "Not specified") . "\n";
        $preferences .= "Energy Renewal Activities: " . ($userProfile->energy_renewal_activities ?? "Not specified") . "\n";
        
        // Add calendar preferences if they exist
        $calendarPrefs = $userProfile->calendar_preferences ?? [];
        if (!empty($calendarPrefs)) {
            $preferences .= "Calendar Preferences:\n";
            foreach ($calendarPrefs as $key => $value) {
                 $preferences .= "- " . ucfirst(str_replace("_", " ", $key)) . ": " . $value . "\n";
            }
        }
        
        return $preferences;
    }
    
    protected function createGradingPrompt($formattedEvents, $userPreferences, $startDate, $endDate)
    {
        // Using a simplified prompt structure for clarity and focusing on core grading
        $prompt = <<<PROMPT
You are an expert calendar grading assistant. Analyze the provided calendar events based on the user's profile and the following productivity rules. Provide the output strictly in the specified JSON format.

**User Profile:**
{$userPreferences}

**Calendar Events for {$startDate} to {$endDate}:**
{$formattedEvents}

**Productivity Rules:**
1.  **Prioritize Non-Negotiables (Weight: 40%):** Ensure high-priority commitments (related to 'Mt Everest Goal' or explicitly marked) are scheduled without conflicts. Score: 100=All scheduled, no conflicts; 50=Partial/conflicts; 0=Missing.
2.  **Focus on Money-Making (Weight: 25%):** Schedule 3+ activities related to 'Money Making Activities'. Score: 100=3+; 50=1-2; 0=None.
3.  **Invest in Growth (Weight: 20%):** Include 2-4 hours total for learning, reflection, journaling, or planning. Score: 100=2-4 hrs; 50=<2 hrs; 0=None.
4.  **Design for Motivation (Weight: 10%):** Include one 'Energy Renewal Activity' with no disruptions. Score: 100=1+ activity, no disruptions; 50=Minor disruptions; 0=None.
5.  **Eliminate Waste (Weight: 5%):** Identify potential low-value activities that could be removed or delegated. Score: 100=1+ identified; 50=Potential identified; 0=No evaluation.

**Instructions:**
1.  Calculate a score (0-100) for each rule based *only* on the provided events and profile.
2.  Calculate the weighted overall grade.
3.  Provide 2-3 specific strengths observed in the calendar.
4.  Provide 2-3 specific areas for improvement.
5.  Provide 2-3 actionable recommendations based on the improvements.
6.  Output *only* the JSON structure below, filling in the values. Do not include any introductory text, explanations, or markdown formatting outside the JSON structure.

**Output JSON Format:**
{
  "overall_grade": <number 0-100>,
  "rule_grades": {
    "1": <number 0-100>,
    "2": <number 0-100>,
    "3": <number 0-100>,
    "4": <number 0-100>,
    "5": <number 0-100>
  },
  "strengths": [
    "<string strength 1>",
    "<string strength 2>"
  ],
  "improvement_areas": [
    "<string improvement area 1>",
    "<string improvement area 2>"
  ],
  "recommendations": [
    {
        "title": "<string recommendation title 1>",
        "description": "<string recommendation description 1>"
    },
    {
        "title": "<string recommendation title 2>",
        "description": "<string recommendation description 2>"
    }
  ]
}
PROMPT;

        return $prompt;
    }

    
    /**
     * Parse the grading response from OpenAI
     *
     * @param string $gradingText
     * @param int $userId For logging
     * @return array
     * @throws \Exception If parsing fails
     */
    protected function parseGradingResponse($gradingText, $userId)
    {
        try {
            // Attempt to find JSON block if there's extraneous text (though prompt requests only JSON)
            $jsonStart = strpos($gradingText, "{");
            $jsonEnd = strrpos($gradingText, "}");
            if ($jsonStart === false || $jsonEnd === false) {
                throw new \Exception("Could not find JSON structure in OpenAI response.");
            }
            $jsonString = substr($gradingText, $jsonStart, $jsonEnd - $jsonStart + 1);
            
            // Parse the JSON response
            $result = json_decode($jsonString, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("JSON parsing error", ["user_id" => $userId, "error" => json_last_error_msg(), "text_received" => $gradingText]);
                throw new \Exception("Invalid JSON response from OpenAI: " . json_last_error_msg());
            }
            
            // Validate essential fields exist
            if (!isset($result["overall_grade"]) || !isset($result["rule_grades"])) {
                 Log::error("Missing essential fields in parsed OpenAI JSON", ["user_id" => $userId, "parsed_json" => $result]);
                 throw new \Exception("Parsed JSON response from OpenAI is missing required fields.");
            }
            
            // Return only the expected fields for saving
            return [
                "overall_grade" => $result["overall_grade"] ?? 0,
                "rule_grades" => $result["rule_grades"] ?? [],
                "strengths" => $result["strengths"] ?? [],
                "improvement_areas" => $result["improvement_areas"] ?? [],
                "recommendations" => $result["recommendations"] ?? [],
                // Add other fields if needed, e.g., summary, letter grade
            ];
        } catch (\Exception $e) {
            Log::error("Error parsing OpenAI grading response", [
                "user_id" => $userId,
                "exception_message" => $e->getMessage(),
                "raw_response_text" => $gradingText, // Log the raw text that failed parsing
                // "exception_trace" => $e->getTraceAsString() // Optional trace
            ]);
            // Re-throw the exception to be handled by the controller
            throw $e;
        }
    }
    
    // Removed convertNumericToLetterGrade and getDefaultGradingResult as errors are now thrown
}

