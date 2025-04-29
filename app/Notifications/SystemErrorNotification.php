<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemErrorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $errorData;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $errorData)
    {
        $this->errorData = $errorData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('System Error Alert')
            ->greeting('System Error Detected')
            ->line("Error ID: {$this->errorData['error_id']}")
            ->line("Context: {$this->errorData['context']}")
            ->line("Message: {$this->errorData['message']}")
            ->line("File: {$this->errorData['file']}")
            ->line("Line: {$this->errorData['line']}")
            ->action('View Error Details', url('/admin/errors/' . $this->errorData['error_id']))
            ->line('This is an automated message from the system error monitoring service.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'error_id' => $this->errorData['error_id'],
            'context' => $this->errorData['context'],
            'message' => $this->errorData['message'],
        ];
    }
} 