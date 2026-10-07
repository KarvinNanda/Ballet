<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgeCalculation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'age:calculation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Making update age for student';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Age from date of birth, so running it any number of times gives the same result.
        DB::table('students')
            ->whereNotNull('Dob')
            ->update(['age' => DB::raw('TIMESTAMPDIFF(YEAR, Dob, CURDATE())')]);

        return self::SUCCESS;
    }
}
