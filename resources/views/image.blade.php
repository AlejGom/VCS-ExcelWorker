@extends('templates.header')

@section('body')
<link rel="stylesheet" href="{{ asset('../resources/css/file.css') }}">

<div class="downloadContainer">
    <p><b>Descargar</b></p>
    <a class="fileName" href="{{ route('downloadFile', $image->id) }}"><b>{{ $image->name }}</b></a>
</div>
<div class="buttonContainerOnImage">
    <a href="{{ route('goBack') }}"><button class="backButton">Volver</button></a>
</div>

<img class="viewImage" src="../../storage/app/{{ $image->file_path }}">

@endsection