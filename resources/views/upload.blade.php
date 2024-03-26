@extends('templates.header')

@section('body')
<link rel="stylesheet" href="{{ asset('../resources/css/forms.css') }}">

<div class="uploadContainer">
    <form method="POST" action="{{ url('/uploadFile') }}" enctype="multipart/form-data">
        @csrf
        <input class="imageInput" type="file" name="file"><br><br>
        <button class="submitButton" type="submit">Enviar archivo</button><br>
    </form>
</div>

@endsection