@extends('templates.header')

@section('body')
<link rel="stylesheet" href="{{ asset('../resources/css/forms.css') }}">

<div class="uploadContainer">
    <form method="POST" action="{{ url('/shareFile') }}" enctype="multipart/form-data">
        @csrf
        <p class="tittle">Nombre del archivo: {{ $file->name }}</p>
        <p class="tittle">Dueño del archivo: {{ $file->user->name }}</p>
        <input type="hidden" name="fileId" value="{{ $file->id }}">
        <select class="userSelect" name="user" id="user">
            <option value="" disabled selected>Selecciona un usuario</option>
            <!-- <option value="allUsers">Todos</option> -->
            @foreach ($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select><br><br>
        <button class="submitButton" type="submit">Compartir archivo</button><br>
    </form>
</div>

@endsection