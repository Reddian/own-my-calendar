<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemErrorNotification;
use Exception;
use Throwable;

class ErrorHandlerService
{
    /**
     * Handle API errors consistently
     *
     * @param Throwable $e
     * @param string $context
     * @param array $additionalData
     * @return array
     */
    public function handleApiError(Throwable $e, string $context, array $additionalData = []): array
    {
        $errorId = uniqid('err_');
        $errorData = array_merge([
            'error_id' => $errorId,
            'context' => $context,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ], $additionalData);

        // Log the error
        Log::error("API Error [{$errorId}]: {$context}", $errorData);

        // Notify administrators for critical errors
        if ($this->isCriticalError($e)) {
            $this->notifyAdmins($errorData);
        }

        return [
            'success' => false,
            'error' => [
                'id' => $errorId,
                'message' => $this->getUserFriendlyMessage($e),
                'code' => $e->getCode(),
            ],
            'retry_after' => $this->getRetryAfter($e),
        ];
    }

    /**
     * Handle Google Calendar specific errors
     *
     * @param Throwable $e
     * @param string $operation
     * @return array
     */
    public function handleGoogleCalendarError(Throwable $e, string $operation): array
    {
        $errorData = [
            'operation' => $operation,
            'error_type' => get_class($e),
        ];

        // Handle specific Google API errors
        if (str_contains($e->getMessage(), 'invalid_grant')) {
            return $this->handleTokenExpiredError($e, $errorData);
        }

        if (str_contains($e->getMessage(), 'quota_exceeded')) {
            return $this->handleQuotaExceededError($e, $errorData);
        }

        return $this->handleApiError($e, "Google Calendar: {$operation}", $errorData);
    }

    /**
     * Handle AI grading specific errors
     *
     * @param Throwable $e
     * @param array $gradingData
     * @return array
     */
    public function handleAIGradingError(Throwable $e, array $gradingData = []): array
    {
        $errorData = [
            'grading_data' => $gradingData,
            'error_type' => get_class($e),
        ];

        // Handle OpenAI specific errors
        if (str_contains($e->getMessage(), 'rate_limit')) {
            return $this->handleRateLimitError($e, $errorData);
        }

        return $this->handleApiError($e, 'AI Grading', $errorData);
    }

    /**
     * Determine if an error is critical
     *
     * @param Throwable $e
     * @return bool
     */
    protected function isCriticalError(Throwable $e): bool
    {
        $criticalErrors = [
            'invalid_grant',
            'quota_exceeded',
            'rate_limit',
            'connection_timeout',
            'server_error',
        ];

        foreach ($criticalErrors as $error) {
            if (str_contains(strtolower($e->getMessage()), $error)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get user-friendly error message
     *
     * @param Throwable $e
     * @return string
     */
    protected function getUserFriendlyMessage(Throwable $e): string
    {
        $message = $e->getMessage();

        // Map technical errors to user-friendly messages
        $errorMap = [
            'invalid_grant' => 'Your Google Calendar connection has expired. Please reconnect your calendar.',
            'quota_exceeded' => 'Google Calendar API quota exceeded. Please try again later.',
            'rate_limit' => 'Too many requests. Please wait a moment and try again.',
            'connection_timeout' => 'Connection timed out. Please check your internet connection and try again.',
            'server_error' => 'A server error occurred. Please try again later.',
        ];

        foreach ($errorMap as $error => $friendlyMessage) {
            if (str_contains(strtolower($message), $error)) {
                return $friendlyMessage;
            }
        }

        return 'An unexpected error occurred. Please try again later.';
    }

    /**
     * Get retry after time in seconds
     *
     * @param Throwable $e
     * @return int|null
     */
    protected function getRetryAfter(Throwable $e): ?int
    {
        if (str_contains($e->getMessage(), 'rate_limit')) {
            return 60; // 1 minute
        }

        if (str_contains($e->getMessage(), 'quota_exceeded')) {
            return 300; // 5 minutes
        }

        return null;
    }

    /**
     * Handle token expired error
     *
     * @param Throwable $e
     * @param array $errorData
     * @return array
     */
    protected function handleTokenExpiredError(Throwable $e, array $errorData): array
    {
        Log::warning('Google Calendar token expired', $errorData);
        
        return [
            'success' => false,
            'error' => [
                'message' => 'Your Google Calendar connection has expired. Please reconnect your calendar.',
                'code' => 'token_expired',
                'action' => 'reconnect',
            ],
        ];
    }

    /**
     * Handle quota exceeded error
     *
     * @param Throwable $e
     * @param array $errorData
     * @return array
     */
    protected function handleQuotaExceededError(Throwable $e, array $errorData): array
    {
        Log::warning('Google Calendar quota exceeded', $errorData);
        
        return [
            'success' => false,
            'error' => [
                'message' => 'Google Calendar API quota exceeded. Please try again later.',
                'code' => 'quota_exceeded',
                'retry_after' => 300,
            ],
        ];
    }

    /**
     * Handle rate limit error
     *
     * @param Throwable $e
     * @param array $errorData
     * @return array
     */
    protected function handleRateLimitError(Throwable $e, array $errorData): array
    {
        Log::warning('AI Grading rate limit exceeded', $errorData);
        
        return [
            'success' => false,
            'error' => [
                'message' => 'Too many grading requests. Please wait a moment and try again.',
                'code' => 'rate_limit',
                'retry_after' => 60,
            ],
        ];
    }

    /**
     * Notify administrators about critical errors
     *
     * @param array $errorData
     * @return void
     */
    protected function notifyAdmins(array $errorData): void
    {
        try {
            Notification::route('mail', config('app.admin_email'))
                ->notify(new SystemErrorNotification($errorData));
        } catch (Exception $e) {
            Log::error('Failed to send admin notification', [
                'error' => $e->getMessage(),
                'original_error' => $errorData,
            ]);
        }
    }
} 