@extends('templates.header')

@section('body')
<link rel="stylesheet" href="{{ asset('../resources/css/forms.css') }}">
<div class="supportContainer">
<div class="loginContainer">
    <p class="tittle">Iniciar sesión</p>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <input placeholder="Usuario" type="text" class="form-control" id="name" name="name" @error ('name') style="border: 1px solid red; color: red;" @enderror value="{{ old('name') }}" autofocus>
            @error ('name') <p style="color: red;">{{ $message }}</p> @enderror
        </div>

        <div class="mb-3">
            <input placeholder="Contraseña" type="password" class="form-control" id="password" name="password" @error ('password') style="border: 1px solid red; color: red;" @enderror>
            @error ('password') <p style="color: red;">{{ $message }}</p> @enderror
        </div>
        @error('error') <p style="color: red;">{{ $message }}</p> @enderror

        <button type="submit" class="userSubmit">Iniciar sesión</button>
    </form>
    <a href="{{ route('signup') }}" class="formLinks">¿No tienes cuenta? Registrate</a>
</div>
</div>
@endsection