@inject('carbon', 'Carbon\Carbon')
    <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Report Stock</title>
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous"> --}}
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
        }
        th, td {
            background-color: white;
            padding: 3px;
        }

        .col {
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row d-block p-3">
        <div class="col">
            <h2>Report Stock {{$carbon::parse($start_date)->toDateString() == $carbon::parse($end_date)->toDateString() ? $carbon::parse($start_date)->format('d M Y') : $carbon::parse($start_date)->format('d M Y')." - ".$carbon::parse($end_date)->format('d M Y')}}</h2>
        </div>

        <div class="col text-center">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Size</th>
                    <th>First Quantity</th>
                    <th>IN</th>
                    <th>OUT</th>
                    <th>Last Quantity</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $find=false;
                @endphp
                @foreach($stock as $item)
                    <tr>
                        <td>{{$item->name}}</td>
                        <td>{{$item->size}}</td>
                        @foreach($report as $subItem)
                            @if($subItem->stock_id == $item->id)
                            <td>{{$subItem->first_qty}}</td>
                            <td>{{$subItem->in_qty}}</td>
                            <td>{{$subItem->out_qty}}</td>
                            <td>{{$subItem->first_qty + $subItem->in_qty - $subItem->out_qty}}</td>
                            @php
                                $find=true;
                            @endphp
                            @break
                            @endif
                        @endforeach
                        @if(!$find)
                        <td>{{$item->quantity}}</td>
                            <td>0</td>
                            <td>0</td>
                            <td>{{$item->quantity}}</td>
                        @endif
                        @php
                            $find=false;
                        @endphp
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>




{{-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script> --}}
</body>
</html>
