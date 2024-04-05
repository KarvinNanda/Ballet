<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>@yield('title')</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="/assets/img/favicon.png" rel="icon">
    <link href="/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="/assets/vendor/simple-datatables/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <!-- Template Main CSS File -->
    <link href="/assets/css/style.css" rel="stylesheet">
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/sweetalert2@10.10.1/dist/sweetalert2.min.css'>

    <!-- Jquery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10.16.6/dist/sweetalert2.all.min.js"></script>
</head>

@if(!str_contains(url()->current(),'buyer'))
<body>
@else
<body class="toggle-sidebar">
@endif

<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
        <a href="" class="logo d-flex align-items-center">
            @if(\Illuminate\Support\Facades\Auth::check())
            <span class="d-none d-lg-block">Hallo , {{\Illuminate\Support\Facades\Auth::user()->name}}</span>
            @else
            <span class="d-none d-lg-block">Hallo</span>
            @endif
        </a>
        {{-- @if(\Illuminate\Support\Facades\Auth::check())
        @endif --}}
        <i class="bi bi-list toggle-sidebar-btn"></i>

    </div><!-- End Logo -->

    @if(\Illuminate\Support\Facades\Auth::check())
    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">

            <li class="nav-item dropdown pe-3">

                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <span class="d-none d-md-block dropdown-toggle ps-2">{{\Illuminate\Support\Facades\Auth::user()->name}}</span>
                </a><!-- End Profile Iamge Icon -->

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6>{{\Illuminate\Support\Facades\Auth::user()->name}}</h6>
                        <span>{{strtoupper(\Illuminate\Support\Facades\Auth::user()->role)}}</span>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{route('change-profile-page')}}">
                                                        <i class="bi bi-person"></i>
                            <span>Change Profile</span>
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{route('change-password-page')}}">
                                                        <i class="bi bi-person"></i>
                            <span>Change Password</span>
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{route('logout')}}">
                                                        <i class="bi bi-box-arrow-right"></i>
                            <span>Sign Out</span>
                        </a>
                    </li>

                </ul><!-- End Profile Dropdown Items -->
            </li><!-- End Profile Nav -->

        </ul>
    </nav><!-- End Icons Navigation -->
    @endif

</header><!-- End Header -->

