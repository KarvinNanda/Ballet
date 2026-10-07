{{-- Student table of a class detail page. $actions: show Delete / Generate Transaction (not on frozen classes). --}}
@php
    $quotaPay = match ($class_name) {
        'Pointe Class' => 4,
        'Intensive Kids', 'Intensive Class' => 12,
        default => 3,
    };
@endphp
<table class="table table-striped">
    <thead>
    <tr>
        <th scope="col">Name</th>
        <th scope="col">DOB</th>
        <th scope="col">Address</th>
        <th scope="col">Email</th>
        <th scope="col">Phone</th>
        <th scope="col">Quota</th>
        @if($actions)
            <th scope="col">Action</th>
        @endif
    </tr>
    </thead>
    <tbody>
    @forelse($students as $student)
        <tr>
            <td>
                <a href="{{staff_route('student.show', $student->id)}}">{{$student->studentName}}</a>
            </td>
            <td>{{\Carbon\Carbon::parse($student->studentDOB)->format('d M Y')}}</td>
            <td>{{$student->studentAddress}}</td>
            <td>{{$student->studentEmail}}</td>
            <td>{{$student->studentPhone}}</td>
            @if($student->studentStatus != 'trial')
                <td>{{$student->studentQuota}} / {{$student->studentMaxQuota == 0 ? $quotaPay : $student->studentMaxQuota}}</td>
            @else
                <td>{{$student->studentQuota}} / 2</td>
            @endif
            @if($actions)
                <td class="d-flex">
                    <form action="{{staff_route('class.student.destroy', ['student' => $student->id, 'class' => $class_id])}}" method="post" data-confirm="Hapus data ini?">
                        @csrf
                        <button type="submit" class="btn btn-danger me-2">Delete</button>
                    </form>

                    <form action="{{staff_route('class.student.generate-transaction', ['student' => $student->id, 'class' => $class_id])}}" method="post">
                        @csrf
                        <button type="submit" class="btn btn-info">Generate Transaction</button>
                    </form>
                </td>
            @endif
        </tr>
    @empty
        <tr><td colspan="{{ $actions ? 7 : 6 }}">No Data</td></tr>
    @endforelse
    </tbody>
</table>
<div class="alert text-center" role="alert">
    {{$students->links()}}
</div>
