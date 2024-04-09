@extends('templates.header')

@section('body')
<link rel="stylesheet" href="{{ asset('../resources/css/mainpage.css') }}">
@error ('extensionError') <p style="color: red;">{{ $message }}</p> @enderror

@if (auth()->user()->name === 'admin')
    <p class="adminText">Usuario administrador, visibles los archivos de todos los usuarios</p>
@endif
<div class="searchContainer">
    <form action="{{ route('filterFiles') }}" method="POST">
        @csrf
        <!-- <label for="search_text">Texto de Búsqueda:</label> -->
        <div class="searchInputContainer">
            <input class="searchInput" type="text" name="search_text" id="search_text" placeholder="Texto de Búsqueda" @if (isset($inputText)) value="{{ $inputText }}" @endif><br>
            <label class="dateInputText" for="start_date">Fecha de Inicio:</label>
            <input class="dateInput" type="date" name="start_date" id="start_date" value="2000-01-01" @if (isset($startDate)) value="{{ $startDate }}" @endif><br>
            <label class="dateInputText" for="end_date">Fecha de Fin:</label>
            <input class="dateInput dateInput2" type="date" name="end_date" id="end_date" @if (isset($endDate)) value="{{ $endDate }}" @endif><br>
            @if (auth()->user()->name === 'admin')
                <!-- <input class="searchInput" type="text" name="search_user" id="search_user" placeholder="Usuario" @if (isset($user)) value="{{ $searchUser }}" @endif> -->
                <label class="dateInputText" for="search_user">Usuario:</label>
                <select class="searchUserInput" name="search_user" id="search_user">
                    <option value="" selected>Todos</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            @endif

        </div>
        <div class="buttonContainer">
            <button  class="searchButton" type="submit">Búsqueda</button>
            @if ($filtered === true)
                <a class="cleanButton" href="{{ route('mainPage') }}">Limpiar filtro</a>
            @endif
        </div>
    </form>
</div>
<div id="loadingGif" style="display: none;">
    <img src="{{ asset('../resources/images/loading.gif') }}">
</div>
<div class="tableContainer">
    <!-- files table -->
    <table class="generalTable">
        <thead>
            <tr>
                <th class="firstItem"></th>
                <th class="firstItem">Nombre</th>
                <th class="firstItem">Fecha de subida</th>
                <th class="firstItem">Tamaño (KB)</th>
                <th class="firstItem">Usuario</th>
                <th class="firstItem"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($files as $file)
                <tr>
                    <td class="item"><a class="shareLink" href="{{ route('showShareFile', ['id' => $file->id]) }}"><img class="trashIcon" src="{{ asset('../resources/images/share.png') }}"></a></td>
                    <td class="item"><a onclick="showLoading()" class="fileLink" href="{{ route('readFile', ['id' => $file->id]) }}">{{ $file->name }}</a></td>
                    <td class="item"><a onclick="showLoading()" class="fileLink" href="{{ route('readFile', ['id' => $file->id]) }}">{{ $file->created_at }}</a></td>
                    <td class="item"><a onclick="showLoading()" class="fileLink" href="{{ route('readFile', ['id' => $file->id]) }}">{{ round($file->size / 1000, 2) }} KB</a></td>
                    <td class="item"><a onclick="showLoading()" class="fileLink" href="{{ route('readFile', ['id' => $file->id]) }}">{{ $file->user->name }}</a></td>
                    <td class="item"><a class="deleteLink" id="deleteFile_{{ $file->id }}" href="{{ route('deleteFile', ['id' => $file->id]) }}"><img class="trashIcon" src="{{ asset('../resources/images/papelera.png') }}"></a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
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
                var confirmDelete = confirm('¿Estás seguro de que deseas eliminar este archivo? Se eliminará también a las personas que has compartido.');
                if (!confirmDelete) {
                    event.preventDefault(); // Cancela la acción de eliminación si el usuario cancela
                }
            });
        });
    });
</script>
@endsection