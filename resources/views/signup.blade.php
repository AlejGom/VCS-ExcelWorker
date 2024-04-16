@extends('templates.header')

@section('body')
<link rel="stylesheet" href="{{ asset('../resources/css/forms.css') }}">
<div class="supportContainer">
<div class="signupContainer">
    <p class="tittle">Registrarse</p>
    <form method="POST" action="{{ route('signup') }}">
        @csrf
        <div class="mb-3">
            <input placeholder="Nombre" type="text" id="name" name="name"  @error('name') style="border: 1px solid red; color: red;" @enderror value="{{ old('name') }}" autofocus>
            @error('name') <p style="color: red;">{{ $message }}</p> @enderror
        </div>

        <div class="mb-3">
            <input placeholder="Email" type="email" id="email" name="email" @error('email') style="border: 1px solid red; color: red;" @enderror value="{{ old('email') }}">
            @error('email') <p style="color: red;">{{ $message }}</p> @enderror
        </div>

        <div class="mb-3">
            <input placeholder="Contraseña" type="password" id="password" name="password" @error('password') style="border: 1px solid red; color: red;" @enderror>
            @error('password') <p style="color: red;">{{ $message }}</p> @enderror
        </div>

        <div class="mb-3">
            <input placeholder="Confirmar contraseña" type="password" id="password_confirmation" name="password_confirmation" @error('password') style="border: 1px solid red; color: red;" @enderror>
            @error('password_confirmation') <p style="color: red;">{{ $message }}</p> @enderror
        </div>

         <button type="submit" class="userSubmit">Registrarse</button>
    </form>
    <a href="{{ route('login') }}" class="formLinks">¿Ya tienes cuenta? Iniciar sesión</a>
</div>
</div>
@endsection