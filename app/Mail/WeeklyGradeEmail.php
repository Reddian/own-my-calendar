<?php

namespace App\Mail;

use App\Models\CalendarGrade;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyGradeEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user instance.
     *
     * @var \App\Models\User
     */
    protected $user;

    /**
     * The calendar grade instance.
     *
     * @var \App\Models\CalendarGrade
     */
    protected $grade;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, CalendarGrade $grade)
    {
        $this->user = $user;
        $this->grade = $grade;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Weekly Calendar Grade from Own My Calendar',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-grade',
            with: [
                'user' => $this->user,
                'grade' => $this->grade,
                'overallGrade' => $this->grade->overall_grade,
                'strengths' => $this->grade->strengths,
                'improvements' => $this->grade->improvements,
                'recommendations' => $this->grade->recommendations,
                'ruleGrades' => $this->grade->rule_grades,
                'weekStart' => $this->grade->start_date,
                'weekEnd' => $this->grade->end_date,
                'appUrl' => config('app.url'),
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
}
