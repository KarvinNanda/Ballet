<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveFinishedTrials extends Command
{
    protected $signature = 'students:remove-finished-trials';

    protected $description = 'Remove trial students who used their two trial sessions from every class';

    public function handle(): int
    {
        $ids = DB::table('students')->where('Status', 'trial')->where('Quota', '>=', 2)->pluck('id');
        $removed = DB::table('mapping_class_children')->whereIn('student_id', $ids)->delete();
        $this->info("Removed {$removed} class mappings of {$ids->count()} finished trial students.");

        return self::SUCCESS;
    }
}
