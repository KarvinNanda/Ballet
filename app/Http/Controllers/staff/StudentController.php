<?php

namespace App\Http\Controllers\staff;

use App\Support\Like;
use App\Http\Requests\Staff\StoreStudentRequest;
use App\Http\Requests\Staff\StudentListRequest;
use App\Http\Requests\Staff\ToggleStudentStatusRequest;
use App\Http\Requests\Staff\UpdateStudentRequest;
use App\Http\Controllers\Controller;
use App\Models\Banks;
use App\Models\ClassTransaction;
use App\Models\MappingClassChild;
use App\Models\Rekenings;
use App\Models\Rules;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\UniqueConstraintViolationException;

class StudentController extends Controller
{
    private const PER_PAGE = 20;

    public function index(StudentListRequest $request){
        $sort = 'asc';
        $students = $this->filtered($this->listQuery(), $request)
            ->orderBy('students.id','desc')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('staff.student.index',compact('students','sort'));
    }

    public function sort(StudentListRequest $request, $column, $direction){
        [$column, $direction] = $this->sortOrFail($column, $direction, ['age', 'dob', 'name']);
        $students = $this->filtered($this->listQuery(), $request)
            ->orderBy($column,$direction)
            ->orderBy('students.id','desc')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
        $sort = $direction == 'asc' ? 'desc':'asc';
        return view('staff.student.index',compact('students','sort'));
    }

    /** Status and keyword filters of the list page. StudentListRequest has already dropped invalid values. */
    private function filtered($query, StudentListRequest $request){
        $status = $request->query('status', 'all');

        return $query
            ->when($status !== 'all', fn ($q) => $q->where('students.Status', $status))
            ->when(filled($keyword = $request->query('keyword')), function ($q) use ($keyword) {
                $like = Like::contains($keyword);
                $q->where(function ($q) use ($like) {
                    $q->where('students.LongName',"LIKE",$like)
                        ->orWhere('students.ShortName',"LIKE",$like)
                        ->orWhere('students.Instagram',"LIKE",$like)
                        ->orWhere('students.Phone1',"LIKE",$like)
                        ->orWhere('students.Phone2',"LIKE",$like)
                        ->orWhere('students.bank_rek',"LIKE",$like)
                        ->orWhere('students.nama_orang_tua',"LIKE",$like)
                        ->orWhere('students.Address',"LIKE",$like)
                        ->orWhere('rekenings.nama_pengirim',"LIKE",$like)
                        ->orWhere('banks.bank_name',"LIKE",$like);
                });
            });
    }