<!-- ======= Sidebar ======= -->
@if(\Illuminate\Support\Facades\Auth::check())
<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link collapsed" href="{{route(\Illuminate\Support\Facades\Auth::user()->role)}}">
                <span>Dashboard</span>
            </a>
        </li><!-- End Dashboard Nav -->

        @auth
            @if(\Illuminate\Support\Facades\Auth::user()->role == 'admin' || \Illuminate\Support\Facades\Auth::user()->role == 'head')
                <li class="nav-item">
                    <a class="nav-link collapsed" data-bs-target="#tables-nav" href="#">
                        <span>Master</span>
                    </a>
                        @if(\Illuminate\Support\Facades\Auth::user()->role == 'admin')
                            <ul id="tables-nav" class="nav-content" data-bs-parent="#sidebar-nav">
                                <li>
                                    <a href="{{route('adminClassView')}}">
                                        <i class="bi bi-circle"></i><span>Class Data</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('adminClassFreezeView')}}">
                                        <i class="bi bi-circle"></i><span>Class Freeze Data</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('adminClassTypePage')}}">
                                        <i class="bi bi-circle"></i><span>Course Data</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('adminStudentView')}}">
                                        <i class="bi bi-circle"></i><span>Student Data</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('adminTeacherView')}}">
                                        <i class="bi bi-circle"></i><span>Teacher Data</span>
                                    </a>
                                </li>
                            </ul>
                        @elseif(\Illuminate\Support\Facades\Auth::user()->role == 'head')
                            <ul id="tables-nav" class="nav-content" data-bs-parent="#sidebar-nav">
                                <li>
                                    <a href="{{route('headAdminPage')}}">
                                        <i class="bi bi-circle"></i><span>Admin Data</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('headClassPage')}}">
                                        <i class="bi bi-circle"></i><span>Class Data</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('headClassFreezeView')}}">
                                        <i class="bi bi-circle"></i><span>Class Freeze Data</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('headClassTypePage')}}">
                                        <i class="bi bi-circle"></i><span>Course Data</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('headStudentPage')}}">
                                        <i class="bi bi-circle"></i><span>Student Data</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('headTeacherPage')}}">
                                        <i class="bi bi-circle"></i><span>Teacher Data</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="{{route('headFinancePage')}}">
                                        <i class="bi bi-circle"></i><span>Finance Data</span>
                                    </a>
                                </li>
                            </ul>
                        @endif
                    @endif
                    @endauth
                </li><!-- End Tables Nav -->

                @auth()
                    @if(\Illuminate\Support\Facades\Auth::user()->role == 'head')
                        <li class="nav-item">
                            <a class="nav-link collapsed" href="{{route('headStockPage')}}">
                                <span>Stock</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link collapsed" href="{{route('headTransactionPage')}}">
                                <span>Transaction</span>
                            </a>
                        </li>

                            <li class="nav-item">
                                <a class="nav-link collapsed" data-bs-target="#tables-nav" href="#">
                                    <span>Report</span>
                                </a>
                                    <ul id="tables-nav" class="nav-content" data-bs-parent="#sidebar-nav">
                                        <li>
                                            <a href="{{route('headTeacherReportPage')}}">
                                                <i class="bi bi-circle"></i><span>Teacher Report</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{route('headClassReport')}}">
                                                <i class="bi bi-circle"></i><span>Class Attendance Report</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{route('headStockReport')}}">
                                                <i class="bi bi-circle"></i><span>Stock Report</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{route('headPrintActiveStudentPage')}}">
                                                <i class="bi bi-circle"></i><span>Active Student Report</span>
                                            </a>
                                        </li>
                                    </ul>
                            </li>
                        @elseif(\Illuminate\Support\Facades\Auth::user()->role == 'admin')
                            <li class="nav-item">
                                <a class="nav-link collapsed" href="{{route('adminStockPage')}}">
                                    <span>Stock</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link collapsed" href="{{route('adminTransactionPage')}}">
                                    <span>Transaction</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link collapsed" data-bs-target="#tables-nav" href="#">
                                    <span>Report</span>
                                </a>
                                <ul id="tables-nav" class="nav-content" data-bs-parent="#sidebar-nav">
                                    <li>
                                        <a href="{{route('adminClassReport')}}">
                                            <i class="bi bi-circle"></i><span>Class Attendance Report</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{route('adminPrintActiveStudentPage')}}">
                                            <i class="bi bi-circle"></i><span>Active Student Report</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @elseif(\Illuminate\Support\Facades\Auth::user()->role == 'teacher')
                            <li class="nav-item">
                                <a class="nav-link collapsed" href="{{route('viewClass')}}">
                                    <span>Class</span>
                                </a>
                                <a class="nav-link collapsed" href="{{ route('viewAllScheduleTeacher', ['id' => auth()->id()]) }}">
                                    <span>Schedule</span>
                                </a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link collapsed" href="{{route('financeTransaction')}}">
                                    <span>Transaction</span>
                                </a>
                            </li>
                        <li class="nav-item">
                            <a class="nav-link collapsed" data-bs-target="#tables-nav" href="#">
                                <span>Report</span>
                            </a>
                            <ul id="tables-nav" class="nav-content" data-bs-parent="#sidebar-nav">
                                <li>
                                    <a href="{{route('financeStockReport')}}">
                                        <i class="bi bi-circle"></i><span>Stock Report</span>
                                    </a>
                                    <a href="{{route('financeTeacherReportPage')}}">
                                        <i class="bi bi-circle"></i><span>Teacher Report</span>
                                    </a>
                                    <a href="{{route('financeStudentReportPage')}}">
                                        <i class="bi bi-circle"></i><span>Finance Active Student Report</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                @endauth


    </ul>

</aside><!-- End Sidebar-->
@endif

<main id="main" class="main">
    @if(Session()->has('msg'))
        <div class="alert alert-success" role="alert">
            {{session('msg')}}
        </div>
    @endif
    @yield('content')
</main><!-- End #main -->

<!-- ======= Footer ======= -->
<footer id="footer" class="footer">
    <div class="copyright">
        <strong><span>
En Pointe International Ballet Studio</span></strong>
    </div>
    <div class="credits">
    </div>
</footer><!-- End Footer -->

<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<!-- Vendor JS Files -->
<script src="/assets/vendor/apexcharts/apexcharts.min.js"></script>
<script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/assets/vendor/chart.js/chart.min.js"></script>
<script src="/assets/vendor/echarts/echarts.min.js"></script>
<script src="/assets/vendor/quill/quill.min.js"></script>
<script src="/assets/vendor/simple-datatables/simple-datatables.js"></script>
<script src="/assets/vendor/tinymce/tinymce.min.js"></script>
<script src="/assets/vendor/php-email-form/validate.js"></script>

<!-- Template Main JS File -->
<script src="/assets/js/main.js"></script>
<script src="/assets/js/jquery-3.6.3.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

</body>

</html>
