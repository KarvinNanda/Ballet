@inject('carbon', 'Carbon\Carbon')
    <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Report Active Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            /*text-align: left;*/
            /*width: 50%;*/
        }
        th, td {
            background-color: white;
            text-align: center;
            padding: 3px;
            /*text-wrap: none;*/
        }
        body{
            margin : 0px;
            padding : 0px;
        }
    </style>
</head>
<body>
{{--<div class="container">--}}
    <div class="row d-block p-3">
        <div class="col">
            <h2>Laporan Siswa Aktif</h2>
        </div>

        <div class="col text-center">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Class</th>
                    <th>NIS</th>
                    <th>Name</th>
                    <th>DOB</th>
                    <th>Parent</th>
                    <th>Sender</th>
                    <th>Bank</th>
                    <th>Rekening</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Whatsapp</th>
                    <th>Line</th>
                    <th>Instagram</th>
                    <th>Email</th>
                </tr>
                </thead>
                <tbody>
                @if(count($report))
                    @foreach($report as $item)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td width="120px">{{$item->class_name}}</td>
                            <td>{{$item->nis}}</td>
                            <td>{{$item->LongName}}</td>
                            <td width="120px">{{$carbon::parse($item->Dob)->format('d M')}} ({{$item->old}} Old)</td>
                            <td>{{$item->nama_orang_tua}}</td>
                            <td>{{$item->nama_pengirim}}</td>
                            <td>{{$item->bank_name}}</td>
                            <td>{{$item->bank_rek}}</td>
                            <td>{{$item->Address}},{{$item->City}},{{$item->kode_pos}}</td>
                            <td width="180px">Phone 1 : {{$item->Phone1}} <br> Phone 2 : {{$item->Phone2}}</td>
                            <td>{{$item->Whatsapp}}</td>
                            <td>{{$item->Line}}</td>
                            <td>{{$item->Instagram}}</td>
                            <td>{{$item->Email}}</td>
                        </tr>
                    @endforeach
                @else
                    <td colspan="15">None</td>
                @endif
                </tbody>
            </table>
        </div>
    </div>
{{--</div>--}}




<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
</body>
</html>
