<?php

namespace App\Console\Commands;

use App\Mail\WeeklyGradeEmail;
use App\Mail\WeeklyReminderEmail;
use App\Models\CalendarGrade;
use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWeeklyEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:send-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly grade and reminder emails based on user notification settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to send weekly emails...');
        
        // Process weekly grade emails
        $this->sendWeeklyGradeEmails();
        
        // Process weekly reminder emails
        $this->sendWeeklyReminderEmails();
        
        $this->info('Weekly emails sending completed.');
        
        return Command::SUCCESS;
    }
    
    /**
     * Send weekly grade emails to users who have enabled this notification.
     */
    private function sendWeeklyGradeEmails()
    {
        $this->info('Processing weekly grade emails...');
        
        // Get all notification settings where weekly grade is enabled
        $notificationSettings = NotificationSetting::where('weekly_grade', true)->get();
        
        $emailsSent = 0;
        
        foreach ($notificationSettings as $settings) {
            try {
                // Check if it's time to send the email based on user's timezone and preferences
                if ($settings->shouldSendWeeklyGrade()) {
                    $user = User::find($settings->user_id);
                    
                    if (!$user) {
                        continue;
                    }
                    
                    // Get the most recent grade for this user
                    $latestGrade = CalendarGrade::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    if ($latestGrade) {
                        // Send the email
                        Mail::to($user->email)->send(new WeeklyGradeEmail($user, $latestGrade));
                        
                        // Update the last sent timestamp
                        $settings->last_grade_sent_at = now();
                        $settings->save();
                        
                        $emailsSent++;
                        
                        $this->info("Sent weekly grade email to {$user->email}");
                    } else {
                        $this->warn("No grades found for user {$user->email}, skipping weekly grade email");
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error sending weekly grade email: ' . $e->getMessage());
                $this->error('Error sending weekly grade email: ' . $e->getMessage());
            }
        }
        
        $this->info("Sent {$emailsSent} weekly grade emails");
    }
    
    /**
     * Send weekly reminder emails to users who have enabled this notification.
     */
    private function sendWeeklyReminderEmails()
    {
        $this->info('Processing weekly reminder emails...');
        
        // Get all notification settings where weekly reminder is enabled
        $notificationSettings = NotificationSetting::where('weekly_reminder', true)->get();
        
        $emailsSent = 0;
        
        foreach ($notificationSettings as $settings) {
            try {
                // Check if it's time to send the email based on user's timezone and preferences
                if ($settings->shouldSendWeeklyReminder()) {
                    $user = User::find($settings->user_id);
                    
                    if (!$user) {
                        continue;
                    }
                    
                    // Send the email
                    Mail::to($user->email)->send(new WeeklyReminderEmail($user));
                    
                    // Update the last sent timestamp
                    $settings->last_reminder_sent_at = now();
                    $settings->save();
                    
                    $emailsSent++;
                    
                    $this->info("Sent weekly reminder email to {$user->email}");
                }
            } catch (\Exception $e) {
                Log::error('Error sending weekly reminder email: ' . $e->getMessage());
                $this->error('Error sending weekly reminder email: ' . $e->getMessage());
            }
        }
        
        $this->info("Sent {$emailsSent} weekly reminder emails");
    }
}
