<?php

namespace App\Jobs;

use App\Services\DailySummaryService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; // Mocking mail for now

class SendDailySummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(DailySummaryService $summaryService): void
    {
        $user = User::find($this->userId);
        if (!$user) {
            Log::error("User not found for daily summary job: {$this->userId}");
            return;
        }

        $summary = $summaryService->generateSummary($user->tenant_id);

        // Here we would send the email
        // Mail::to($user->email)->send(new DailySummaryMail($summary));
        
        Log::info("Daily summary generated and sent to user {$this->userId}", [
            'email' => $user->email,
            'summary_keys' => array_keys($summary['summary'])
        ]);
    }
}
