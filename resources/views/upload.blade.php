@extends('templates.header')

@section('body')
<link rel="stylesheet" href="{{ asset('../resources/css/forms.css') }}">

<div class="uploadContainer">
    <form method="POST" action="{{ url('/uploadFile') }}" enctype="multipart/form-data">
        @csrf
        <label for="file" class="fileLabel">Selecciona un archivo:</label>
        <input class="imageInput" type="file" name="file"><br><br>
        <button class="submitButton" type="submit">Enviar archivo</button><br>
    </form>
</div>
<div class="imageTemplate">
    <p>El archivo excel debe tener dos filas al inicio, la segunda con los nombres de las columnas</p>
    <img src="{{ asset('../resources/images/CapturaPlantilla.PNG') }}">
</div>

@endsection