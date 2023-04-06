<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\ClassTransaction;
use App\Models\DetailAbsen;
use App\Models\HeaderAbsen;
use App\Models\MappingClassChild;
use App\Models\MappingClassTeacher;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HeadClassController extends Controller
{
    public function index(){
        $classes = ClassTransaction::simplePaginate(5);
        return view('head.class.index',compact('classes'));
    }

    public function active(){
        $classes = ClassTransaction::where('Status','aktif')->simplePaginate(5);
        return view('head.class.index',compact('classes'));
    }

    public function nonActive(){
        $classes = ClassTransaction::where('Status','non-aktif')->simplePaginate(5);
        return view('head.class.index',compact('classes'));
    }

    public function search(Request $req){
        $classes = ClassTransaction::where('ClassName','like',"%$req->search%")->simplePaginate(5);
        return view('head.class.index',compact('classes'));
    }

    public function insertPage(){
        return view('head.class.insert');
    }

    public function insert(Request $req){
        $rules = [
            'inputName' => 'required',
            'inputPrice' => 'required|numeric'
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate);
        }

        $class = new ClassTransaction();
        $class->ClassName = $req->inputName;
        $class->ClassPrice = $req->inputPrice;
		$class->Status = 'non-aktif';

        $class->save();

        return redirect()->route('headClassPage');
    }

    public function ChangeStatus(ClassTransaction $class){
        $change = ClassTransaction::find($class->id);
        if($change->Status == 'aktif'){
            $change->Status = 'non-aktif';
        } else {
            $change->Status = 'aktif';
        }
        $change->save();
        return redirect()->back();
    }

    public function delete(ClassTransaction $class){
        $delete_id = ClassTransaction::find($class->id);
        $delete_id->delete();
        return redirect()->back();
    }

    public function detailClass(Request $req){
        $class_id = $req->classId;

        $teachers = DB::table('class_transactions')
            ->join('mapping_class_teachers','mapping_class_teachers.class_id','class_transactions.id')
            ->join('users','mapping_class_teachers.user_id','users.id')
            ->selectRaw('
                users.id as id,
                users.name as teacherName,
                users.dob as teacherDOB,
                users.address as teacherAddress,
                users.email as teacherEmail,
                users.phone as teacherPhone
            ')
            ->where('class_transactions.id',$req->classId)
            ->simplePaginate(5);

        $students = DB::table('class_transactions')
            ->join('mapping_class_children','mapping_class_children.class_id','class_transactions.id')
            ->join('students','mapping_class_children.student_id','students.id')
            ->selectRaw('
                students.id as id,
                students.LongName as studentName,
                students.Dob as studentDOB,
                students.Address as studentAddress,
                students.Email as studentEmail,
                students.Phone1 as studentPhone
            ')
            ->where('class_transactions.id',$req->classId)
            ->simplePaginate(5);

        return view('head.class.detail',compact('teachers','students','class_id'));
    }


    public function viewaddTeacher(Request $req){
        $class_id = $req->id;
        $teachers = DB::table('users')->where('role',"LIKE","teacher")
            ->whereNotIn('id',function ($q) use ($class_id){
                $q->select('mapping_class_teachers.user_id')
                    ->from('mapping_class_teachers')
                    ->where('class_id','=',$class_id);
            })

            ->simplePaginate(5);
        return view('head.class.viewTeacher',compact('teachers','class_id'));
    }

    public function addTeacher(Request $req){
        $mappingTeacher = new MappingClassTeacher();
        $mappingTeacher->user_id = $req->teacherId;
        $mappingTeacher->class_id = $req->classId;
        $mappingTeacher->Save();
        return redirect()->route("headClassPage");
    }

    public function viewaddStudent(Request $req){
        $class_id = $req->id;
        $students = DB::table('students')->whereNotIn('id',function($q) use ($class_id){
            $q->select('mapping_class_children.student_id')
                ->from('mapping_class_children');
        })
            ->where('students.Status',"=","aktif")
            ->simplePaginate(5);
        return view('head.class.viewStudent',compact('students','class_id'));
    }

    public function addStudent(Request $req){
        $mappingStudent = new MappingClassChild();
        $mappingStudent->student_id = $req->studentId;
        $mappingStudent->class_id = $req->classId;
        $mappingStudent->Save();
        return redirect()->route("headClassPage");
    }

    public function deleteTeacher($teacher, $class){
        $teacher = DB::table('mapping_class_teachers')->where('class_id',$class)->where('user_id',$teacher);
        $teacher->delete();
        return redirect()->route("headClassPage");
    }

    public function deleteStudent($student, $class){
        $teacher = DB::table('mapping_class_children')->where('class_id',$class)->where('student_id',$student);
        $teacher->delete();
        return redirect()->route("headClassPage");
    }

    public function resetClass($id){
        $classScheduleReset = DB::table('schedules')->where('class_id',$id);
        $classScheduleReset->delete();
        return redirect()->route("headClassPage");
    }

    public function levelUp(Request $req){
        $class_id = $req->classId;

        $students = DB::table('class_transactions')
            ->join('mapping_class_children','mapping_class_children.class_id','class_transactions.id')
            ->join('students','mapping_class_children.student_id','students.id')
            ->selectRaw('
                students.id as id,
                students.LongName as studentName,
                students.Dob as studentDOB,
                students.Address as studentAddress,
                students.Email as studentEmail,
                students.Phone1 as studentPhone
            ')
            ->where('class_transactions.id',$req->classId)
            ->simplePaginate(5);

        return view('head.class.levelUp',compact('students','class_id'));
    }

    public function levelUpStudent(Request $req){
        $check = ClassTransaction::where('id',$req->class_id+1)->first();
        if($check->Status == 'non-aktif'){
            ClassTransaction::where('id',$check->id)->update(['status' => 'aktif']);
            DB::table('mapping_class_children')->where('class_id',$req->class_id)->update(['class_id'=>$req->class_id+1]);
        } else{
            DB::table('mapping_class_children')->where('class_id',$req->class_id)->update(['class_id'=>$req->class_id+1]);
        }
        return redirect()->route("headClassPage");
    }

    public function viewAbsen($id){
        $view = Schedule::find($id);

        $class = DB::table('mapping_class_children')->join('students','students.id','mapping_class_children.student_id')
            ->selectRaw('
            students.nis as nis,
            students.LongName as nama
        ')->where('mapping_class_children.class_id','=',$view->class_id)
            ->get();
        $header = DB::table('header_absens')->where('schedules_id',"=",$id)->first();
        if(!is_null($header)){
            $detail = DB::table('detail_absens')->where('header_absen_id',$header->id)->get();
            return view('head.class.absen',compact("view","class","detail"));
        }
        return redirect()->route('headClassPage')->with('msg','Teacher havent get attendence');;
    }

    public function getAbsen(Request $req, Schedule $schedule){
        $header_id = HeaderAbsen::updateOrCreate([
            'schedules_id' => $schedule->id,
        ]);

//        $header_id = DB::table("header_absens")->orderBy('id','desc')->first();

        $students = Student::whereIn('nis',$req->nis)->get();
        for($i = 0;$i < count($req->nis);$i++){
            DetailAbsen::where('header_absen_id',$header_id->id)
                ->where('student_id',$students[$i]->id)
                ->update([
                'Description' => $req->check[$i] == "on" ? "Masuk" : $req->keterangan[$i],
                'Notes' => $req->keterangan[$i] == "Ijin" ? $req->notes[$i] : '',
            ]);
        }
        return redirect()->route("headClassPage");
    }
}
