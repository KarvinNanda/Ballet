<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HeadClassScheduleController extends Controller
{
    public function viewSchedule(Request $req, $id){
        $classId = $req->classId;
        $class = DB::table('schedules')
            ->join('class_transactions','class_transactions.id','schedules.class_id')
            ->selectRaw('
                schedules.date as date,
                schedules.id as id
            ')
            ->where('schedules.class_id', $id)->orderBy("date")
            ->orderBy('schedules.date','desc')
            ->paginate(5);

        return view('head.class.viewSchedule',compact('class','classId'));
    }

    public function viewaddScheduleClass(Request $req, $id){
        $classId = $id;
        $test = Schedule::find($classId);

        return view('head.class.viewaddSchedule',compact('classId'));
    }

    public function viewUpdateScheduleClass(Request $req){
        $schedule = Schedule::find($req->scheduleId);
        return view('head.class.viewUpdateSchedule',compact('schedule'));
    }

    public function viewAddMultipleScheduleClass(Request $req, $id){
        $classId = $id;
        $test = Schedule::find($classId);

        return view('head.class.addMultipleSchedule',compact('classId'));
    }

    public function addSchedule(Request $req){
        $date = Carbon::parse($req->dateTime);
        $class_schedule = Schedule::where('class_id',$req->classId)->get();
        $bool = true;
        foreach ($class_schedule as $sche){
            if(Carbon::parse($sche->date)->toDateString()==Carbon::parse($date)->toDateString())
            {
                $hour = Carbon::parse($date)->diff(Carbon::parse($sche->date))->format('%H');
                $num = (int)$hour;
                if($num<1)
                {
                    $bool=false;
                }
            }
        }

        if($bool==false){
            return redirect()->route("adminClassView");
        }else{
            $schedule = new Schedule();
            $schedule->class_id = $req->classId;
            $schedule->date = $date;
            $schedule->save();
        }

        return redirect()->route("headViewScheduleClass", ['classId' => $req->classId])->with('msg','Success Create Schedule');
    }

    public function updateSchedule(Request $req){
        $schedule = Schedule::find($req->scheduleId);
        $schedule->date = Carbon::parse($req->dateTime);
        $schedule->save();
        return redirect()->route("headViewScheduleClass",['classId' => $schedule->class_id])->with('msg','Success Update Schedule');
    }

    public function addMultipleSchedule(Request $req){

        $date = Carbon::parse($req->dateTime);

        for($i =0;$i < $req->ScheduleLoop;$i++){
            $schedule = new Schedule();
            $schedule->class_id = $req->classId;
            $schedule->date = $date;
            $schedule->save();
            $date->addDay(7);
        }

        return redirect()->route("headViewScheduleClass", ['classId' => $req->classId])->with('msg','Success Create Schedule');
    }

    public function deleteScheduleClass($id,$classId){
        $classDelete = DB::table('schedules')->where('schedules.id',$id)->where('class_id',$classId);
        $classDelete->delete();
        return redirect()->back()->with('msg','Success Delete Schedule');
    }
}
