<?php

namespace App\Http\Controllers\staff;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\Staff\ClassIdRequest;
use App\Http\Requests\Staff\ClassListRequest;
use App\Http\Requests\Staff\FreezePriceRequest;
use App\Http\Requests\Staff\MapStudentRequest;
use App\Http\Requests\Staff\MapTeacherRequest;
use App\Http\Requests\Staff\StoreClassRequest;
use App\Http\Requests\Staff\StoreCourseRequest;
use App\Http\Controllers\Controller;
use App\Models\ClassTransaction;
use App\Models\ClassType;
use App\Models\MappingClassChild;
use App\Models\MappingClassTeacher;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ClassController extends Controller
{
    public function index(ClassListRequest $request){
        $sort = 'asc';
        $classes = $this->listQuery(false, $request->query('keyword'), $request->query('status', 'all'))
            ->orderBy('class_transactions.id','desc')
            ->paginate(5)
            ->withQueryString();

        return view('staff.class.index', compact('classes','sort'));
    }

    public function sort($column,$direction){
        [$column, $direction] = $this->sortOrFail($column, $direction, ['class_name', 'status']);
        $classes = $this->listQuery(false, null, 'all')
            ->orderBy($column === 'status' ? 'class_transactions.Status' : 'class_types.class_name', $direction)
            ->paginate(5)
            ->withQueryString();
        $sort = $direction == 'asc' ? 'desc':'asc';

        return view('staff.class.index', compact('classes','sort'));
    }

    /**
     * Class rows for the list pages, one row per class.
     * Teachers join on the class id; people_count counts students that are not non-aktif.
     */
    private function listQuery(bool $frozen, ?string $keyword, string $status){
        return ClassTransaction::with(['Type', 'mapping.getUser'])
            ->select(
                'class_transactions.id',
                'class_types.class_name',
                'class_transactions.class_transaction_price',
                'class_transactions.Status',
                'class_transactions.class_type_id',
                DB::raw('COUNT(DISTINCT students.id) as people_count'))
            ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
            ->leftJoin('mapping_class_children','mapping_class_children.class_id','class_transactions.id')
            ->leftJoin('students',function($q){
                $q->on('mapping_class_children.student_id','students.id')
                    ->where('students.Status','!=','non-aktif');
            })
            ->leftJoin('mapping_class_teachers','mapping_class_teachers.class_id','class_transactions.id')
            ->leftJoin('users','users.id','mapping_class_teachers.user_id')
            ->where('class_transactions.is_freeze', $frozen ? '=' : '!=', 1)
            ->when($status !== 'all', fn ($q) => $q->where('class_transactions.Status', $status))
            ->when($keyword, function ($q, $keyword) {
                $q->where(function ($q) use ($keyword) {
                    $q->where('class_types.class_name','like',"%$keyword%")
                        ->orWhere('users.name','like',"%$keyword%")
                        ->orWhere('students.LongName','like',"%$keyword%");
                });
            })
            ->groupBy('class_transactions.id');
    }

    public function create(){
        $types = ClassType::all();
        $users = User::where('role','teacher')->get();
        return view('staff.class.insert',compact('types','users'));
    }

    public function store(StoreClassRequest $req){
        $course = ClassType::findOrFail($req->inputType);

        DB::transaction(function () use ($course, $req) {
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
        });

        return redirect(staff_route('class.index'))->with('msg','Success Create Class');
    }

    public function createCourse(){
        return view('staff.classType.insert');
    }

    public function storeCourse(StoreCourseRequest $req){
        $type = new ClassType();
        $type->class_name = $req->inputName;
        $type->class_price = $req->inputPrice;
        $type->save();

        return redirect(staff_route('class-type.index'))->with('msg','Success Create Course');
    }

    public function show(ClassTransaction $class){
        return $this->detailPage($class, 'staff.class.detail');
    }

    public function freezeShow(ClassTransaction $class){
        return $this->detailPage($class, 'staff.class.detailFreeze');
    }

    /** Detail of one class. Read only: it never deletes mappings (trial cleanup is a daily command). */
    private function detailPage(ClassTransaction $class, string $view){
        if(! Schedule::where('class_id',$class->id)->exists()){
            return redirect()->back()->with('error','Please Create Schedule First');
        }

        $class_id = $class->id;
        $class_name = $class->Type?->class_name;

        $teachers = DB::table('mapping_class_teachers')
            ->join('users','mapping_class_teachers.user_id','users.id')
            ->selectRaw('
                users.id as id,
                users.name as teacherName,
                users.dob as teacherDOB,
                users.address as teacherAddress,
                users.email as teacherEmail,
                users.phone as teacherPhone
            ')
            ->where('mapping_class_teachers.class_id',$class_id)
            ->paginate(5, ['*'], 'teachers')
            ->withQueryString();

        $students = DB::table('mapping_class_children')
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
            ->where('mapping_class_children.class_id',$class_id)
            ->paginate(5, ['*'], 'students')
            ->withQueryString();

        return view($view, compact('teachers','students','class_id','class_name'));
    }

    public function toggleStatus(ClassTransaction $class){
        $class->Status = $class->Status == 'aktif' ? 'non-aktif' : 'aktif';
        $class->save();
        return redirect()->back();
    }

    public function destroy(ClassTransaction $class){
        $class->delete();
        return redirect()->back()->with('msg','Success Delete Class');
    }

    public function resetQuota(ClassTransaction $class){
        $data = DB::table('mapping_class_children')
            ->join('students','mapping_class_children.student_id','students.id')
            ->where('mapping_class_children.class_id', $class->id)
            ->get();
        foreach($data as $d){
            DB::table('students')->where('id',$d->student_id)->update([
                'MaxQuota' => ($d->MaxQuota - $d->Quota),
                'Quota' => 0,
                'is_new' => 0,
            ]);
        }
        return redirect(staff_route('class.show', $class))->with('msg','Success Reset Quota');
    }

    public function resetClass(ClassTransaction $class){
        DB::table('schedules')->where('class_id',$class->id)->delete();
        return redirect(staff_route('class.index'))->with('msg','Success Reset Class');
    }

    public function teacherCreate(ClassTransaction $class){
        $class_id = $class->id;
        $teachers = DB::table('users')->where('role','teacher')
            ->whereNotIn('id',function ($q) use ($class_id){
                $q->select('mapping_class_teachers.user_id')
                    ->from('mapping_class_teachers')
                    ->where('class_id','=',$class_id);
            })
            ->paginate(5);
        return view('staff.class.viewTeacher',compact('teachers','class_id'));
    }

    public function teacherStore(MapTeacherRequest $req){
        $exists = DB::table('mapping_class_teachers')->where('class_id', $req->classId)->where('user_id', $req->teacherId)->exists();
        if ($exists) {
            return redirect(staff_route('class.show', $req->classId))->with('error', 'Teacher is already in this class');
        }

        $mappingTeacher = new MappingClassTeacher();
        $mappingTeacher->user_id = $req->teacherId;
        $mappingTeacher->class_id = $req->classId;
        $mappingTeacher->save();
        return redirect(staff_route('class.show', $req->classId))->with('msg','Success Add Teacher');
    }

    public function teacherDestroy(int $teacher, ClassTransaction $class){
        DB::table('mapping_class_teachers')->where('class_id',$class->id)->where('user_id',$teacher)->delete();
        return redirect(staff_route('class.show', $class))->with('msg','Success Delete Teacher');
    }

    public function studentCreate(SearchRequest $req, ClassTransaction $class){
        $class_id = $class->id;
        $keyword = $req->keyword;
        $students = DB::table('students')
            ->whereNotIn('id',function($q) use ($class_id){
                $q->select('mapping_class_children.student_id')
                    ->from('mapping_class_children')
                    ->where('mapping_class_children.class_id',$class_id);
            })
            ->when($keyword, fn ($q) => $q->where('students.LongName','like',"%$keyword%"))
            ->whereIn('students.Status', ['aktif', 'trial'])
            ->paginate(5)
            ->withQueryString();
        return view('staff.class.viewStudent',compact('students','class_id'));
    }

    public function studentStore(MapStudentRequest $req){
        $class_id = (int) $req->classId;
        $class = ClassTransaction::with('Type')->findOrFail($class_id);
        $student = Student::findOrFail($req->studentId);

        $exists = DB::table('mapping_class_children')->where('class_id', $class_id)->where('student_id', $student->id)->exists();
        if ($exists) {
            return redirect(staff_route('class.show', $class_id))->with('error', 'Student is already in this class');
        }

        $firstSchedule = $this->nextSchedule($class_id);

        if(is_null($firstSchedule) || $student->Status != 'aktif'){
            return redirect(staff_route('class.show', $class_id))->with('error',"Schedule Class Doesn't Greater Than Today or Student Status is not Active");
        }

        try {
            DB::transaction(function () use ($student, $class, $firstSchedule) {
                DB::table('transactions')->insert(
                    $this->transactionRows($student->id, $class->id, $class->Type?->class_price, $firstSchedule->date, $this->sessionQuota($class->Type?->class_name))
                );

                $mappingStudent = new MappingClassChild();
                $mappingStudent->student_id = $student->id;
                $mappingStudent->class_id = $class->id;
                $mappingStudent->save();
            });
        } catch (UniqueConstraintViolationException) {
            // A concurrent request inserted the mapping after the check; the transaction rolled back.
            return redirect(staff_route('class.show', $class_id))->with('error', 'Student is already in this class');
        }

        return redirect(staff_route('class.show', $class_id))->with('msg','Success Add Student');
    }

    public function studentDestroy(int $student, ClassTransaction $class){
        DB::table('mapping_class_children')->where('class_id',$class->id)->where('student_id',$student)->delete();
        return redirect(staff_route('class.show', $class))->with('msg','Success Delete Student');
    }

    public function generateTransaction(int $student, ClassTransaction $class){
        $student = Student::findOrFail($student)->id;
        abort_unless(
            DB::table('mapping_class_children')->where('class_id',$class->id)->where('student_id',$student)->exists(),
            404
        );
        $firstSchedule = $this->nextSchedule($class->id);

        if(! is_null($firstSchedule)){
            DB::table('transactions')->insert(
                $this->transactionRows($student, $class->id, $class->Type?->class_price, $firstSchedule->date, null)
            );
        }
        return redirect(staff_route('class.show', $class))->with('msg','Success Generate Transaction Student');
    }

    private function nextSchedule(int $classId): ?Schedule
    {
        return Schedule::where('class_id',$classId)
            ->whereRaw('date >= curdate()')
            ->orderBy('date')
            ->first();
    }

    /** Sessions per month by course name. */
    private function sessionQuota(?string $courseName): int
    {
        return match ($courseName) {
            'Pointe Class' => 4,
            'Intensive Kids', 'Intensive Class' => 12,
            default => 8,
        };
    }

    /** Three monthly Unpaid transactions: on the first schedule date, then on the 10th of the next two months. */
    private function transactionRows(int $studentId, int $classId, $price, $firstDate, ?int $quota): array
    {
        $rows = [];
        for ($i = 0; $i < 3; $i++) {
            $rows[] = [
                'students_id' => $studentId,
                'class_transactions_id' => $classId,
                'transaction_date' => $i === 0
                    ? Carbon::parse($firstDate)->setTime(0,0,0)
                    : Carbon::parse($firstDate)->addMonthsNoOverflow($i)->setDay(10),
                'payment_status' => 'Unpaid',
                'discount' => 0,
                'price' => $price,
                'desc' => '-',
                'transaction_quota' => $quota,
            ];
        }
        return $rows;
    }

    /** Confirmation page before freezing a class. */
    public function levelUp(ClassIdRequest $req){
        $return_url = url()->previous();
        $class_id = (int) $req->classId;

        $students = DB::table('mapping_class_children')
            ->join('students','mapping_class_children.student_id','students.id')
            ->selectRaw('
                students.id as id,
                students.LongName as studentName,
                students.Dob as studentDOB,
                students.Email as studentEmail
            ')
            ->where('mapping_class_children.class_id',$class_id)
            ->get();

        return view('staff.class.levelUp',compact('students','class_id','return_url'));
    }

    public function levelUpStudent(ClassIdRequest $req){
        DB::table('class_transactions')->where('id', $req->classId)->update([
            'is_freeze' => 1
        ]);

        return $this->backTo($req->return_url)->with('msg','Success Freeze Class');
    }

    public function freezeIndex(ClassListRequest $request){
        $sort = 'asc';
        $classes = $this->listQuery(true, $request->query('keyword'), $request->query('status', 'all'))
            ->orderBy('class_transactions.id','desc')
            ->paginate(5)
            ->withQueryString();

        return view('staff.class.viewFreeze', compact('classes','sort'));
    }

    public function freezeEdit(ClassTransaction $class){
        Gate::authorize('class.freeze-price');
        $return_url = url()->previous();
        $class_id = $class->id;
        $class = DB::table('class_transactions as ct')
            ->join('class_types as ct2','ct2.id','ct.class_type_id')
            ->leftJoin('mapping_class_teachers as mct','mct.class_id','ct.id')
            ->leftJoin('users as u','mct.user_id','u.id')
            ->selectRaw("
                ct.class_transaction_price,
                ct2.class_name,
                u.name
            ")
            ->where('ct.id',$class_id)
            ->first();
        abort_if($class === null, 404);
        return view('staff.class.updateFreeze',compact('class','class_id','return_url'));
    }

    public function freezeUpdate(FreezePriceRequest $req, ClassTransaction $class){
        $class->class_transaction_price = $req->inputPrice;
        $class->save();

        return $this->backTo($req->return_url)->with('msg','Success Update Class Freeze Data');
    }
}
