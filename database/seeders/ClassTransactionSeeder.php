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
                'ClassName' => 'Baby Class'
            ],

            [
                'ClassName' => 'Pre School'
            ],

            [
                'ClassName' => 'Pre Primary'
            ],

            [
                'ClassName' => 'Primary'
            ],

            [
                'ClassName' => 'Grace 1'
            ],

            [
                'ClassName' => 'Grace 2'
            ],

            [
                'ClassName' => 'Grace 3'
            ],

            [
                'ClassName' => 'Grace 4'
            ],

            [
                'ClassName' => 'Grace 5'
            ],

            [
                'ClassName' => 'Intermediate Foundation'
            ],

            [
                'ClassName' => 'Intermediate'
            ],

            [
                'ClassName' => 'Advance Foundation'
            ],

            [
                'ClassName' => 'Advance 1'
            ],

            [
                'ClassName' => 'Advance 2'
            ],

            [
                'ClassName' => 'Intensive Class'
            ],

            [
                'ClassName' => 'Intensive Kids'
            ],

            [
                'ClassName' => 'Pointe Class'
            ],


        ]);
    }
}
