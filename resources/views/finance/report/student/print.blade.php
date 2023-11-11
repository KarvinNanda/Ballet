@inject('carbon', 'Carbon\Carbon')
    <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Report Finance Active Student</title>
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
            <h2>Report Finance Active Student</h2>
        </div>

        <div class="col text-center">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Teacher</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach($report as $item)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$item->name}}</td>
                        <td>{{$item->class}}</td>
                        <td>{{$item->teacher}}</td>
                        <td>Rp.{{number_format($item->price)}}</td>
                        <td>{{$item->discount}}%</td>
                        @if($item->discount != 0)
                            <td>Rp.{{number_format($item->price - (($item->discount/100)*$item->price))}}</td>
                        @else
                            <td>Rp.{{number_format($item->price)}}</td>
                        @endif
                        <td>{{$item->status}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
{{--</div>--}}




<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
</body>
</html>
