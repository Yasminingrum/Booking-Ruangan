<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoEmailService
{
    /**
     * Send an email through Brevo transactional API using a Blade view as the HTML body.
     */
    public function sendTemplate(string $view, string $subject, string $toEmail, string $toName, array $params = [], array $viewData = []): void
    {
        $apiKey = config('services.brevo.api_key');
        $senderEmail = config('services.brevo.sender_email');
        $senderName = config('services.brevo.sender_name', config('app.name'));
        $endpoint = config('services.brevo.endpoint', 'https://api.brevo.com/v3/smtp/email');

        if (!$apiKey || !$senderEmail) {
            Log::warning('BrevoEmailService: Missing API credentials. Email skipped.', [
                'to' => $toEmail,
                'subject' => $subject,
            ]);
            return;
        }

        $htmlContent = view($view, array_merge($params, $viewData))->render();

        $payload = [
            'sender' => [
                'email' => $senderEmail,
                'name' => $senderName,
            ],
            'to' => [[
                'email' => $toEmail,
                'name' => $toName,
            ]],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ];

        if (!empty($params)) {
            $payload['params'] = $params;
        }

        if ($replyTo = Arr::get($viewData, 'reply_to')) {
            $payload['replyTo'] = $replyTo;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'accept' => 'application/json',
                    'api-key' => $apiKey,
                    'content-type' => 'application/json',
                ])
                ->post($endpoint, $payload);

            if (!$response->successful()) {
                Log::error('BrevoEmailService: Failed to send email', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'to' => $toEmail,
                    'subject' => $subject,
                ]);
            } else {
                Log::info('BrevoEmailService: Email sent successfully', [
                    'to' => $toEmail,
                    'subject' => $subject,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('BrevoEmailService: Exception when sending email', [
                'message' => $e->getMessage(),
                'to' => $toEmail,
                'subject' => $subject,
            ]);
        }
    }
}
