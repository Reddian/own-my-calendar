<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyReminderEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user instance.
     *
     * @var \App\Models\User
     */
    protected $user;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Time to Plan Your Week - Own My Calendar',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Get the start and end dates for the upcoming week
        $weekStart = now()->startOfWeek()->format('Y-m-d');
        $weekEnd = now()->endOfWeek()->format('Y-m-d');

        return new Content(
            view: 'emails.weekly-reminder',
            with: [
                'user' => $this->user,
                'weekStart' => $weekStart,
                'weekEnd' => $weekEnd,
                'appUrl' => config('app.url'),
                'calendarRules' => $this->getCalendarRules(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get the calendar rules to include in the reminder email.
     *
     * @return array
     */
    private function getCalendarRules(): array
    {
        return [
            'A' => 'Start with Non-Negotiables - Identify priorities and schedule them first.',
            'B' => 'Determine Where You Will Sacrifice - Be flexible in some areas.',
            'C' => 'Create Time Blocks - Group similar activities together.',
            'D' => 'Designate Focus Time - Schedule uninterrupted work periods.',
            'E' => 'Establish Boundaries - Set clear start and end times.',
        ];
    }
}
