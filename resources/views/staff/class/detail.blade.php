@extends('layouts.app')

@section('title', 'Class · '.$class_label)

@section('content')
    @include('staff.class.partials.detail-page', ['actions' => true])
@endsection
