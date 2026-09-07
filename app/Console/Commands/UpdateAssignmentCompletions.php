<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AssignmentSubmission;

class UpdateAssignmentCompletions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lms:update-assignment-completions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update past assignment submissions with "submitted" status to mark their lessons as complete';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of past assignment completions...');

        // Ambil semua submission yang masih 'submitted' (menunggu dinilai)
        $submissions = AssignmentSubmission::where('status', 'submitted')->get();
        $count = 0;

        foreach ($submissions as $submission) {
            if ($submission->assignment && $submission->assignment->lesson) {
                $user = $submission->user;
                if ($user) {
                    $user->completedLessons()->syncWithoutDetaching($submission->assignment->lesson->id);
                    $count++;
                }
            }
        }

        $this->info("Successfully migrated $count submissions.");
    }
}