    /** Student rows for the list page. LEFT JOINs: a student without a bank account must still show up. */
    private function listQuery(){
        return DB::table('students')
            ->leftJoin('rekenings','students.bank_rek','rekenings.bank_rek')
            ->leftJoin('banks','rekenings.banks_id','banks.id')
            ->selectRaw('
                students.id as id,
                students.nis as nis,
                students.Status as status,
                students.LongName as name,
                students.Dob as dob,
                students.nama_orang_tua as ortu,
                students.Address as alamat,
                students.Phone1 as phone,
                students.Email as email,
                students.Line as line,
                students.Instagram as instagram,
                students.age,
                rekenings.bank_rek as rek,
                rekenings.nama_pengirim as pengirim
            ')
            ->distinct();
    }

    public function create(){
        $rules = Rules::orderBy('id')->get();
        return view('staff.student.insert',compact('rules'));
    }

    public function store(StoreStudentRequest $req){
        DB::transaction(function () use ($req) {
            // rekenings.bank_rek is NOT NULL: only write a row when an account number was given.
            // An existing row for the same number (a sibling's) is reused as is, not duplicated.
            if($req->filled('inputRekening') && ! Rekenings::where('bank_rek', $req->inputRekening)->exists()){
                $bank = $req->filled('inputBankName') ? Banks::firstOrCreate(['bank_name' => $req->inputBankName]) : null;

                try {
                    // Savepoint: a failed insert would otherwise abort the outer transaction.
                    DB::transaction(function () use ($req, $bank) {
                        $rekening = new Rekenings();
                        $rekening->bank_rek = $req->inputRekening;
                        $rekening->nama_pengirim = $req->inputNamaPengirim;
                        $rekening->banks_id = $bank?->id;
                        $rekening->save();
                    });
                } catch (UniqueConstraintViolationException) {
                    // A concurrent request created the same account first: reuse it.
                }
            }

            $student = new Student();
            $student->nis = $req->inputNis;
            $student->LongName = $req->inputLongName;
            $student->ShortName = $req->inputNickName;
            $student->Email = $req->inputEmail;
            $student->Dob = $req->inputDate_of_Birth;
            $student->Address  = $req->inputAddress;
            $student->nama_orang_tua = $req->inputParentName;
            $student->bank_rek = $req->filled('inputRekening') ? $req->inputRekening : null;
            $student->City = $req->inputCity;
            $student->kode_pos = $req->inputPostalCode;
            $student->Phone1 = $req->inputPhone1;
            $student->Phone2 = $req->inputPhone2;
            $student->Whatsapp = $req->inputWhatsapp ;
            $student->Instagram = $req->inputInstagram ?  '@'.$req->inputInstagram : '-';
            $student->Line = $req->inputLine ?: '-';
            $student->Status = 'aktif';
            $student->EnrollDate  = Carbon::now();
            $student->Quota  = 0;
            $student->is_new  = 0;
            $student->age  = round(now()->diff($req->inputDate_of_Birth)->days / 365);
            $student->save();
        });

        return redirect(staff_route('student.index'))->with('msg','Success Create Student');
    }

    public function show(Student $student){
        $return_url = url()->previous();
        $detail = DB::table('students')
            ->leftJoin('rekenings', 'students.bank_rek', 'rekenings.bank_rek')
            ->leftJoin('banks', 'banks.id', 'rekenings.banks_id')
            ->selectRaw('
                students.id as id,
                students.nis as nis,
                students.Status as status,
                students.LongName as LongName,
                students.ShortName as ShortName,
                students.Dob as dob,
                students.EnrollDate as EnrollDate,
                students.nama_orang_tua as nama_orang_tua,
                students.Address as Address,
                students.City as City,
                students.kode_pos as kode_pos,
                students.Phone1 as Phone1,
                students.Phone2 as Phone2,
                students.Whatsapp as Whatsapp,
                students.Instagram as Instagram,
                students.Line as Line,
                students.Email as Email,
                students.Quota as Quota,
                students.Status as Status,
                students.MaxQuota,
                students.is_new,
                students.age,
                rekenings.bank_rek as rek,
                rekenings.nama_pengirim as pengirim,
                banks.bank_name as bank
            ')->where('students.id', $student->id)->first();

        $courses_taken = DB::table('mapping_class_children')
            ->join('class_transactions', 'mapping_class_children.class_id', 'class_transactions.id')
            ->join('class_types', 'class_transactions.class_type_id', 'class_types.id')
            ->where('mapping_class_children.student_id', $student->id)
            ->groupBy('class_transactions.class_type_id')
            ->get();

        $transactions = Transaction::join('students','students.id','transactions.students_id')
            ->leftjoin('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->leftjoin('class_types','class_transactions.class_type_id','class_types.id')
            ->selectRaw('
                transactions.id,
                transactions.transaction_date,
                transactions.transaction_payment,
                transactions.payment_status,
                transactions.price as price,
                class_types.class_name,
                transactions.discount,
                transactions.transaction_quota
            ')
            ->where('students.id', $student->id)
            ->orderBy('transactions.id','desc')
            ->get();

        return view('staff.student.detail', compact('detail','courses_taken','transactions','return_url'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $saved = DB::transaction(function () use ($request, $student) {
            // Attendance increments Quota while this form may be open. Lock the row so the check and the write
            // see the same value, then only write Quota when the user changed it on a form that is not stale.
            $currentQuota = (int) DB::table('students')->where('id', $student->id)->lockForUpdate()->value('Quota');
            $openedQuota = (int) $request->validated('Quota_original');
            $quotaEdited = (int) $request->validated('Quota') !== $openedQuota;
            if($quotaEdited && $currentQuota !== $openedQuota){
                return 'stale-quota';
            }

            if(! $this->saveBankAccount($student, $request->accountno, $request->sender, $request->bank)){
                return false;
            }

            $student->nis = $request->nis;
            $student->LongName = $request->LongName;
            $student->ShortName = $request->ShortName;
            $student->Email = $request->Email;
            $student->Dob = $request->dob;
            $student->Address = $request->Address;
            $student->nama_orang_tua = $request->nama_orang_tua;
            $student->City = $request->city;
            $student->kode_pos = $request->kode_pos;
            $student->Phone1 = $request->Phone1;
            $student->Phone2 = $request->Phone2;
            $student->Whatsapp = $request->Whatsapp;
            $student->Instagram = $request->Instagram;
            $student->Line = $request->Line;
            $student->EnrollDate = $request->EnrollDate;
            $student->Quota = $quotaEdited ? $request->validated('Quota') : $currentQuota;
            if($request->filled('MaxQuota')) $student->MaxQuota = $request->MaxQuota;
            $student->Status = $request->status;
            $student->is_new = in_array(strtolower($request->is_new), ['no'], true) ? 0 : 1;

            $student->bank_rek = $request->accountno;
            $student->save();
            return true;
        });

        if($saved === 'stale-quota'){
            return redirect()->back()->withInput($request->except('Quota_original'))->with('error','Quota sudah berubah sejak halaman dibuka. Buka ulang halaman lalu coba lagi.');
        }

        if(! $saved){
            return redirect()->back()->withInput()->with('error','That account number belongs to another student with a different sender or bank. Use the same sender and bank, or a different account number.');
        }

        return $this->backTo($request->return_url)->with('msg','Success Update Student');
    }

    /**
     * Point the student at the account row for $accountNo. Returns false (nothing written) when refused.
     * rekenings has no primary key and siblings share one row by design:
     * - number unchanged, or no other student uses the target row: the submitted sender/bank are written to the row
     *   (when the number is unchanged this applies to every sibling on it, by design);
     * - target row used by another student and this student is not on it yet: attach only, never rewrite that row.
     *   If the submitted sender/bank differ from the row, refuse.
     * - target row does not exist: rename the old row in place if nobody else uses it, else insert a new one.
     */
    private function saveBankAccount(Student $student, string $accountNo, string $sender, ?string $bankName): bool
    {
        $old = $student->bank_rek;
        $bankName = ($bankName === null || $bankName === '') ? null : $bankName;
        $target = DB::table('rekenings')->where('bank_rek', $accountNo)->first();
        $usedBy = fn (string $no) => Student::where('bank_rek', $no)->where('id', '!=', $student->id)->exists();

        if($target && $accountNo !== $old && $usedBy($accountNo)){
            $sameBank = $bankName === null || Banks::where('bank_name', $bankName)->value('id') == $target->banks_id;
            return $sender === $target->nama_pengirim && $sameBank;
        }

        $values = ['nama_pengirim' => $sender, 'updated_at' => now()];
        if($bankName !== null){
            $values['banks_id'] = Banks::firstOrCreate(['bank_name' => $bankName])->id;
        }

        if($target){
            DB::table('rekenings')->where('bank_rek', $accountNo)->update($values);
        } elseif($old !== null && ! $usedBy($old) && DB::table('rekenings')->where('bank_rek', $old)->exists()){
            DB::table('rekenings')->where('bank_rek', $old)->update($values + ['bank_rek' => $accountNo]);
        } else {
            DB::table('rekenings')->insert($values + ['bank_rek' => $accountNo, 'created_at' => now()]);
        }
        return true;
    }

    public function toggleStatus(Student $student, ToggleStudentStatusRequest $req){
        $student->Status = match ($req->validated('stats')) {
            'Active' => 'aktif',
            'Inactive' => 'non-aktif',
            'Trial' => 'trial',
        };
        $student->save();
        return redirect()->back()->with('msg','Success Update Student Status');
    }

    public function destroy(Student $student){
        $student->delete();
        return redirect()->back()->with('msg','Success Delete Student');
    }

    public function classCreate(Student $student){
        $taken = DB::table('mapping_class_children')->where('student_id',$student->id)->pluck('class_id')->unique()->all();

        $data = DB::table('class_transactions as ct')
            ->leftJoin('class_types as ct2','ct2.id','ct.class_type_id')
            ->leftJoin('mapping_class_children as mcc','mcc.class_id','ct.id')
            ->leftJoin('mapping_class_teachers as mct','mct.class_id','ct.id')
            ->leftJoin('users as u','mct.user_id','u.id')
            ->when($taken, fn ($q) => $q->whereNotIn('ct.id',$taken))
            ->havingRaw('count(mcc.student_id) > 0')
            ->selectRaw("ct.id,ct2.class_name,u.name as user,count(mcc.student_id) as students")
            ->where('mcc.student_id','!=',0)
            ->groupBy('ct.id','ct2.class_name','u.name')
            ->orderBy('ct2.id')
            ->distinct()
            ->get();

        if(count($data) == 0){
            return redirect(staff_route('student.show', $student))->with('msg','No Classes Available');
        }
        $schedules = DB::table('schedules')->whereIn('class_id',$data->pluck('id')->toArray())->select('class_id')->distinct()->get()->pluck('class_id')->toArray();

        return view('staff.student.class',compact('data','student','schedules'));
    }

    public function classStore(ClassTransaction $class, Student $student){
        $back = redirect(staff_route('student.show', $student));

        if(MappingClassChild::where('class_id',$class->id)->where('student_id',$student->id)->exists()){
            return $back->with('error','Student is already in this class');
        }

        $classType = ClassTransaction::leftJoin('class_types','class_types.id','class_transactions.class_type_id')
            ->where('class_transactions.id',$class->id)->first();

        if($classType->class_name == 'Pointe Class') $quota = 4;
        else if($classType->class_name == 'Intensive Kids' || $classType->class_name == 'Intensive Class') $quota = 12;
        else $quota = 8;

        $check_schedule = Schedule::where('class_id',$class->id)
            ->whereRaw('date  >= curdate()')
            ->orderBy('date')
            ->first();

        if(is_null($check_schedule) || $student->Status != 'aktif'){
            return $back->with('error',"Schedule Class Doesn't Greater Than Today or Student Status is not Active");
        }

        $first = Carbon::parse($check_schedule->date)->setTime(0,0,0);
        $trans = [];
        for ($i=0;$i<3;$i++){
            $trans[] = [
                'students_id' => $student->id,
                'class_transactions_id' => $class->id,
                'transaction_date' => $i == 0 ? $first : $first->copy()->addMonthsNoOverflow($i)->setDay(10),
                'payment_status' => 'Unpaid',
                'discount' => 0,
                'price' => $classType->class_price,
                'desc' => '-',
                'transaction_quota' => $quota,
            ];
        }

        try {
            DB::transaction(function () use ($trans, $student, $class) {
                DB::table('transactions')->insert($trans);

                $mappingStudent = new MappingClassChild();
                $mappingStudent->student_id = $student->id;
                $mappingStudent->class_id = $class->id;
                $mappingStudent->save();
            });
        } catch (UniqueConstraintViolationException) {
            // A concurrent request added the student first; the transaction rolled back, so no extra bills.
            return $back->with('error','Student is already in this class');
        }

        return $back->with('msg','Success Add Student into Class');
    }
}
