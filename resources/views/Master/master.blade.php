@php
    $user = \Illuminate\Support\Facades\Auth::user();
    $role = $user->role ?? null;
    $isBuyer = str_contains(url()->current(), 'buyer');
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>@yield('title')</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <link href="/assets/img/favicon.png" rel="icon">
    <link href="/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <link href="/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="/assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <link href="/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10.10.1/dist/sweetalert2.min.css">

    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10.16.6/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
</head>

<body class="{{ $isBuyer ? 'toggle-sidebar' : '' }}">

<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <a href="#" class="logo d-flex align-items-center">
            <span class="d-none d-lg-block">Hallo{{ $user ? ' , '.$user->name : '' }}</span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>

    @if($user)
        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">
                <li class="nav-item dropdown pe-3">
                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                        <span class="d-none d-md-block dropdown-toggle ps-2">{{ $user->name }}</span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header">
                            <h6>{{ $user->name }}</h6>
                            <span>{{ strtoupper($user->role) }}</span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('change-profile-page') }}">
                                <i class="bi bi-person"></i>
                                <span>Change Profile</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('change-password-page') }}">
                                <i class="bi bi-person"></i>
                                <span>Change Password</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('logout') }}">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Sign Out</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    @endif
</header>

@if($user)
<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route($role) }}">
                <span>Dashboard</span>
            </a>
        </li>

        @if($role === 'admin')
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#master-nav" data-bs-toggle="collapse" href="#">
                    <span>Master</span>
                </a>
                <ul id="master-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    <li><a href="{{ route('adminClassView') }}"><i class="bi bi-circle"></i><span>Class Data</span></a></li>
                    <li><a href="{{ route('adminClassFreezeView') }}"><i class="bi bi-circle"></i><span>Class Freeze Data</span></a></li>
                    <li><a href="{{ route('adminClassTypePage') }}"><i class="bi bi-circle"></i><span>Course Data</span></a></li>
                    <li><a href="{{ route('adminStudentView') }}"><i class="bi bi-circle"></i><span>Student Data</span></a></li>
                    <li><a href="{{ route('adminTeacherView') }}"><i class="bi bi-circle"></i><span>Teacher Data</span></a></li>
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('adminStockPage') }}"><span>Stock</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('adminTransactionPage') }}"><span>Transaction</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#report-nav" data-bs-toggle="collapse" href="#">
                    <span>Report</span>
                </a>
                <ul id="report-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    <li><a href="{{ route('adminClassReport') }}"><i class="bi bi-circle"></i><span>Class Attendance Report</span></a></li>
                    <li><a href="{{ route('adminPrintActiveStudentPage') }}"><i class="bi bi-circle"></i><span>Active Student Report</span></a></li>
                </ul>
            </li>

        @elseif($role === 'head')
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#master-nav" data-bs-toggle="collapse" href="#">
                    <span>Master</span>
                </a>
                <ul id="master-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    <li><a href="{{ route('headAdminPage') }}"><i class="bi bi-circle"></i><span>Admin Data</span></a></li>
                    <li><a href="{{ route('headClassPage') }}"><i class="bi bi-circle"></i><span>Class Data</span></a></li>
                    <li><a href="{{ route('headClassFreezeView') }}"><i class="bi bi-circle"></i><span>Class Freeze Data</span></a></li>
                    <li><a href="{{ route('headClassTypePage') }}"><i class="bi bi-circle"></i><span>Course Data</span></a></li>
                    <li><a href="{{ route('headStudentPage') }}"><i class="bi bi-circle"></i><span>Student Data</span></a></li>
                    <li><a href="{{ route('headTeacherPage') }}"><i class="bi bi-circle"></i><span>Teacher Data</span></a></li>
                    <li><a href="{{ route('headFinancePage') }}"><i class="bi bi-circle"></i><span>Finance Data</span></a></li>
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('headStockPage') }}"><span>Stock</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('headTransactionPage') }}"><span>Transaction</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('Rules') }}"><span>Rule & Regulation</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#report-nav" data-bs-toggle="collapse" href="#">
                    <span>Report</span>
                </a>
                <ul id="report-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    <li><a href="{{ route('headTeacherReportPage') }}"><i class="bi bi-circle"></i><span>Teacher Report</span></a></li>
                    <li><a href="{{ route('headClassReport') }}"><i class="bi bi-circle"></i><span>Class Attendance Report</span></a></li>
                    <li><a href="{{ route('headStockReport') }}"><i class="bi bi-circle"></i><span>Stock Report</span></a></li>
                    <li><a href="{{ route('headPrintActiveStudentPage') }}"><i class="bi bi-circle"></i><span>Active Student Report</span></a></li>
                </ul>
            </li>

        @elseif($role === 'teacher')
            <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('viewClass') }}"><span>Class</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('viewAllScheduleTeacher', ['id' => $user->id]) }}"><span>Schedule</span></a>
            </li>

        @elseif($role === 'finance')
            <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('financeTransaction') }}"><span>Transaction</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#report-nav" data-bs-toggle="collapse" href="#">
                    <span>Report</span>
                </a>
                <ul id="report-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    <li><a href="{{ route('financeStockReport') }}"><i class="bi bi-circle"></i><span>Stock Report</span></a></li>
                    <li><a href="{{ route('financeTeacherReportPage') }}"><i class="bi bi-circle"></i><span>Teacher Report</span></a></li>
                    <li><a href="{{ route('financeStudentReportPage') }}"><i class="bi bi-circle"></i><span>Finance Active Student Report</span></a></li>
                </ul>
            </li>
        @endif

    </ul>
</aside>
@endif

<main id="main" class="main">
    @if(session()->has('msg'))
        <div class="alert alert-success" role="alert">
            {{ session('msg') }}
        </div>
    @endif
    @yield('content')
</main>

<footer id="footer" class="footer">
    <div class="copyright">
        <strong><span>En Pointe International Ballet Studio</span></strong>
    </div>
    <div class="credits"></div>
</footer>

<a href="#" class="back-to-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
</a>

<script src="/assets/vendor/apexcharts/apexcharts.min.js"></script>
<script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/assets/vendor/chart.js/chart.min.js"></script>
<script src="/assets/vendor/echarts/echarts.min.js"></script>
<script src="/assets/vendor/quill/quill.min.js"></script>
<script src="/assets/vendor/simple-datatables/simple-datatables.js"></script>
<script src="/assets/vendor/php-email-form/validate.js"></script>
<script src="/assets/js/main.js"></script>

</body>
</html>
