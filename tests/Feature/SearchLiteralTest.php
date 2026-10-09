<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Teacher\TeacherTestCase;

/** Keyword search: "%", "_" and "\" match literally, and the keyword "0" is a real search. */
class SearchLiteralTest extends TeacherTestCase
{
    private function stockItem(string $name, int $quantity): void
    {
        DB::table('stocks')->insert(['name' => $name, 'size' => 'M', 'quantity' => $quantity, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_percent_in_a_stock_search_matches_literally(): void
    {
        $this->stockItem('Kaos 50% Off', 3);
        $this->stockItem('Kaos 500 Off', 3);

        $html = $this->asRole('admin')->get(route('admin.stock.index', ['search' => '50%']))->assertOk()->getContent();

        $this->assertStringContainsString('Kaos 50% Off', $html);
        $this->assertStringNotContainsString('Kaos 500 Off', $html);
    }

    public function test_underscore_in_a_stock_search_matches_literally(): void
    {
        $this->stockItem('Kaos 50% Off', 3);
        $this->stockItem('Kaos 500 Off', 3);
        $this->stockItem('A_B', 3);

        $html = $this->asRole('admin')->get(route('admin.stock.index', ['search' => '_']))->assertOk()->getContent();

        $this->assertStringContainsString('A_B', $html);
        $this->assertStringNotContainsString('Kaos 50% Off', $html);
        $this->assertStringNotContainsString('Kaos 500 Off', $html);
    }

    public function test_staff_student_list_searches_for_zero(): void
    {
        Student::factory()->create(['LongName' => 'Zero 0 Kid']);
        // The factory phone contains zeros, so the control student gets one without.
        Student::factory()->create(['LongName' => 'Plain Kid', 'Phone1' => '8123456789', 'Address' => 'Jl Mawar', 'nama_orang_tua' => 'Plain Parent', 'ShortName' => 'Plain']);

        $html = $this->asRole('admin')->get(route('admin.student.index', ['keyword' => '0']))->assertOk()->getContent();

        $this->assertStringContainsString('Zero 0 Kid', $html);
        $this->assertStringNotContainsString('Plain Kid', $html);
    }

    public function test_buyer_sort_links_keep_a_search_for_zero(): void
    {
        $this->stockItem('Item 0', 2);
        $this->stockItem('Item Plain', 2);

        $html = $this->asTeacher(User::factory()->create(['role' => 'buyer']))->get(route('buyer', ['search' => '0']))->assertOk()->getContent();

        $this->assertStringContainsString('Item 0', $html);
        $this->assertStringNotContainsString('Item Plain', $html);
        $this->assertStringContainsString(
            'href="'.route('buyerSorting', ['value' => 'name', 'type' => 'asc', 'search' => '0']).'"',
            $html
        );
    }

    public function test_teacher_home_searches_for_zero(): void
    {
        $teacher = $this->newTeacher();
        $withZero = $this->classFor($teacher, 'Grade 1', 0);
        $this->enrol($withZero, Student::factory()->create(['LongName' => 'Zero 0 Kid', 'Status' => 'aktif']));
        $without = $this->classFor($teacher, 'Grade 2', 0);
        $this->enrol($without, Student::factory()->create(['LongName' => 'Plain Kid', 'Status' => 'aktif']));
        $zeroSession = $this->sessionAt($withZero, '2026-10-09 09:00:00');
        $plainSession = $this->sessionAt($without, '2026-10-09 10:00:00');
        $this->at('2026-10-09 10:30:00');

        $html = $this->asTeacher($teacher)->get(route('teacher', ['keyword' => '0']))->assertOk()->getContent();

        $this->assertStringContainsString('action="'.route('viewAbsen', $zeroSession).'"', $html);
        $this->assertStringNotContainsString(route('viewAbsen', $plainSession).'"', $html);
    }
}
