<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Routes that delete or reset data must not answer GET: a link, image tag or prefetch could trigger them. */
class MutatingRoutesTest extends TestCase
{
    use RefreshDatabase;

    private const MUTATING_ROUTES = [
        'admin.schedule.destroy', 'admin.class.student.destroy', 'admin.class.teacher.destroy', 'admin.class.reset-quota', 'admin.class.reset', 'admin.teacher.destroy',
        'head.schedule.destroy', 'head.class.student.destroy', 'head.class.teacher.destroy', 'head.class.reset-quota', 'RulesDelete',
        'head.class.reset', 'head.teacher.destroy', 'deleteScheduleTeacher',
    ];

    public function test_mutating_routes_accept_post_only(): void
    {
        $getRoutes = [];

        foreach (self::MUTATING_ROUTES as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, "route {$name} missing");
            if (in_array('GET', $route->methods(), true)) {
                $getRoutes[] = $name;
            }
        }

        $this->assertSame([], $getRoutes);
    }

    public function test_head_deletes_a_schedule_with_post(): void
    {
        $schedule = DB::table('schedules')->orderBy('id')->first();

        $this->actingAs($this->user('head'))
            ->post(route('head.schedule.destroy', $schedule->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    public function test_head_deletes_a_teacher_without_classes(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($this->user('head'))->post(route('head.teacher.destroy', $teacher))->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $teacher->id]);
    }

    public function test_deleting_a_teacher_with_classes_goes_to_the_switch_page_and_its_search_never_deletes(): void
    {
        $teacher = User::where('email', 'teacher@gmail.com')->firstOrFail();

        $this->actingAs($this->user('head'))
            ->post(route('head.teacher.destroy', $teacher))
            ->assertRedirect(route('head.teacher.switch', $teacher));

        $this->get(route('head.teacher.switch', ['teacher' => $teacher, 'search' => 'Sari']))
            ->assertOk()
            ->assertSee('Sari Wulandari')
            ->assertDontSee('action="'.route('head.teacher.destroy', $teacher).'" method="get"', false);

        $this->assertDatabaseHas('users', ['id' => $teacher->id]);
    }

    public function test_head_freeze_detail_page_uses_head_routes(): void
    {
        $frozen = DB::table('class_transactions')->where('is_freeze', 1)->value('id');

        $this->actingAs($this->user('head'))
            ->get(route('head.class.freeze.show', $frozen))
            ->assertOk()
            ->assertDontSee(url('/admin/'), false);
    }

    private function user(string $role): User
    {
        return User::where('role', $role)->firstOrFail();
    }
}
