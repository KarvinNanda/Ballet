<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ClassTransaction;
use App\Models\ClassType;
use App\Models\MappingClassChild;
use App\Models\MappingClassTeacher;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function PHPSTORM_META\map;

class AdminClassTransactionController extends Controller
{
    public function viewClass(Request $request)
    {
        $sort = 'asc';
        $keyword = $request->query('keyword');
        $status = $request->query('status');
        if(is_null($status))$status='all';
        if ($status == "all") {
            $classes = ClassTransaction::select(
                'class_transactions.id',
                'class_name',
                'class_price',
                'Status',
                'class_type_id',
                'student_id',
                'class_id',
                DB::raw('COUNT(student_id) as people_count'))
                ->where(function($q) use ($keyword){
                    if(!is_null($keyword)) $q->where('class_name','like',"%$keyword%");
                })
                ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
                ->leftJoin('mapping_class_children', 'class_transactions.id', 'mapping_class_children.class_id')
                ->orderBy('class_transactions.id','desc')
                ->groupBy('class_transactions.id')
                ->paginate(5);
        } else{
            $classes = ClassTransaction::select(
                'class_transactions.id',
                'class_name',
                'class_price',
                'Status',
                'class_type_id',
                'student_id',
                'class_id',
                DB::raw('COUNT(student_id) as people_count'))
                ->where(function($q) use ($keyword){
                    if(!is_null($keyword)) $q->where('class_name','like',"%$keyword%");
                })
                ->where('Status','=',$status)
                ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
                ->leftJoin('mapping_class_children', 'class_transactions.id', 'mapping_class_children.class_id')
                ->orderBy('class_transactions.id','desc')
                ->groupBy('class_transactions.id')
                ->paginate(5);
        }


        return view('admin.class.view', compact('classes','sort'));
    }

    public function viewClassSorting($value,$type)
    {
        $classes = ClassTransaction::select(
            'class_transactions.id',
            'class_name',
            'class_price',
            'Status',
            'class_type_id',
            'student_id',
            'class_id',
            DB::raw('COUNT(student_id) as people_count'))
            ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
            ->leftJoin('mapping_class_children', 'class_transactions.id', 'mapping_class_children.class_id')
            ->groupBy('class_id')
            ->orderBy($value,$type)
            ->paginate(5);
        $sort = $type == 'asc' ? 'desc':'asc';

        return view('admin.class.view',compact('classes','sort'));
    }

    public function active(){
        $classes = ClassTransaction::where('Status','aktif')->paginate(5);
        return view('admin.class.view',compact('classes'));
    }

    public function nonActive(){
        $classes = ClassTransaction::where('Status','non-aktif')->paginate(5);
        return view('admin.class.view',compact('classes'));
    }

    public function search(Request $req){
        $classes = ClassTransaction::join('class_types','class_transactions.class_type_id','class_types.id')
        ->where('class_name','like',"%$req->search%")->paginate(5);
        return view('admin.class.view',compact('classes'));
    }

    public function addCoursePage(){
        return view('admin.class.classTypeAdd');
    }

    public function addCourse(Request $req){
        $rules = [
            'inputName' => 'required',
            'inputPrice' => 'required|numeric'
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $class = new ClassType();
        $class->class_name = $req->inputName;
        $class->class_price = $req->inputPrice;

        $class->save();

        return redirect()->route('adminClassTypePage')->with('msg','Success Create Course');
    }

    public function insertPage(){
        $types = ClassType::all();
        $users = User::all()->where('role','teacher');
        return view('admin.class.insert',compact('types','users'));
    }

    public function insert(Request $req){
        $rules = [
            'inputType' => 'required',
            'inputTeacher' => 'required'
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $class = new ClassTransaction();
        $class->class_type_id = $req->inputType;
        $class->Status = 'aktif';

        $class->save();

        $map = new MappingClassTeacher();
        $map->class_id = $class->id;
        $map->user_id = $req->inputTeacher;

        $map->save();

        return redirect()->route('adminClassView')->with('msg','Success Create Class');
    }
    //detail
    public function detailClass($id){
        $class_id = $id;
        $check_schedule = Schedule::where('class_id',$class_id)->first();
        if(is_null($check_schedule)) return redirect()->back()->with('msg','Please Create Schedule First');
        $class_name = DB::table('class_transactions')
            ->leftJoin('class_types','class_types.id','class_transactions.class_type_id')
            ->where('class_transactions.id',$class_id)
        ->first()->class_name;


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
            ->where('class_transactions.id',$id)
            ->paginate(5);

        $students = DB::table('class_transactions')
            ->join('mapping_class_children','mapping_class_children.class_id','class_transactions.id')
            ->join('students','mapping_class_children.student_id','students.id')
            ->selectRaw('
                students.id as id,
                students.LongName as studentName,
                students.Dob as studentDOB,
                students.Address as studentAddress,
                students.Email as studentEmail,
                students.Phone1 as studentPhone,
                students.Quota as studentQuota,
                students.MaxQuota as studentMaxQuota
            ')
            ->where('students.Status','aktif')
            ->where('class_transactions.id',$id)
            ->paginate(5);

        //  $transactions = DB::table('transactions')
        //             ->where('class_transactions_id',$class_id)
        //             ->selectRaw("
        //                 students_id,
        //                 sum(students_id) as paid
        //             ")
        //             ->where('payment_status','Paid')
        //             ->whereRaw('transaction_date >= curdate()')
        //             ->groupBy('students_id')
        //             ->get();

        return view('admin.class.detail',compact('teachers','students','class_id','class_name'));
    }

    public function resetQuota($id){
        $class_id = $id;

        $data = DB::table('class_transactions')
            ->join('mapping_class_children','mapping_class_children.class_id','class_transactions.id')
            ->join('students','mapping_class_children.student_id','students.id')
            ->where('class_transactions.id', $class_id)
            ->get();
        foreach($data as $d){
            DB::table('students')->where('id',$d->student_id)->update([
                'MaxQuota' => ($d->MaxQuota - $d->Quota),
                'Quota' => 0,
                'is_new' => 0,
            ]);
        }
        return redirect()->route("adminDetailClass", ['id' => $class_id])->with('msg','Success Reset Quota');
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
            ->paginate(5);

        return view('admin.class.levelUp',compact('students','class_id'));
    }

    public function levelUpStudent(Request $req){
        $class = ClassTransaction::where('id',$req->class_id)->first();
        $class->class_type_id += 1;
        $class->save();

        $class_id = $req->class_id;
        $data = DB::table('class_transactions')
        ->join('mapping_class_children','mapping_class_children.class_id','class_transactions.id')
        ->join('students','mapping_class_children.student_id','students.id')
        ->where('class_transactions.id', $class_id)
        ->get();
        foreach($data as $d){
            DB::table('students')->where('id',$d->student_id)->update([
                'MaxQuota' => ($d->MaxQuota - $d->Quota),
                'Quota' => 0,
                'is_new' => 0,
            ]);
        }

        MappingClassChild::where('class_id',$req->class_id)->delete();
        return redirect()->route("adminClassView")->with('msg','Success Level up All Student');
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

            ->paginate(5);
//        $class_id = $req->id;
        return view('admin.class.viewTeacher',compact('teachers','class_id'));
    }

    public function addTeacher(Request $req){
        $mappingTeacher = new MappingClassTeacher();
        $mappingTeacher->user_id = $req->teacherId;
        $mappingTeacher->class_id = $req->classId;
        $mappingTeacher->Save();
        return redirect()->route("adminDetailClass", ['id' => $req->classId])->with('msg','Success Add Teacher');
    }

    //addStudent
    public function viewaddStudent(Request $req){
        $class_id = $req->id;
        $keyword = $req->keyword;
        $students = DB::table('students')
        ->whereNotIn('id',function($q) use ($class_id){
            $q->select('mapping_class_children.student_id')
                ->from('mapping_class_children')
                ->where('mapping_class_children.class_id',$class_id);
        })
            ->where(function ($query) use ($keyword){
                if($keyword != '' || is_null($keyword)){
                    $query->where('students.LongName','like',"%$keyword%");
                }
            })
            ->whereRaw("(students.Status = 'aktif' or students.Status = 'trial')")
            ->paginate(5);
        return view('admin.class.viewStudent',compact('students','class_id'));
    }

    public function addStudent(Request $req){
        $class_id = $req->classId;
        $get_class_price = ClassTransaction::leftJoin('class_types','class_types.id','class_transactions.class_type_id')
            ->where('class_transactions.id',$class_id)->first();
        $quota=0;
        if($get_class_price->class_name == 'Pointe Class') $quota = 4;
        else if($get_class_price->class_name == 'Intensive Kids' || $get_class_price->class_name == 'Intensive Class')$quota = 12;
        else $quota = 8;
        $check_schedule = Schedule::where('class_id',$class_id)
            ->whereRaw('date  >= curdate()')
            ->orderBy('date')
            ->first();

        if(!is_null($check_schedule)){
            // $first_month = Carbon::parse($check_schedule->date)->addMonth(1)->addDays(10)->setTime(0,0,0);
            $first_month = Carbon::parse($check_schedule->date)->setTime(0,0,0);


            for ($i=0;$i<3;$i++){
                if($i==0){
                    $trans[] = [
                        'students_id' =>$req->studentId,
                        'class_transactions_id' => $class_id,
                        'transaction_date' => $first_month,
                        'payment_status' => 'Unpaid',
                        'discount' => 0,
                        'price' => $get_class_price->class_price,
                        'desc' => '-',
                        'transaction_quota' => $quota,
                    ];
                } else {
                    $trans[] = [
                        'students_id' =>$req->studentId,
                        'class_transactions_id' => $class_id,
                        'transaction_date' => Carbon::parse($check_schedule->date)->day + 10 > 30 ? Carbon::parse($check_schedule->date)->addMonth($i)->setDay(10) : Carbon::parse($check_schedule->date)->addMonth($i)->setDay(10),
                        'payment_status' => 'Unpaid',
                        'discount' => 0,
                        'price' => $get_class_price->class_price,
                        'desc' => '-',
                        'transaction_quota' => $quota,
                    ];
                }
            }
            DB::table('transactions')->insert($trans);
        }

        $mappingStudent = new MappingClassChild();
        $mappingStudent->student_id = $req->studentId;
        $mappingStudent->class_id = $class_id;
        $mappingStudent->Save();


        return redirect()->route("adminDetailClass", ['id' => $class_id])->with('msg','Success Add Student');
    }

    public function deleteTeacher($teacher, $class){
        $teacher = DB::table('mapping_class_teachers')->where('class_id',$class)->where('user_id',$teacher);
        $teacher->delete();
        return redirect()->route("adminDetailClass", ['id' => $class])->with('msg','Success Delete Teacher');
    }

    public function deleteStudent($student, $class){
        $teacher = DB::table('mapping_class_children')->where('class_id',$class)->where('student_id',$student);
        $teacher->delete();
        return redirect()->route("adminDetailClass", ['id' => $class])->with('msg','Success Delete Student');
    }

    public function generateTransactionStudent($student, $class){
        $get_class_price = ClassTransaction::leftJoin('class_types','class_types.id','class_transactions.class_type_id')
            ->where('class_transactions.id',$class)->first()->class_price;
        $check_schedule = Schedule::where('class_id',$class)
            ->whereRaw('date  >= curdate()')
            ->orderBy('date')
            ->first();

        if(!is_null($check_schedule)){
            // $first_month = Carbon::parse($check_schedule->date)->addMonth(1)->addDays(10)->setTime(0,0,0);
            $first_month = Carbon::parse($check_schedule->date)->setTime(0,0,0);


            for ($i=0;$i<3;$i++){
                if($i==0){
                    $trans[] = [
                        'students_id' =>$student,
                        'class_transactions_id' => $class,
                        'transaction_date' => $first_month,
                        'payment_status' => 'Unpaid',
                        'discount' => 0,
                        'price' => $get_class_price,
                        'desc' => '-'
                    ];
                } else {
                    $trans[] = [
                        'students_id' =>$student,
                        'class_transactions_id' => $class,
                        'transaction_date' => Carbon::parse($check_schedule->date)->day + 10 > 30 ? Carbon::parse($check_schedule->date)->addMonth($i)->setDay(10) : Carbon::parse($check_schedule->date)->addMonth($i)->setDay(10),
                        'payment_status' => 'Unpaid',
                        'discount' => 0,
                        'price' => $get_class_price,
                        'desc' => '-'
                    ];
                }
            }
            DB::table('transactions')->insert($trans);
        }
        return redirect()->route("adminDetailClass", ['id' => $class])->with('msg','Success Generate Transaction Student');
    }

    public function resetClass($id){
        $classScheduleReset = DB::table('schedules')->where('class_id',$id);
        $classScheduleReset->delete();
        return redirect()->route("adminClassView")->with('msg','Success Reset Class');
    }

    public function delete($classId){
        $delete_id = ClassTransaction::find($classId);
        $delete_id->delete();
        return redirect()->back()->with('msg','Success Delete Class');
    }
}
