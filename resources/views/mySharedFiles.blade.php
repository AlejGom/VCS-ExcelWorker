@extends('templates.header')

@section('body')
<link rel="stylesheet" href="{{ asset('../resources/css/mainpage.css') }}">

<div id="loadingGif" style="display: none;">
    <img src="{{ asset('../resources/images/loading.gif') }}">
</div>

<div class="tablesContainer">
<div class="tableContainer">
    <!-- files table -->
    <h1 class="tableTitle">Mis archivos compartidos</h1>
    <table class="generalTable">
        <thead>
            <tr>
                <th class="firstItem"></th>
                <th class="firstItem">Nombre</th>
                <th class="firstItem">Fecha de subida</th>
                <th class="firstItem">Tamaño (KB)</th>
                <th class="firstItem">Destino</th>
                <th class="firstItem"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($myFiles as $myFile)
                <tr>
                    <td class="item"><a class="shareLink" href="{{ route('downloadFile', ['id' => $myFile['id']]) }}"><img class="trashIcon" src="{{ asset('../resources/images/descargar.png') }}"></a></td>
                    <td class="item"><a onclick="showLoading()" class="fileLink" href="{{ route('readFile', ['id' => $myFile['id']]) }}">{{ $myFile['name'] }}</a></td>
                    <td class="item"><a onclick="showLoading()" class="fileLink" href="{{ route('readFile', ['id' => $myFile['id']]) }}">{{ $myFile['created'] }}</a></td>
                    <td class="item"><a onclick="showLoading()" class="fileLink" href="{{ route('readFile', ['id' => $myFile['id']]) }}">{{ round($myFile['size'] / 1000, 2) }} KB</a></td>
                    <td class="item"><a onclick="showLoading()" class="fileLink" href="{{ route('readFile', ['id' => $myFile['id']]) }}">{{ $myFile['destinatary'] }}</a></td>
                    <td class="item"><a class="deleteLink" id="deleteSharedFile_{{ $myFile['id'] }}" href="{{ route('deleteSharedFile', ['id' => $myFile['idShared']]) }}"><img class="trashIcon" src="{{ asset('../resources/images/papelera.png') }}"></a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="tableContainer">
    <!-- files table -->
    <h1 class="tableTitle">Compartido a {{ auth()->user()->name }}</h1>
    <table class="generalTable">
        <thead>
            <tr>
                <th class="firstItem"></th>
                <th class="firstItem">Nombre</th>
                <th class="firstItem">Fecha de subida</th>
                <th class="firstItem">Tamaño (KB)</th>
                <th class="firstItem">Dueño</th>
                <th class="firstItem"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($files as $file)
                <tr>
                    <td class="item"><a class="deleteLink" href="{{ route('downloadFile', ['id' => $file['id']]) }}"><img class="trashIcon" src="{{ asset('../resources/images/descargar.png') }}"></a></td>
                    <td class="item"><a onclick="showLoading()" class="fileLink" href="{{ route('readFile', ['id' => $file['id']]) }}">{{ $file['name'] }}</a></td>
                    <td class="item"><a onclick="showLoading()" class="fileLink" href="{{ route('readFile', ['id' => $file['id']]) }}">{{ $file['created'] }}</a></td>
                    <td class="item"><a onclick="showLoading()" class="fileLink" href="{{ route('readFile', ['id' => $file['id']]) }}">{{ round($file['size'] / 1000, 2) }} KB</a></td>
                    <td class="item"><a onclick="showLoading()" class="fileLink" href="{{ route('readFile', ['id' => $file['id']]) }}">{{ $file['user'] }}</a></td>
                    <td class="item"><a class="deleteLink" id="deleteSharedFile_{{ $file['id'] }}" href="{{ route('deleteSharedFile', ['id' => $file['idShared']]) }}"><img class="trashIcon" src="{{ asset('../resources/images/papelera.png') }}"></a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
</div>

<!-- ----------------------------------------------------- -->
<!-- ----------------------------------------------------- -->
<!-- -----------------------SCRIPTS----------------------- -->
<!-- ----------------------------------------------------- -->
<!-- ----------------------------------------------------- -->

<script>
    function showLoading() {
        document.getElementById('loadingGif').style.display = 'block';
    }

    document.addEventListener('DOMContentLoaded', function() {
        var deleteButtons = document.querySelectorAll('.deleteLink');
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                // Muestra un mensaje de confirmación antes de eliminar el archivo
                var confirmDelete = confirm('Si eliminas este archivo solo lo eliminarás para ti, el dueño del archivo lo podrá seguir viendo.');
                if (!confirmDelete) {
                    event.preventDefault(); // Cancela la acción de eliminación si el usuario cancela
                }
            });
        });
    });
</script>

@endsection