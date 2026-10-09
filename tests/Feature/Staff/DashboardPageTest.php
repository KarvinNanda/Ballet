<?php

namespace Tests\Feature\Staff;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DashboardPageTest extends StaffTestCase
{
    public function test_home_uses_the_page_kit_for_admin_and_head(): void
    {
        foreach (['admin', 'head'] as $role) {
            $this->asRole($role)->get(route($role))->assertOk()
                ->assertSee('<h1 class="page-title">Schedules</h1>', false)
                ->assertSeeText('Teacher')
                ->assertDontSee('pagetitle', false);
        }
    }

    public function test_row_shows_day_date_and_minute_time(): void
    {
        $row = DB::table('class_transactions')
            ->join('schedules', 'class_transactions.id', 'schedules.class_id')
            ->join('mapping_class_teachers', 'mapping_class_teachers.class_id', 'class_transactions.id')
            ->where('class_transactions.Status', 'aktif')
            ->whereDate('schedules.date', '<=', now()->setTimezone('GMT+7')->addDay(7)->toDateString())
            ->orderBy('schedules.date', 'desc')
            ->value('schedules.date');
        $this->assertNotNull($row, 'seed has no schedule for an active class');
        $date = \Illuminate\Support\Carbon::parse($row);

        $this->asRole('admin')->get(route('admin'))->assertOk()
            ->assertSeeText($date->format('d M Y'))
            ->assertSeeText($date->englishDayOfWeek)
            ->assertSee('<td>'.$date->format('H:i').'</td>', false);
    }

    public function test_no_schedules_shows_the_empty_state(): void
    {
        DB::table('class_transactions')->update(['Status' => 'nonaktif']);

        $this->asRole('head')->get(route('head'))->assertOk()
            ->assertSeeText('No upcoming or recent sessions')
            ->assertDontSee('<table', false);
    }

    public function test_master_layout_shim_is_gone(): void
    {
        $this->assertFileDoesNotExist(resource_path('views/Master/master.blade.php'));
        foreach (File::allFiles(resource_path('views')) as $file) {
            $this->assertStringNotContainsString("Master.master", $file->getContents(), $file->getRelativePathname());
        }
    }
}
