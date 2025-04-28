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
        $prompt = "Please grade my calendar for the week of " . date('F j', strtotime($startDate)) . 
                  " to " . date('F j, Y', strtotime($endDate)) . ".\n\n";
        
        $prompt .= "Here are my preferences:\n" . $userPreferences . "\n";
        
        $prompt .= "Here are my calendar events for this week:\n" . $formattedEvents . "\n";
        
        $prompt .= "Please grade my calendar based on the following criteria:\n";
        $prompt .= "1. Work-life balance\n";
        $prompt .= "2. Focus time allocation\n";
        $prompt .= "3. Break scheduling\n";
        $prompt .= "4. Alignment with goals\n";
        $prompt .= "5. Overall time management\n\n";
        
        $prompt .= "For each criterion, assign a letter grade (A, B, C, D, or F) and provide a brief explanation.\n";
        $prompt .= "Then, provide an overall grade (0-100 and letter grade), a summary, 3 strengths, 3 areas for improvement, and 3 specific recommendations.\n";
        $prompt .= "Format your response as follows:\n";
        $prompt .= "OVERALL_GRADE: [0-100]\n";
        $prompt .= "LETTER_GRADE: [A-F]\n";
        $prompt .= "SUMMARY: [Brief summary]\n";
        $prompt .= "STRENGTHS:\n- [Strength 1]\n- [Strength 2]\n- [Strength 3]\n";
        $prompt .= "IMPROVEMENTS:\n- [Improvement 1]\n- [Improvement 2]\n- [Improvement 3]\n";
        $prompt .= "RECOMMENDATIONS:\n- [Recommendation 1]\n- [Recommendation 2]\n- [Recommendation 3]\n";
        $prompt .= "RULE_GRADES:\n";
        $prompt .= "- Work-life balance: [Grade]\n";
        $prompt .= "- Focus time allocation: [Grade]\n";
        $prompt .= "- Break scheduling: [Grade]\n";
        $prompt .= "- Alignment with goals: [Grade]\n";
        $prompt .= "- Overall time management: [Grade]\n";
        
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
        $result = [
            'overall_grade' => 0,
            'letter' => 'F',
            'summary' => '',
            'strengths' => [],
            'improvements' => [],
            'recommendations' => [],
            'rule_grades' => []
        ];
        
        // Extract overall grade
        if (preg_match('/OVERALL_GRADE:\s*(\d+)/i', $gradingText, $matches)) {
            $result['overall_grade'] = (int) $matches[1];
        }
        
        // Extract letter grade
        if (preg_match('/LETTER_GRADE:\s*([A-F])/i', $gradingText, $matches)) {
            $result['letter'] = strtoupper($matches[1]);
        }
        
        // Extract summary
        if (preg_match('/SUMMARY:\s*(.*?)(?=STRENGTHS:|$)/is', $gradingText, $matches)) {
            $result['summary'] = trim($matches[1]);
        }
        
        // Extract strengths
        if (preg_match('/STRENGTHS:(.*?)(?=IMPROVEMENTS:|$)/is', $gradingText, $matches)) {
            preg_match_all('/- (.*?)(?=\n|$)/s', $matches[1], $strengthMatches);
            $result['strengths'] = array_map('trim', $strengthMatches[1]);
        }
        
        // Extract improvements
        if (preg_match('/IMPROVEMENTS:(.*?)(?=RECOMMENDATIONS:|$)/is', $gradingText, $matches)) {
            preg_match_all('/- (.*?)(?=\n|$)/s', $matches[1], $improvementMatches);
            $result['improvements'] = array_map('trim', $improvementMatches[1]);
        }
        
        // Extract recommendations
        if (preg_match('/RECOMMENDATIONS:(.*?)(?=RULE_GRADES:|$)/is', $gradingText, $matches)) {
            preg_match_all('/- (.*?)(?=\n|$)/s', $matches[1], $recommendationMatches);
            $result['recommendations'] = array_map('trim', $recommendationMatches[1]);
        }
        
        // Extract rule grades
        if (preg_match('/RULE_GRADES:(.*?)$/is', $gradingText, $matches)) {
            preg_match_all('/- (.*?):\s*([A-F])/i', $matches[1], $ruleMatches, PREG_SET_ORDER);
            
            foreach ($ruleMatches as $ruleMatch) {
                $ruleName = trim($ruleMatch[1]);
                $ruleGrade = strtoupper($ruleMatch[2]);
                
                $result['rule_grades'][] = [
                    'name' => $ruleName,
                    'grade' => $ruleGrade
                ];
            }
        }
        
        return $result;
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
                ['name' => 'Work-life balance', 'grade' => 'C'],
                ['name' => 'Focus time allocation', 'grade' => 'C'],
                ['name' => 'Break scheduling', 'grade' => 'C'],
                ['name' => 'Alignment with goals', 'grade' => 'C'],
                ['name' => 'Overall time management', 'grade' => 'C']
            ]
        ];
    }
}
