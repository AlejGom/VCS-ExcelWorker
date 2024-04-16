@extends('templates.header')

@section('body')
<link rel="stylesheet" href="{{ asset('../resources/css/forms.css') }}">

<div class="uploadContainer">
    <form method="POST" action="{{ url('/uploadFile') }}" enctype="multipart/form-data">
        @csrf
        <label for="fileInput" class="fileLabel">Selecciona un archivo:<br>
            <input class="fileInput" type="file" name="file" id="fileInput">
            <i class="fas fa-file-upload"></i> Seleccionar archivo
        </label><br><br>
        <input class="fileNameInput" type="text" name="fileName" id="fileName" placeholder="Nombre del archivo"><br><br>
        <button class="submitButton" type="submit" onclick="showLoading()">Enviar archivo</button><br>
    </form>
</div>
<div id="loadingGif" style="display: none;">
    <img src="{{ asset('../resources/images/loading.gif') }}">
</div>
<div class="imageTemplate">
    <p>El archivo excel debe tener dos filas al inicio, la segunda con los nombres de las columnas</p>
    <img src="{{ asset('../resources/images/CapturaPlantilla.PNG') }}">
</div>

<script>
    document.getElementById('fileInput').addEventListener('change', function() {
        var fileName = this.files[0].name;
        document.getElementById('fileName').value = fileName;
    });
    
    function showLoading() {
        document.getElementById('loadingGif').style.display = 'block';
    }
</script>

@endsection