@inject('carbon', 'Carbon\Carbon')
    <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Report Class Attendence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            text-align: center;
        }
        th, td {
            background-color: white;
            padding: 3px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row d-block p-3">
        <div class="col">
            <h2>Laporan Kelas {{$className}}</h2>
            <h3>Guru : {{$teacher}}</h3>
        </div>

        <div class="row">
            <div class="col">
                <div class="col text-center">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Student Name</th>
                            @foreach($report as $items)
                                <th>{{$carbon::parse($items[0]->date)->format('d M Y')}}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($first as $f)
                            @php
                                $temp = $f->student_name;
                            @endphp
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{$f->student_name}}</td>
                                @foreach($report as $items)
                                    @foreach($items as $item)
                                        @if($item->description == 'Attend' && $item->student_name == $temp)
                                            <td>V</td>
                                        @elseif($item->description == 'Sick' && $item->student_name == $temp)
                                            <td>S</td>
                                        @elseif($item->description == 'Permission' && $item->student_name == $temp)
                                            <td>I</td>
                                        @elseif($item->description == 'Absent' && $item->student_name == $temp)
                                            <td>A</td>
                                        @endif
                                    @endforeach
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <h3>Catatan</h3>
                <p>V : Hadir</p>
                <p>S : Sakit</p>
                <p>I : Izin</p>
                <p>A : Absen</p>
            </div>
        </div>
    </div>
</div>




<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
</body>
</html>
