<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ClassTransaction;
use App\Models\DetailAbsen;
use App\Models\HeaderAbsen;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminClassScheduleController extends Controller
{
    public function viewSchedule(Request $req, $id){
        $class = DB::table('schedules')
            ->join('class_transactions','class_transactions.id','schedules.class_id')
            ->selectRaw('
                schedules.date as date,
                schedules.id as id
            ')->where('schedules.class_id', $id)->orderBy("date",'desc')
            ->get();

        $class_id = $id;

        return view('admin.class.viewSchedule', compact('class', 'class_id'));
    }

    public function viewaddScheduleClass(Request $req, $id){
        $classId = $id;
        $test = Schedule::find($classId);

        return view('admin.class.viewaddSchedule', compact('classId'));
    }

    public function viewUpdateScheduleClass(Request $req, $id){
        $schedule = Schedule::find($req->scheduleId);
        $class_id = $id;

        return view('admin.class.viewUpdateSchedule', compact('schedule', 'class_id'));
    }

    public function viewAddMultipleScheduleClass(Request $req){
        $classId = $req->classId;
        $test = Schedule::find($classId);
        return view('admin.class.addMultipleSchedule', compact('classId'));
    }

    public function addSchedule(Request $req, $id){
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
            return redirect()->route("viewScheduleClass", ['id' => $id]);
        }else{
            $schedule = new Schedule();
            $schedule->class_id = $id;
            $schedule->date = $date;
            $schedule->save();
        }

        return redirect()->route("viewScheduleClass", ['id' => $id])->with('msg','Success Create Schedule');
    }

    public function updateSchedule(Request $req, $id){
        $schedule = Schedule::find($req->scheduleId);
        $schedule->date = Carbon::parse($req->dateTime);
        $schedule->save();

        return redirect()->route("viewScheduleClass", ['id' => $id])->with('msg','Success Update Schedule');
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

        return redirect()->route("viewScheduleClass", ['id' => $req->classId])->with('msg','Success Create Schedule');
    }



    public function deleteScheduleClass($id,$classId){
        $classDelete = DB::table('schedules')->where('schedules.id',$id)->where('class_id',$classId);
        $classDelete->delete();

        return redirect()->route("viewScheduleClass", ['id' => $classId])->with('msg','Success Delete Schedule');
    }
}
