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
        $students = DB::table('students')
                    ->selectRaw("
                        id,age
                    ")
                    ->whereRaw('MONTH(Dob) = MONTH(CURDATE())')
                    ->whereRaw('DAY(Dob) = DAY(CURDATE())')
                    ->get();
        foreach($students as $s){
            // Log::info(round($s->age)." = ".$s->age);
            DB::table('students')
            ->where('id',$s->id)->update([
                'age' => $s->age + 1
            ]);
        }
    }
}
