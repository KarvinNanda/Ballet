@inject('carbon', 'Carbon\Carbon')
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Report Teacher Reward</title>
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous"> --}}
    <style>
        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
            margin-left: auto;
            margin-right: auto;
            /*text-align: left;*/
            /*width: 50%;*/
        }

        th,
        td {
            background-color: white;
            text-align: center;
            padding: 3px;
            /*text-wrap: none;*/
        }

        body {
            margin: 0px;
            padding: 0px;
            text-align: center;
        }

        .text-start{
            text-align: left;
            margin-left: 100px;
        }
    </style>
</head>

<body>
    {{-- <div class="container"> --}}


    <div class="row d-block p-3">
        <div class="col">
            <h2>Report Teacher {{ $getmonth[0]->month }}</h2>
        </div>

        <div class="col text-center">
            @foreach ($report as $item)
                @php
                    $total_all = 0;
                @endphp
                <h3 class="text-start">Teacher : {{ $item[0]->teacherName }}</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Price</th>
                            <th>Meet</th>
                            <th>Fee / Meeting</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($item as $i)
                            <tr>
                                <td>{{ $i->studentName }}</td>
                                <td>{{ $i->class_name }}</td>
                                <td>Rp.{{ number_format($i->price) }}</td>
                                <td>{{ $i->meet }}</td>
                                <td>Rp.{{ number_format($i->paid) }}</td>
                                @if ($i->class_name == 'Intensive Kids')
                                    <td>Rp.{{ number_format($i->price / 4) }}</td>
                                @else
                                    <td>Rp.{{ number_format(($i->paid * $i->meet * $i->teacher_reward) / 100) }}</td>
                                @endif

                            </tr>
                            @php
                                $total_all += ($i->paid * $i->meet * $i->teacher_reward) / 100;
                            @endphp
                        @endforeach
                        <tr>
                            <td class="text-end pe-5" colspan="5">Grand Total</td>
                            <td class="text-center">Rp.{{ number_format($total_all) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endforeach
        </div>
    </div>
    {{-- </div> --}}




    {{-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
        integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"
        integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous">
    </script> --}}
</body>

</html>
