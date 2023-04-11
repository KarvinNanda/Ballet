<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('class_transactions')->insert([
            [
                'class_name' => 'Baby Class 1',
                'class_type_id' => 1,
                'status'=>'aktif'
            ],
            [
                'class_name' => 'Baby Class 2',
                'class_type_id' => 2,
                'status'=>'non-aktif'
            ],
            ]
        );
    }
}
