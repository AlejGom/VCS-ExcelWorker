@extends('templates.header')

@section('body')
<link rel="stylesheet" href="{{ asset('../resources/css/forms.css') }}">

@if (auth()->user()->name === 'admin')
    <div class="uploadContainer">
        <form method="POST" action="{{ route('changeUserPassword') }}">
            @csrf
            <select class="userSelect" name="user" id="user">
                <option value="" selected disabled>Selecciona un usuario</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select><br><br>
            <input type="text" name="password" value="">

            <button class="submitButton" type="submit">Guardar cambios</button>
        </form>
    </div>
@else
    <p>Permiso denegado</p>
    <a href="{{ route('mainPage') }}">Volver</a>
@endif

@endsection
