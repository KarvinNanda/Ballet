<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassPagesTest extends TestCase
{
    use RefreshDatabase;

    public static function classPages(): array
    {
        return [
            'admin class' => ['admin', '/admin/class'],
            'admin class freeze' => ['admin', '/admin/class/freeze'],
            'head class' => ['head', '/head/class'],
            'head class freeze' => ['head', '/head/class/freeze'],
        ];
    }

    #[DataProvider('classPages')]
    public function test_class_pages_open(string $role, string $uri): void
    {
        $user = User::where('role', $role)->firstOrFail();

        $this->actingAs($user)->get($uri)->assertOk();
    }

    public function test_admin_add_student_page_searches_with_admin_route(): void
    {
        $classId = \Illuminate\Support\Facades\DB::table('class_transactions')->where('Status', 'aktif')->value('id');

        $this->actingAs(User::where('role', 'admin')->firstOrFail())
            ->get(route('admin.class.student.create', $classId))
            ->assertOk()
            ->assertSee('action="'.route('admin.class.student.create', $classId).'"', false)
            ->assertDontSee(url('/head/'), false);
    }

    public function test_head_freeze_update_page_opens(): void
    {
        $frozen = \Illuminate\Support\Facades\DB::table('class_transactions')->where('is_freeze', 1)->value('id');

        $this->actingAs(User::where('role', 'head')->firstOrFail())
            ->get(route('head.class.freeze.edit', $frozen))
            ->assertOk();
    }
}
