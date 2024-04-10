@extends('templates.header')

@section('body')

<link rel="stylesheet" href="{{ asset('../resources/css/file.css') }}">

<img class="viewImage" src="../../storage/app/{{ $image }}" alt="">

@endsection