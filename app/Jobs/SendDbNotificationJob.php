<?php

namespace App\Jobs;

use App\Facades\DbNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDbNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $template;
    public User $recipient;
    public array $templateData;

    /**
     * Create a new job instance.
     */
    public function __construct(string $template, User $user, array $templateData)
    {
        $this->template = $template;
        $this->recipient = $user;
        $this->templateData = $templateData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            \Log::info('📬 Sending DB Notification', [
                'template' => $this->template,
                'recipient_id' => $this->recipient->id ?? null,
                'recipient_email' => $this->recipient->email ?? null,
                'template_data' => $this->templateData,
            ]);

            // Attempt to send notification
            \App\Facades\DbNotification::dispatch($this->template, $this->recipient, $this->templateData);

            \Log::info('✅ DB Notification sent successfully', [
                'template' => $this->template,
                'recipient_id' => $this->recipient->id ?? null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('❌ Failed to send DB Notification', [
                'template' => $this->template,
                'recipient_id' => $this->recipient->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Optional: rethrow to mark job as failed in queue
            throw $e;
        }
    }
}
