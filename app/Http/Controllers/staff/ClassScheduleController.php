<?php

namespace App\Http\Controllers\staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ScheduleDateRequest;
use App\Http\Requests\Staff\StoreMultipleScheduleRequest;
use App\Models\ClassTransaction;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/** Schedules of one class, shared by admin and head. The attendance link is head only (Gate attendance.record). */
class ClassScheduleController extends Controller
{
    public function index(ClassTransaction $class)
    {
        $schedules = Schedule::where('class_id', $class->id)->orderBy('date', 'desc')->paginate(5);

        return view('staff.schedule.index', compact('class', 'schedules'));
    }

    public function create(ClassTransaction $class)
    {
        return view('staff.schedule.insert', compact('class'));
    }

    public function store(ScheduleDateRequest $req, ClassTransaction $class)
    {
        $date = Carbon::parse($req->dateTime);

        // Two schedules of one class must be at least an hour apart.
        $clash = Schedule::where('class_id', $class->id)->whereDate('date', $date->toDateString())->get()
            ->contains(fn ($s) => $date->diffInMinutes(Carbon::parse($s->date), true) < 60);
        if ($clash) {
            return redirect(staff_route('schedule.create', $class->id))->withInput()->with('error', 'Schedule clashes with an existing one');
        }

        $schedule = new Schedule();
        $schedule->class_id = $class->id;
        $schedule->date = $date;
        $schedule->save();

        return redirect(staff_route('schedule.index', $class->id))->with('msg', 'Success Create Schedule');
    }

    /** With a class from the path (or the old ?classId= query) the form is for that class; without one it asks for the class. */
    public function createMultiple(Request $req, ?ClassTransaction $class = null)
    {
        $class ??= ClassTransaction::find(filter_var($req->query('classId'), FILTER_VALIDATE_INT) ?: 0);
        $classes = $class ? collect() : DB::table('class_transactions')
            ->join('class_types', 'class_types.id', 'class_transactions.class_type_id')
            ->where('class_transactions.Status', 'aktif')
            ->orderBy('class_types.class_name')
            ->get(['class_transactions.id', 'class_types.class_name']);

        return view('staff.schedule.multiple', compact('class', 'classes'));
    }

    public function storeMultiple(StoreMultipleScheduleRequest $req)
    {
        $date = Carbon::parse($req->dateTime);
        DB::transaction(function () use ($req, $date) {
            for ($i = 0; $i < $req->ScheduleLoop; $i++) {
                $schedule = new Schedule();
                $schedule->class_id = $req->classId;
                $schedule->date = $date->copy();
                $schedule->save();
                $date->addDays(7);
            }
        });

        return redirect(staff_route('schedule.index', $req->classId))->with('msg', 'Success Create Schedule');
    }

    public function edit(Schedule $schedule)
    {
        return view('staff.schedule.update', compact('schedule'));
    }

    public function update(ScheduleDateRequest $req, Schedule $schedule)
    {
        $schedule->date = Carbon::parse($req->dateTime);
        $schedule->save();

        return redirect(staff_route('schedule.index', $schedule->class_id))->with('msg', 'Success Update Schedule');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return redirect(staff_route('schedule.index', $schedule->class_id))->with('msg', 'Success Delete Schedule');
    }
}
