@extends('layouts.app')

@section('title', 'Frozen class · '.$class_label)

@section('content')
    @include('staff.class.partials.detail-page', ['actions' => false])
@endsection
