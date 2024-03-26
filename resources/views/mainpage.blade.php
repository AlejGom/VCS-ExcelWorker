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
            <input class="dateInput" type="date" name="start_date" id="start_date" @if (isset($startDate)) value="{{ $startDate }}" @endif><br>
            <label class="dateInputText" for="end_date">Fecha de Fin:</label>
            <input class="dateInput dateInput2" type="date" name="end_date" id="end_date" @if (isset($endDate)) value="{{ $endDate }}" @endif><br>
        </div>
        <div class="buttonContainer">
            <button  class="searchButton" type="submit">Búsqueda</button>
            @if ($filtered === true)
                <a class="cleanButton" href="{{ route('mainPage') }}">Limpiar filtro</a>
            @endif
        </div>
    </form>
</div>
<div class="tableContainer">
    <!-- files table -->
    <table class="generalTable">
        <thead>
            <tr>
                <th class="firstItem">Nombre</th>
                <th class="firstItem">Fecha de subida</th>
                <th class="firstItem">Tamaño (KB)</th>
                @if (auth()->user()->name === 'admin')
                    <th class="firstItem">Usuario</th>
                @endif
                <th class="firstItem"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($files as $file)
                <tr>
                    <td class="item"><a class="fileLink" href="{{ route('readFile', ['id' => $file->id]) }}">{{ $file->name }}</a></td>
                    <td class="item"><a class="fileLink" href="{{ route('readFile', ['id' => $file->id]) }}">{{ $file->created_at }}</a></td>
                    <td class="item"><a class="fileLink" href="{{ route('readFile', ['id' => $file->id]) }}">{{ round($file->size / 1000, 2) }} KB</a></td>
                    @if (auth()->user()->name === 'admin')
                        <td class="item"><a class="fileLink" href="{{ route('readFile', ['id' => $file->id]) }}">{{ $file->user->name }}</a></td>
                    @endif
                    <td class="item"><a class="deleteLink" href="{{ route('deleteFile', ['id' => $file->id]) }}">Eliminar</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection