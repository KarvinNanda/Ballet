<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\ClassTransaction;
use App\Models\ClassType;
use App\Models\DetailAbsen;
use App\Models\HeaderAbsen;
use App\Models\MappingClassChild;
use App\Models\MappingClassTeacher;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HeadClassController extends Controller
{

    public function updateClassFreezePage($id){
        $return_url = url()->previous();
        $class_id = $id;
        $class = DB::table('class_transactions as ct')
            ->join('class_types as ct2','ct2.id','ct.class_type_id')
            ->join('mapping_class_teachers as mct','mct.class_id','ct.id')
            ->join('users as u','mct.user_id','u.id')
            ->selectRaw("
                ct.class_transaction_price,
                ct2.class_name,
                u.name 
            ")
            ->where('ct.id',$class_id)
            ->first();
        return view('head.class.update',compact('class','class_id','return_url'));
    }

    public function updateClassFreeze(Request $req,$id){
        $rules = [
            'inputPrice' => 'required|numeric'
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $class_id = $id;
        $price = $req->inputPrice;
        DB::table('class_transactions as ct')
            ->where('ct.id',$class_id)
            ->update([
                'class_transaction_price' => $price
            ]);
        return redirect()->to($req->return_url)->with('msg','Success Update Class Freeze Data');
    }

    public function detailClassFreeze($id){
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

        $get_student_trial_to_get_out = DB::table('class_transactions')
                ->join('mapping_class_children','mapping_class_children.class_id','class_transactions.id')
                ->join('students','mapping_class_children.student_id','students.id')
                ->selectRaw('
                students.id as id
            ')
            ->where('students.Status','=','trial')
            ->where('students.Quota','>=',2)
            ->where('class_transactions.id',$id)
            ->get()
            ->pluck('id');

        DB::table('mapping_class_children')->whereIn('student_id',$get_student_trial_to_get_out)->delete();

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
                mapping_class_children.quota as studentMaxQuota,
                students.Status as studentStatus
            ')
            ->where('students.Status','!=','non-aktif')
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

        return view('head.class.detailFreeze',compact('teachers','students','class_id','class_name'));
    }

    public function viewClassFreeze(Request $request)
    {
        $sort = 'asc';
        $keyword = $request->query('keyword');
        $status = $request->query('status');
        if(is_null($status))$status='all';
        if ($status == "all") {
            $classes = ClassTransaction::select(
                'class_transactions.id',
                'class_name',
                'class_transaction_price',
                'class_transactions.Status',
                'class_type_id',
                'student_id',
                'mapping_class_children.class_id',
                DB::raw('COUNT(student_id) as people_count'))
                ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
                ->leftJoin('mapping_class_children',function($q){
                    $q->on('mapping_class_children.class_id','class_transactions.id')
                        ->where('mapping_class_children.student_id','!=',0);
                })
                ->leftJoin('students','mapping_class_children.student_id','students.id')
                ->leftJoin('mapping_class_teachers','mapping_class_teachers.class_id','students.id')
                ->leftJoin('users','users.id','mapping_class_teachers.user_id')
                ->where(function($q) use ($keyword){
                    if(!is_null($keyword)){
                        $q->where('class_name','like',"%$keyword%")
                            ->orWhere('users.name','like',"%$keyword%")
                            ->orWhere('students.LongName','like',"%$keyword%");
                    } 
                })
                ->where('class_transactions.is_freeze','=',1)
                // ->where('mapping_class_children.student_id','!=',0)
                ->orderBy('class_transactions.id','desc')
                ->groupBy('class_transactions.id')
                ->paginate(5);
        } else{
            $classes = ClassTransaction::select(
                'class_transactions.id',
                'class_name',
                'class_transaction_price',
                'class_transactions.Status',
                'class_type_id',
                'student_id',
                'mapping_class_children.class_id',
                DB::raw('COUNT(student_id) as people_count'))
                ->where(function($q) use ($keyword){
                    if(!is_null($keyword)) $q->where('class_name','like',"%$keyword%");
                })
                ->where('Status','=',$status)
                ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
                ->leftJoin('mapping_class_children',function($q){
                    $q->on('mapping_class_children.class_id','class_transactions.id')
                        ->where('mapping_class_children.student_id','!=',0);
                })
                ->where(function($q) use ($keyword){
                    if(!is_null($keyword)){
                        $q->where('class_name','like',"%$keyword%")
                            ->orWhere('users.name','like',"%$keyword%")
                            ->orWhere('students.LongName','like',"%$keyword%");
                    } 
                })
                ->where('class_transactions.is_freeze','=',1)
                // ->where('mapping_class_children.student_id','!=',0)
                ->orderBy('class_transactions.id','desc')
                ->groupBy('class_transactions.id')
                ->paginate(5);
        }


        return view('head.class.viewFreeze', compact('classes','sort'));
    }

    public function index(Request $request){
        $sort = 'asc';
        $keyword = $request->query('keyword');
        $status = $request->query('status');
        if(is_null($status))$status='all';
        if ($status == "all") {
            $classes = ClassTransaction::select(
                'class_transactions.id',
                'class_name',
                'class_transaction_price',
                'class_transactions.Status',
                'class_type_id',
                'student_id',
                'mapping_class_children.class_id',
                DB::raw('COUNT(students.id) as people_count'))
                ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
                ->leftJoin('mapping_class_children',function($q){
                    $q->on('mapping_class_children.class_id','class_transactions.id')
                        ->where('mapping_class_children.student_id','!=',0);
                })
                ->leftJoin('students',function($q){
                    $q->on('mapping_class_children.student_id','students.id')
                        ->where('students.Status','!=','non-aktif');
                })
                ->leftJoin('mapping_class_teachers','mapping_class_teachers.class_id','students.id')
                ->leftJoin('users','users.id','mapping_class_teachers.user_id')
                ->where(function($q) use ($keyword){
                    if(!is_null($keyword)){
                        $q->where('class_name','like',"%$keyword%")
                            ->orWhere('users.name','like',"%$keyword%")
                            ->orWhere('students.LongName','like',"%$keyword%");
                    } 
                })
                ->where('class_transactions.is_freeze','!=',1)
                ->orderBy('class_transactions.id','desc')
                ->groupBy('class_transactions.id')
                ->paginate(5);
        } else{
            $classes = ClassTransaction::select(
                'class_transactions.id',
                'class_name',
                'class_transaction_price',
                'class_transactions.Status',
                'class_type_id',
                'student_id',
                'mapping_class_children.class_id',
                DB::raw('COUNT(students.id) as people_count'))
                ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
                ->leftJoin('mapping_class_children',function($q){
                    $q->on('mapping_class_children.class_id','class_transactions.id')
                        ->where('mapping_class_children.student_id','!=',0);
                })
                ->leftJoin('students',function($q){
                    $q->on('mapping_class_children.student_id','students.id')
                        ->where('students.Status','!=','non-aktif');
                })
                ->leftJoin('mapping_class_teachers','mapping_class_teachers.class_id','students.id')
                ->leftJoin('users','users.id','mapping_class_teachers.user_id')
                ->where(function($q) use ($keyword){
                    if(!is_null($keyword)){
                        $q->where('class_name','like',"%$keyword%")
                            ->orWhere('users.name','like',"%$keyword%")
                            ->orWhere('students.LongName','like',"%$keyword%");
                    } 
                })
                ->where('class_transactions.is_freeze','!=',1)
                ->orderBy('class_transactions.id','desc')
                ->groupBy('class_transactions.id')
                ->paginate(5);
        }

        return view('head.class.index',compact('classes','sort'));
    }

    public function sorting($value,$type)
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

        return view('head.class.index',compact('classes','sort'));
    }

    public function indexType(){
        $types = ClassType::orderBy('id')->paginate(5);
        return view('head.class.classType',compact('types'));
    }

    public function addCoursePage(){
        return view('head.class.classTypeAdd');
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

        return redirect()->route('headClassTypePage')->with('msg','Success Create Course Data');
    }

    public function viewUpdateType(Request $req){
        $return_url = url()->previous();
        $type = ClassType::find($req->typeID);
        return view('head.class.classTypeUpdate',compact('type','return_url'));
    }

    public function updateType(Request $req){
        $type = ClassType::find($req->typeID);
        $type->class_price = $req->inputPrice;
        $type->save();

        DB::table('transactions as t')
        ->leftJoin('class_transactions as ct','t.class_transactions_id','ct.id')
        ->leftJoin('class_types as ct2','ct2.id','ct.class_type_id')
        ->where('ct2.id',$req->typeID)
        ->where('ct.is_freeze','!=',1)
            ->update([
                't.price' => $req->inputPrice,
                'ct.class_transaction_price' => $req->inputPrice
            ]);

        return redirect()->to($req->return_url)->with('msg','Success Update Course Data');
    }

    public function DeleteType(Request $req){
        $type = ClassType::find($req->typeID)->delete();
        return redirect()->back()->with('msg','Success Delete Course Data');
    }

    public function active(){
        $classes = ClassTransaction::where('Status','aktif')->paginate(5);
        return view('head.class.index',compact('classes'));
    }

    public function nonActive(){
        $classes = ClassTransaction::where('Status','non-aktif')->paginate(5);
        return view('head.class.index',compact('classes'));
    }

    public function search(Request $req){
        $classes = ClassTransaction::join('class_types','class_transactions.class_type_id','class_types.id')
            ->where('class_name','like',"%$req->search%")->paginate(5);
        return view('head.class.index',compact('classes'));
    }

    public function insertPage(){
        $types = ClassType::all();
        $users = User::all()->where('role','teacher');
        return view('head.class.insert',compact('types','users'));
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
        $course = DB::table('class_types')->where('id',$req->inputType)->first();

        $class = new ClassTransaction();
        $class->class_type_id = $course->id;
        $class->Status = 'aktif';
        $class->is_freeze = 0;
        $class->class_transaction_price = $course->class_price;

        $class->save();

        $map = new MappingClassTeacher();
        $map->class_id = $class->id;
        $map->user_id = $req->inputTeacher;

        $map->save();

        $map2 = new MappingClassChild();
        $map2->class_id = $class->id;

        $map2->save();

        return redirect()->route('headClassPage')->with('msg','Success Create Class');
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
        return redirect()->back()->with('msg','Success Delete Class');
    }

    public function detailClass(Request $req, $id){
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
            ->where('class_transactions.id', $id)
            ->paginate(5, ['*'], 'teachers');

        $teachers->appends(['teachers' => request('teachers')]);

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
        mapping_class_children.quota as studentMaxQuota,
        students.Status as studentStatus
        ')
        ->where('students.Status','!=','non-aktif')
        ->where('class_transactions.id', $id)
        ->paginate(5, ['*'], 'students');

        // $transactions = DB::table('transactions')
        //             ->where('class_transactions_id',$class_id)
        //             ->selectRaw("
        //                 students_id,
        //                 sum(Quota) as paid
        //             ")
        //             ->where('payment_status','Paid')
        //             // ->whereRaw('transaction_date >= curdate()')
        //             ->groupBy('students_id')
        //             ->get();

        $students->appends(['students' => request('students')]);

        return view('head.class.detail',compact('teachers','students','id','class_name'));
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

        return redirect()->route("headDetailClass", ['id' => $class_id])->with('msg','Success Reset Quota');
    }


    public function viewaddTeacher(Request $req){
        $class_id = $req->id;
        $teachers = DB::table('users')->where('role',"LIKE","teacher")
            ->whereNotIn('id',function ($q) use ($class_id){
                $q->select('mapping_class_teachers.user_id')
                    ->from('mapping_class_teachers')
                    ->where('class_id','=',$class_id);
            })

            ->paginate(5);
        return view('head.class.viewTeacher',compact('teachers','class_id'));
    }

    public function addTeacher(Request $req){
        $mappingTeacher = new MappingClassTeacher();
        $mappingTeacher->user_id = $req->teacherId;
        $mappingTeacher->class_id = $req->classId;
        $mappingTeacher->Save();
        return redirect()->route("headDetailClass", ['id' => $req->classId])->with('msg','Success Add Teacher');
    }

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
        return view('head.class.viewStudent',compact('students','class_id'));
    }

    public function addStudent(Request $req){
        $class_id = $req->classId;
        $get_class_price = ClassTransaction::leftJoin('class_types','class_types.id','class_transactions.class_type_id')
            ->where('class_transactions.id',$class_id)->first();

        $get_student = Student::where('id',$req->studentId)->first();
        $quota=0;
        if($get_class_price->class_name == 'Pointe Class') $quota = 4;
        else if($get_class_price->class_name == 'Intensive Kids' || $get_class_price->class_name == 'Intensive Class')$quota = 12;
        else $quota = 8;
        $check_schedule = Schedule::where('class_id',$class_id)
            ->whereRaw('date  >= curdate()')
            ->orderBy('date')
            ->first();

        if(!is_null($check_schedule) && $get_student->Status == 'aktif'){
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

            $mappingStudent = new MappingClassChild();
            $mappingStudent->student_id = $req->studentId;
            $mappingStudent->class_id = $class_id;
            $mappingStudent->Save();
            return redirect()->route("headDetailClass", ['id' => $class_id])->with('msg','Success Add Student');
        }

        
        return redirect()->route("headDetailClass", ['id' => $class_id])->with('msg',"Schedule Class Doesn't Greater Than Today or Student Status is not Active");
    }

    public function deleteTeacher($teacher, $class){
        $teacher = DB::table('mapping_class_teachers')->where('class_id',$class)->where('user_id',$teacher);
        $teacher->delete();
        return redirect()->route("headDetailClass", ['id' => $class])->with('msg','Success Delete Teacher');
    }

    public function deleteStudent($student, $class){
        $teacher = DB::table('mapping_class_children')->where('class_id',$class)->where('student_id',$student);
        $teacher->delete();
        return redirect()->route("headDetailClass", ['id' => $class])->with('msg','Success Delete Student');
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
        return redirect()->route("headDetailClass", ['id' => $class])->with('msg','Success Generate Transaction Student');
    }

    public function resetClass($id){
        $classScheduleReset = DB::table('schedules')->where('class_id',$id);
        $classScheduleReset->delete();
        return redirect()->route("headClassPage")->with('msg','Success Reset Class');
    }

    public function levelUp(Request $req){
        $return_url = url()->previous();
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

        return view('head.class.levelUp',compact('students','class_id'));
    }

    public function levelUpStudent(Request $req){
        // $class = ClassTransaction::where('id',$req->class_id)->first();
        // $class->class_type_id += 1;
        // $class->save();

        // $class_id = $req->class_id;
        // $data = DB::table('class_transactions')
        // ->join('mapping_class_children','mapping_class_children.class_id','class_transactions.id')
        // ->join('students','mapping_class_children.student_id','students.id')
        // ->where('class_transactions.id', $class_id)
        // ->get();
        // foreach($data as $d){
        //     DB::table('students')->where('id',$d->student_id)->update([
        //         'MaxQuota' => ($d->MaxQuota - $d->Quota),
        //         'Quota' => 0,
        //         'is_new' => 0,
        //     ]);
        // }
        $class_id = $req->classId;
        DB::table('class_transactions')->where('id', $class_id)->update([
            'is_freeze' => 1
        ]);

        // MappingClassChild::where('class_id',$req->class_id)->delete();
        return redirect()->to($req->return_url)->with('msg','Success Level up All Student');
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
        return view('head.class.absen',compact("view","class"));
        // return redirect()->route('headClassPage')->with('msg','Teacher havent get attendence');;
    }

    public function getAbsen(Request $req, Schedule $schedule){
        $getTeacher = DB::table('schedules as s')
                            ->leftJoin('class_transactions as ct','ct.id','s.class_id')
                            ->leftJoin('mapping_class_teachers as mct','ct.id','mct.class_id')
                            ->where('s.id',$schedule->id)->first();
        $header_check = DB::table("header_absens")->where('schedules_id',$schedule->id)->where('teacher_id',$getTeacher->user_id)->first();

        if(is_null($header_check)){
            $header_id = DB::table('header_absens')->insertGetId([
                'schedules_id' => $schedule->id,
                'teacher_id' => $getTeacher->user_id,
            ]);
        } else $header_id = $header_check->id;
        $check_detail = DB::table("detail_absens")->where('header_absen_id',$header_id)->get(); 

        $students = Student::whereIn('nis',$req->nis)->get();
        for($i = 0;$i < count($req->nis);$i++){
            if(count($check_detail)){
                DetailAbsen::where('header_absen_id',$header_id)
                    ->where('student_id',$students[$i]->id)
                    ->update([
                    'Description' => $req->check[$i] == "on" ? "Attend" : $req->keterangan[$i],
                    'Notes' => $req->keterangan[$i] == "Ijin" ? $req->notes[$i] : '',
                ]);
            } else {
                DetailAbsen::create([
                    'Description' => $req->check[$i] == "on" ? "Attend" : $req->keterangan[$i],
                    'Notes' => $req->keterangan[$i] == "Ijin" ? $req->notes[$i] : '',
                    'header_absen_id' => $header_id,
                    'student_id' => $students[$i]->id,
                ]);
            }
        }
        return redirect()->route("headViewScheduleClass",['classId' => $schedule->class_id])->with('msg','Success Update Attendance');
    }
}
