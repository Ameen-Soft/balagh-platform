<?php

namespace App\Console\Commands;

use App\Models\Complaint;
use App\Models\ComplaintLike;
use App\Services\CommunityEngagementService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileComplaintLikesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'complaints:reconcile-likes {--complaint_id= : Reconcile a specific complaint ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconciles the denormalized likes_count in complaints table with actual records in complaint_likes table';

    /**
     * Execute the console command.
     */
    public function handle(CommunityEngagementService $engagementService): int
    {
        $specificId = $this->option('complaint_id');

        if ($specificId) {
            $complaint = Complaint::find($specificId);
            if (! $complaint) {
                $this->error("Complaint #{$specificId} not found.");
                return Command::FAILURE;
            }

            $oldCount = (int) $complaint->likes_count;
            $newCount = $engagementService->reconcileLikesCount((int) $complaint->id);

            $this->info("Complaint #{$complaint->id} reconciled: {$oldCount} -> {$newCount}");
            return Command::SUCCESS;
        }

        $this->info('Starting full reconciliation of complaint likes...');

        $discrepanciesCount = 0;
        $totalProcessed = 0;

        // Query complaints and their actual like counts
        Complaint::chunk(100, function ($complaints) use ($engagementService, &$discrepanciesCount, &$totalProcessed) {
            foreach ($complaints as $complaint) {
                $totalProcessed++;
                $actual = ComplaintLike::where('complaint_id', $complaint->id)->count();

                if ((int) $complaint->likes_count !== $actual) {
                    $discrepanciesCount++;
                    $this->warn("Fixing Complaint #{$complaint->id}: recorded {$complaint->likes_count}, actual {$actual}");
                    $engagementService->reconcileLikesCount((int) $complaint->id);
                }
            }
        });

        $this->info("Reconciliation complete. Processed {$totalProcessed} complaints. Fixed {$discrepanciesCount} discrepancies.");

        return Command::SUCCESS;
    }
}
