<?php
namespace App\Services;

use App\Models\UserProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected $apiKey;
    
    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }
    
    /**
     * Grade a calendar based on events and user profile
     *
     * @param array $events
     * @param UserProfile $userProfile
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function gradeCalendar($events, UserProfile $userProfile, $startDate, $endDate)
    {
        try {
            // Format events for the prompt
            $formattedEvents = $this->formatEventsForPrompt($events);
            
            // Get user preferences from profile
            $userPreferences = $this->getUserPreferencesFromProfile($userProfile);
            
            // Create the prompt
            $prompt = $this->createGradingPrompt($formattedEvents, $userPreferences, $startDate, $endDate);
            
            // Call OpenAI API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a calendar grading assistant that helps users improve their time management and productivity.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 1500,
            ]);
            
            if ($response->failed()) {
                Log::error('OpenAI API error: ' . $response->body());
                throw new \Exception('Failed to get response from OpenAI: ' . $response->status());
            }
            
            $result = $response->json();
            $gradingText = $result['choices'][0]['message']['content'];
            
            // Parse the grading text into structured data
            return $this->parseGradingResponse($gradingText);
        } catch (\Exception $e) {
            Log::error('Error in OpenAI service: ' . $e->getMessage());
            
            // Return a default grading result in case of error
            return $this->getDefaultGradingResult();
        }
    }
    
    /**
     * Format events for the grading prompt
     *
     * @param array $events
     * @return string
     */
    protected function formatEventsForPrompt($events)
    {
        $formattedEvents = '';
        
        foreach ($events as $index => $event) {
            $formattedEvents .= ($index + 1) . ". ";
            $formattedEvents .= $event['title'] . " - ";
            
            if ($event['all_day']) {
                $formattedEvents .= "All day";
            } else {
                $formattedEvents .= "From " . date('D, M j, g:i A', strtotime($event['start']));
                $formattedEvents .= " to " . date('g:i A', strtotime($event['end']));
            }
            
            if (!empty($event['calendar_name'])) {
                $formattedEvents .= " (Calendar: " . $event['calendar_name'] . ")";
            }
            
            $formattedEvents .= "\n";
        }
        
        return $formattedEvents;
    }
    
    /**
     * Get user preferences from profile
     *
     * @param UserProfile $userProfile
     * @return string
     */
    protected function getUserPreferencesFromProfile(UserProfile $userProfile)
    {
        $preferences = '';
        
        // Add work hours
        $preferences .= "Work hours: " . $userProfile->work_start_time . " to " . $userProfile->work_end_time . "\n";
        
        // Add work days
        $workDays = json_decode($userProfile->work_days, true) ?? [];
        $preferences .= "Work days: " . implode(', ', $workDays) . "\n";
        
        // Add focus time preferences
        $preferences .= "Focus time preference: " . $userProfile->focus_time_preference . "\n";
        
        // Add break time preferences
        $preferences .= "Break time preference: " . $userProfile->break_time_preference . "\n";
        
        // Add goals
        $goals = json_decode($userProfile->goals, true) ?? [];
        if (!empty($goals)) {
            $preferences .= "Goals:\n";
            foreach ($goals as $index => $goal) {
                $preferences .= "- " . $goal . "\n";
            }
        }
        
        return $preferences;
    }
    
    /**
     * Create the grading prompt
     *
     * @param string $formattedEvents
     * @param string $userPreferences
     * @param string $startDate
     * @param string $endDate
     * @return string
     */
    protected function createGradingPrompt($formattedEvents, $userPreferences, $startDate, $endDate)
    {
        $prompt = "I need you to grade a user's Google Calendar for one or more weeks within a specified period based on 5 optimized productivity rules, dynamically adjusted by user behavior and AI-driven insights.\n\n";

        $prompt .= "**Optimized Rules** (Dynamically Generated or Adjusted):\n";
        $prompt .= "- Default Rules (if no dynamic generation):\n";
        $prompt .= "  1. **Prioritize Non-Negotiables (Default Weight: 40%)**\n";
        $prompt .= "     - Ensure high-priority commitments are scheduled without conflicts.\n";
        $prompt .= "     - Scoring: 100 = All scheduled, no conflicts; 50 = Partial scheduling; 0 = Missing.\n";
        $prompt .= "  2. **Focus on Money-Making (Default Weight: 25%)**\n";
        $prompt .= "     - Schedule 3+ money-making activities.\n";
        $prompt .= "     - Scoring: 100 = 3+ activities; 50 = 1-2 activities; 0 = None.\n";
        $prompt .= "  3. **Invest in Growth (Default Weight: 20%)**\n";
        $prompt .= "     - Include 2-4 hours for learning, reflection, journaling, or planning.\n";
        $prompt .= "     - Scoring: 100 = 2-4 hours; 50 = <2 hours; 0 = None.\n";
        $prompt .= "  4. **Design for Motivation (Default Weight: 10%)**\n";
        $prompt .= "     - Include one energy-renewal activity with no disruptions.\n";
        $prompt .= "     - Scoring: 100 = 1+ activity, no disruptions; 50 = Minor disruptions; 0 = None.\n";
        $prompt .= "  5. **Eliminate Waste (Default Weight: 5%)**\n";
        $prompt .= "     - Remove one low-value activity.\n";
        $prompt .= "     - Scoring: 100 = 1+ removed; 50 = Identified; 0 = No evaluation.\n\n";

        $prompt .= "**User Profile Information**:\n";
        $prompt .= $userPreferences . "\n";

        $prompt .= "**Calendar Data**:\n";
        $prompt .= $formattedEvents . "\n";

        $prompt .= "**Evaluation Parameters**:\n";
        $prompt .= "- Evaluation Period: " . date('F j', strtotime($startDate)) . " to " . date('F j, Y', strtotime($endDate)) . "\n\n";

        $prompt .= "**Instructions**:\n";
        $prompt .= "1. Validate inputs and normalize rule weights to 100%.\n";
        $prompt .= "2. Parse calendar data efficiently and identify recurring patterns.\n";
        $prompt .= "3. Evaluate each week and calculate overall grade using adjusted weights.\n";
        $prompt .= "4. Identify 2-3 strengths and improvement areas.\n";
        $prompt .= "5. Use predictive modeling to suggest optimal schedules.\n\n";

        $prompt .= "**Output Format** (JSON):\n";
        $prompt .= "{\n";
        $prompt .= "  \"evaluations\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"week_range\": \"[Start] - [End]\",\n";
        $prompt .= "      \"overall_grade\": [0-100],\n";
        $prompt .= "      \"rule_grades\": {\n";
        $prompt .= "        \"1\": [0-100],\n";
        $prompt .= "        \"2\": [0-100],\n";
        $prompt .= "        \"3\": [0-100],\n";
        $prompt .= "        \"4\": { \"score\": [0-100], \"sub_metrics\": { \"Z\": [0-100] } },\n";
        $prompt .= "        \"5\": [0-100]\n";
        $prompt .= "      },\n";
        $prompt .= "      \"calendar_hash\": \"[hash]\",\n";
        $prompt .= "      \"strengths\": [\"strength1\", \"strength2\"],\n";
        $prompt .= "      \"improvement_areas\": [\"area1\", \"area2\"],\n";
        $prompt .= "      \"top_recommendation\": \"[recommendation]\",\n";
        $prompt .= "      \"recommendations\": [\"rec1\", \"rec2\", \"rec3\"],\n";
        $prompt .= "      \"details_link\": \"[link]\",\n";
        $prompt .= "      \"note\": null\n";
        $prompt .= "    }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"pagination\": {\n";
        $prompt .= "    \"total_entries\": [number],\n";
        $prompt .= "    \"current_page\": [number],\n";
        $prompt .= "    \"entries_per_page\": [number]\n";
        $prompt .= "  },\n";
        $prompt .= "  \"radar_chart\": {\n";
        $prompt .= "    \"current_week\": {\n";
        $prompt .= "      \"Non-negotiables\": [0-100],\n";
        $prompt .= "      \"Money-making activities\": [0-100],\n";
        $prompt .= "      \"Reflection time\": [0-100],\n";
        $prompt .= "      \"Learning time\": [0-100],\n";
        $prompt .= "      \"Planning time\": [0-100],\n";
        $prompt .= "      \"Time protection\": [0-100]\n";
        $prompt .= "    },\n";
        $prompt .= "    \"previous_week\": {\n";
        $prompt .= "      \"Non-negotiables\": [0-100],\n";
        $prompt .= "      \"Money-making activities\": [0-100],\n";
        $prompt .= "      \"Reflection time\": [0-100],\n";
        $prompt .= "      \"Learning time\": [0-100],\n";
        $prompt .= "      \"Planning time\": [0-100],\n";
        $prompt .= "      \"Time protection\": [0-100]\n";
        $prompt .= "    },\n";
        $prompt .= "    \"descriptions\": {\n";
        $prompt .= "      \"Non-negotiables\": \"[description]\",\n";
        $prompt .= "      \"Money-making activities\": \"[description]\",\n";
        $prompt .= "      \"Reflection time\": \"[description]\",\n";
        $prompt .= "      \"Learning time\": \"[description]\",\n";
        $prompt .= "      \"Planning time\": \"[description]\",\n";
        $prompt .= "      \"Time protection\": \"[description]\"\n";
        $prompt .= "    }\n";
        $prompt .= "  },\n";
        $prompt .= "  \"visualizations\": {\n";
        $prompt .= "    \"productivity_heatmap\": {\n";
        $prompt .= "      \"9 AM-12 PM\": \"[productivity level]\",\n";
        $prompt .= "      \"1 PM-3 PM\": \"[productivity level]\"\n";
        $prompt .= "    }\n";
        $prompt .= "  },\n";
        $prompt .= "  \"gamification\": {\n";
        $prompt .= "    \"badges\": [\"badge1\"],\n";
        $prompt .= "    \"streak\": [number]\n";
        $prompt .= "  }\n";
        $prompt .= "}\n";

        return $prompt;
    }
    
    /**
     * Parse the grading response from OpenAI
     *
     * @param string $gradingText
     * @return array
     */
    protected function parseGradingResponse($gradingText)
    {
        try {
            // Parse the JSON response
            $result = json_decode($gradingText, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from OpenAI');
            }
            
            // Get the first evaluation (since we're currently only grading one week)
            $evaluation = $result['evaluations'][0] ?? null;
            
            if (!$evaluation) {
                throw new \Exception('No evaluation found in response');
            }
            
            // Convert the numeric grade to a letter grade
            $letterGrade = $this->convertNumericToLetterGrade($evaluation['overall_grade']);
            
            return [
                'overall_grade' => $evaluation['overall_grade'],
                'letter' => $letterGrade,
                'summary' => $evaluation['note'] ?? '',
                'strengths' => $evaluation['strengths'],
                'improvements' => $evaluation['improvement_areas'],
                'recommendations' => $evaluation['recommendations'],
                'rule_grades' => $evaluation['rule_grades'],
                'radar_chart' => $result['radar_chart'],
                'visualizations' => $result['visualizations'],
                'gamification' => $result['gamification']
            ];
        } catch (\Exception $e) {
            Log::error('Error parsing grading response: ' . $e->getMessage());
            return $this->getDefaultGradingResult();
        }
    }
    
    /**
     * Convert numeric grade to letter grade
     *
     * @param float $grade
     * @return string
     */
    protected function convertNumericToLetterGrade($grade)
    {
        if ($grade >= 90) return 'A';
        if ($grade >= 80) return 'B';
        if ($grade >= 70) return 'C';
        if ($grade >= 60) return 'D';
        return 'F';
    }
    
    /**
     * Get a default grading result in case of error
     *
     * @return array
     */
    protected function getDefaultGradingResult()
    {
        return [
            'overall_grade' => 70,
            'letter' => 'C',
            'summary' => 'Unable to generate a detailed analysis at this time. Here is a default grade.',
            'strengths' => [
                'You have events in your calendar which is a good start for time management.',
                'You are using a calendar system to track your activities.',
                'You are seeking feedback on your calendar organization.'
            ],
            'improvements' => [
                'Consider adding more structure to your calendar.',
                'Make sure to include breaks between intensive work sessions.',
                'Align your calendar events with your stated goals.'
            ],
            'recommendations' => [
                'Block dedicated focus time for important tasks.',
                'Schedule regular breaks to avoid burnout.',
                'Review your calendar weekly to ensure it aligns with your priorities.'
            ],
            'rule_grades' => [
                '1' => 70,
                '2' => 70,
                '3' => 70,
                '4' => 70,
                '5' => 70
            ],
            'radar_chart' => [
                'current_week' => [
                    'Non-negotiables' => 70,
                    'Money-making activities' => 70,
                    'Reflection time' => 70,
                    'Learning time' => 70,
                    'Planning time' => 70,
                    'Time protection' => 70
                ],
                'previous_week' => [
                    'Non-negotiables' => 70,
                    'Money-making activities' => 70,
                    'Reflection time' => 70,
                    'Learning time' => 70,
                    'Planning time' => 70,
                    'Time protection' => 70
                ],
                'descriptions' => [
                    'Non-negotiables' => 'Ensures your top priorities are scheduled without conflicts',
                    'Money-making activities' => 'Focuses on scheduling income-generating tasks',
                    'Reflection time' => 'Time for journaling and self-assessment',
                    'Learning time' => 'Dedicated to education and skill-building',
                    'Planning time' => 'Time for weekly and monthly planning',
                    'Time protection' => 'Prevents interruptions to key activities'
                ]
            ],
            'visualizations' => [
                'productivity_heatmap' => [
                    '9 AM-12 PM' => 'Medium productivity',
                    '1 PM-3 PM' => 'Medium productivity'
                ]
            ],
            'gamification' => [
                'badges' => [],
                'streak' => 0
            ]
        ];
    }
}
