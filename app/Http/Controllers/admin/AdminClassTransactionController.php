<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ClassTransaction;
use App\Models\MappingClassChild;
use App\Models\MappingClassTeacher;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminClassTransactionController extends Controller
{
    public function viewClass()
    {
        $classes = ClassTransaction::simplePaginate(5);
        return view('admin.class.view',compact('classes'));
    }

    public function active(){
        $classes = ClassTransaction::where('Status','aktif')->simplePaginate(5);
        return view('admin.class.view',compact('classes'));
    }

    public function nonActive(){
        $classes = ClassTransaction::where('Status','non-aktif')->simplePaginate(5);
        return view('admin.class.view',compact('classes'));
    }

    public function search(Request $req){
        $classes = ClassTransaction::where('ClassName','like',"%$req->search%")->simplePaginate(5);
        return view('admin.class.view',compact('classes'));
    }

    public function insertPage(){
        return view('admin.class.insert');
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

        $class->save();

        return redirect()->route('adminClassView');
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

    //detail
    public function detailClass(Request $req){
//        $teachers = MappingClassTeacher::where('class_id',$req->classId)->simplePaginate(5);
//
////        dd($teachers->User);
//        $students = MappingClassChild::where('class_id',$req->classId)->simplePaginate(5);
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
//        dd($teachers);

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

        return view('admin.class.detail',compact('teachers','students','class_id'));
    }

    //addteacher
    public function viewaddTeacher(Request $req){
//        $mappingteachers = MappingClassTeacher::doesntHave("User")->get();
//        dd($mappingteachers);
//        = MappingClassTeacher::where('role','teacher')->Doesnthas("User")->simplePaginate(5);
        $class_id = $req->id;
        $teachers = DB::table('users')->where('role',"LIKE","teacher")
            ->whereNotIn('id',function ($q) use ($class_id){
                $q->select('mapping_class_teachers.user_id')
                    ->from('mapping_class_teachers')
                    ->where('class_id','=',$class_id);
            })

            ->simplePaginate(5);
//        $class_id = $req->id;
        return view('admin.class.viewTeacher',compact('teachers','class_id'));
    }

    public function addTeacher(Request $req){
        $mappingTeacher = new MappingClassTeacher();
        $mappingTeacher->user_id = $req->teacherId;
        $mappingTeacher->class_id = $req->classId;
        $mappingTeacher->Save();
        return redirect()->route("adminClassView");
    }

    //addStudent
    public function viewaddStudent(Request $req){
        $class_id = $req->id;
        $students = DB::table('students')->whereNotIn('id',function($q) use ($class_id){
            $q->select('mapping_class_children.student_id')
                ->from('mapping_class_children');
        })->simplePaginate(5);
        return view('admin.class.viewStudent',compact('students','class_id'));
    }

    public function addStudent(Request $req){
        $mappingStudent = new MappingClassChild();
        $mappingStudent->student_id = $req->studentId;
        $mappingStudent->class_id = $req->classId;
        $mappingStudent->Save();
        return redirect()->route("adminClassView");
    }

//    public function deleteTeacher(User $teacher, ClassTransaction $class){
//        dd($teacher);
//    }


}
