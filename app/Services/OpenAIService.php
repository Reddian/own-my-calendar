<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\UserProfile;
use Carbon\Carbon;

class OpenAIService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openai.com/v1';
    protected $model = 'gpt-4';

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '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');
    }

    /**
     * Grade a calendar based on the A-Z calendar rules
     *
     * @param array $calendarData Calendar events data
     * @param UserProfile $userProfile User profile with goals and activities
     * @param string $startDate Start date of the week
     * @param string $endDate End date of the week
     * @return array Grading results
     */
    public function gradeCalendar($calendarData, $userProfile, $startDate, $endDate)
    {
        try {
            // Format calendar data for analysis
            $formattedCalendar = $this->formatCalendarForAnalysis($calendarData, $startDate, $endDate);
            
            // Prepare the prompt with calendar rules and user profile
            $prompt = $this->prepareGradingPrompt($formattedCalendar, $userProfile, $startDate, $endDate);
            
            // Call OpenAI API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert calendar analyst and productivity coach. Your task is to grade a user\'s calendar based on specific rules and provide constructive feedback.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                $aiResponse = $result['choices'][0]['message']['content'];
                
                // Parse the AI response into structured grading data
                return $this->parseGradingResponse($aiResponse);
            } else {
                Log::error('OpenAI API error: ' . $response->body());
                throw new \Exception('Failed to get response from OpenAI: ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Calendar grading error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Format calendar data for analysis
     *
     * @param array $calendarData Calendar events data
     * @param string $startDate Start date of the week
     * @param string $endDate End date of the week
     * @return string Formatted calendar data
     */
    protected function formatCalendarForAnalysis($calendarData, $startDate, $endDate)
    {
        $startDateObj = Carbon::parse($startDate);
        $endDateObj = Carbon::parse($endDate);
        
        $formattedCalendar = "Calendar for week of {$startDate} to {$endDate}:\n\n";
        
        // Group events by day
        $eventsByDay = [];
        foreach ($calendarData as $event) {
            $eventDate = Carbon::parse($event['start'])->format('Y-m-d');
            if (!isset($eventsByDay[$eventDate])) {
                $eventsByDay[$eventDate] = [];
            }
            $eventsByDay[$eventDate][] = $event;
        }
        
        // Format each day's events
        for ($date = clone $startDateObj; $date <= $endDateObj; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dayName = $date->format('l');
            
            $formattedCalendar .= "{$dayName} ({$dateStr}):\n";
            
            if (isset($eventsByDay[$dateStr]) && count($eventsByDay[$dateStr]) > 0) {
                foreach ($eventsByDay[$dateStr] as $event) {
                    $title = $event['title'];
                    $time = $event['all_day'] 
                        ? 'All day' 
                        : Carbon::parse($event['start'])->format('H:i') . ' - ' . Carbon::parse($event['end'])->format('H:i');
                    
                    $formattedCalendar .= "- {$time}: {$title}\n";
                    
                    if (!empty($event['description'])) {
                        $formattedCalendar .= "  Description: {$event['description']}\n";
                    }
                    
                    if (!empty($event['location'])) {
                        $formattedCalendar .= "  Location: {$event['location']}\n";
                    }
                }
            } else {
                $formattedCalendar .= "- No events scheduled\n";
            }
            
            $formattedCalendar .= "\n";
        }
        
        return $formattedCalendar;
    }

    /**
     * Prepare the prompt for calendar grading
     *
     * @param string $formattedCalendar Formatted calendar data
     * @param UserProfile $userProfile User profile with goals and activities
     * @param string $startDate Start date of the week
     * @param string $endDate End date of the week
     * @return string Complete prompt for OpenAI
     */
    protected function prepareGradingPrompt($formattedCalendar, $userProfile, $startDate, $endDate)
    {
        $prompt = <<<EOT
I need you to grade a user's Google Calendar for the week of {$startDate} to {$endDate} based on the following calendar rules (A-Z):

A. Start with Non-Negotiables
- Identify priorities (e.g., family time, work commitments, health).
- Write them down and ensure they are scheduled into your calendar.

B. Determine Where You Will Sacrifice
- Reflect on areas where you can be flexible or let go of commitments.
- List them out (e.g., skipping a social gathering, reducing screen time).

C. Money-Making Activities (Top 5)
- Examples: W2, REI $ activities, Affiliate marketing, Creating and selling digital content, Crypto

D. Fill in Reflection Time / Content-Making Time
- Allocate blocks during the week for self-reflection and content creation journal, document your time

E. Learning Time
- Schedule 1-2 hours a week for online courses or reading.

F. Planning / Me Time / Manifest Time
- Dedicate 4 hours (e.g., Sunday) for comprehensive planning – week/month; spend your last hour with your spouse

G. Remove One Activity Per Month That Does Not Serve You
- Evaluate tasks and eliminate one that feels redundant or are not helping you

H. Self-Assessment of Activities
- Regularly assess whether activities align with your goals.
- 1. What do I want?
- 2. What do they want?
- 3. Who has the thing they want?

I. Protect Your Time with Intention
- Create boundaries around your work and personal time.

J. Honest Evaluation of Desire
- Ask yourself: "Do I really want this goal?"

K. Journal Your Progress
- Track achievements and lessons learned throughout your journey.

L. Live and Die by Your Calendar
- Schedule everything important and follow it diligently.

M. Manage Your Life / Manage Your People
- Organize responsibilities and delegate where possible.

N. Never Deviate
- Stay committed to your established priorities and routines.

O. Organize Daily
- Aim to accomplish "5 birds with 1 stone" by combining tasks.

P. Purpose of This Meeting
- Define the goals and outcomes of your meetings.

Q. Question Your Own Beliefs
- Challenge assumptions that may limit your growth.

R. Reject All Requests That Are Not in Advance
- Commit to a schedule planned a week in advance.

S. Slowly Build a Week That You Are Excited About
- Design your week with motivating activities.

T. Train Yourself to Think 6-12 Months Out
- Develop a long-term vision to guide short-term actions.

U. Understand That This Is the Most Important Thing You Will Do All Week
- Prioritize personal development as essential.

V. (E)"valuate" Yourself
- Regularly assess your performance and progress.

W. Without Your Calendar, You Won't Know What to Blame
- Use your calendar as a tool for accountability.

X. X-Factor
- Identify unique qualities that set you apart.

Y. Yearn for Less
- Focus on quality over quantity.

Z. Zenith (Your Mount Everest)
- Define your ultimate goal or aspiration.

User Profile Information:
- Mount Everest (Ultimate Goal): {$userProfile->mt_everest}
- Money-Making Activities: {$userProfile->money_making_activities}
- Energy Renewal Activities: {$userProfile->energy_renewal_activities}

Here is the user's calendar for the week:

{$formattedCalendar}

Please analyze this calendar and provide:
1. An overall grade (0-100) for how well the calendar follows the rules
2. Individual grades (0-100) for each of the A-Z rules
3. 3-5 key strengths of the calendar
4. 3-5 areas for improvement
5. Specific recommendations for better calendar management

Format your response as JSON with the following structure:
{
  "overall_grade": 85,
  "rule_grades": {
    "A": 90,
    "B": 80,
    ...
  },
  "strengths": "Strength 1. Strength 2. Strength 3.",
  "improvement_areas": "Area 1. Area 2. Area 3.",
  "recommendations": "Recommendation 1. Recommendation 2. Recommendation 3."
}
EOT;

        return $prompt;
    }

    /**
     * Parse the AI response into structured grading data
     *
     * @param string $aiResponse Response from OpenAI
     * @return array Structured grading data
     */
    protected function parseGradingResponse($aiResponse)
    {
        try {
            // Extract JSON from the response
            $jsonStart = strpos($aiResponse, '{');
            $jsonEnd = strrpos($aiResponse, '}');
            
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonString = substr($aiResponse, $jsonStart, $jsonEnd - $jsonStart + 1);
                $gradingData = json_decode($jsonString, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $gradingData;
                }
            }
            
            // Fallback if JSON parsing fails
            Log::warning('Failed to parse AI response as JSON: ' . $aiResponse);
            
            // Create a default structure with extracted information
            return [
                'overall_grade' => $this->extractOverallGrade($aiResponse),
                'rule_grades' => $this->extractRuleGrades($aiResponse),
                'strengths' => $this->extractSection($aiResponse, 'strengths', 'Strengths'),
                'improvement_areas' => $this->extractSection($aiResponse, 'improvement areas', 'Areas for Improvement'),
                'recommendations' => $this->extractSection($aiResponse, 'recommendations', 'Recommendations'),
            ];
        } catch (\Exception $e) {
            Log::error('Error parsing AI response: ' . $e->getMessage());
            throw new \Exception('Failed to parse grading response: ' . $e->getMessage());
        }
    }

    /**
     * Extract overall grade from text response
     *
     * @param string $response AI response text
     * @return float Overall grade
     */
    protected function extractOverallGrade($response)
    {
        $patterns = [
            '/overall[^0-9]*([0-9]{1,3})/',
            '/grade[^0-9]*([0-9]{1,3})/',
            '/score[^0-9]*([0-9]{1,3})/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, strtolower($response), $matches)) {
                return min(100, max(0, (float)$matches[1]));
            }
        }
        
        return 70; // Default grade if extraction fails
    }

    /**
     * Extract rule grades from text response
     *
     * @param string $response AI response text
     * @return array Rule grades
     */
    protected function extractRuleGrades($response)
    {
        $ruleGrades = [];
        $rules = range('A', 'Z');
        
        foreach ($rules as $rule) {
            $pattern = "/{$rule}[^0-9]*([0-9]{1,3})/i";
            if (preg_match($pattern, $response, $matches)) {
                $ruleGrades[$rule] = min(100, max(0, (float)$matches[1]));
            } else {
                $ruleGrades[$rule] = 70; // Default grade if extraction fails
            }
        }
        
        return $ruleGrades;
    }

    /**
     * Extract a section from text response
     *
     * @param string $response AI response text
     * @param string $sectionKey Section key to look for
     * @param string $sectionTitle Alternative section title to look for
     * @return string Extracted section text
     */
    protected function extractSection($response, $sectionKey, $sectionTitle)
    {
        $patterns = [
            "/{$sectionKey}[^\n]*:([^\n]*)/i",
            "/{$sectionTitle}[^\n]*:([^\n]*)/i",
            "/{$sectionKey}[^:]*:([^\\n]*(?:\\n(?!\\n)[^:]*)*)/i",
            "/{$sectionTitle}[^:]*:([^\\n]*(?:\\n(?!\\n)[^:]*)*)/i",
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $response, $matches)) {
                return trim($matches[1]);
            }
        }
        
        return "No {$sectionTitle} information available.";
    }
}
