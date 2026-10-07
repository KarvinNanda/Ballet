<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TeacherReportTest extends TestCase
{
    use RefreshDatabase;

    public static function reports(): array
    {
        return [
            'head' => ['head', 'head.report.teacher.print'],
            'finance' => ['finance', 'financeTeacherReport'],
        ];
    }

    #[DataProvider('reports')]
    public function test_teacher_report_pdf_is_generated_for_the_current_month(string $role, string $route): void
    {
        $user = User::where('role', $role)->firstOrFail();

        $this->actingAs($user)
            ->post(route($route, now('Asia/Jakarta')->month))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_invalid_month_is_not_found(): void
    {
        $this->actingAs(User::where('role', 'finance')->firstOrFail())
            ->post(route('financeTeacherReport', 13))
            ->assertNotFound();
    }
}
