@extends('templates.header')

@section('body')
<link rel="stylesheet" href="{{ asset('../resources/css/forms.css') }}">

<div class="uploadContainer">
    <form method="POST" action="{{ route('user') }}">
        @csrf

        <p class="tittle">Configuración del usuario {{ $user->name }}</p>
        <input type="text" name="name" @error ('name') style="border-color: red;" @enderror value="{{ $user->name }}"><br><br>
        @error ('name') <p style="color: red;">{{ $message }}</p> @enderror
        <input type="text" name="email" @error ('email') style="border-color: red;" @enderror value="{{ $user->email }}"><br><br>
        @error ('email') <p style="color: red;">{{ $message }}</p> @enderror
        <input type="text" name="password" value="{{ $user->password }}" hidden>

        <button class="submitButton" type="submit">Guardar cambios</button>
    </form>
    <a href="{{ route('deleteUser', ['id' => $user->id]) }}" class="deleteButton" id="deleteUser_{{ $user->id }}"><button>Eliminar cuenta</button></a>
</div>

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var deleteButtons = document.querySelectorAll('.deleteButton');
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                // Muestra un mensaje de confirmación antes de eliminar el archivo
                var confirmDelete = confirm('¿Estás seguro de que deseas eliminar esta cuenta? Los archivos subidos y compartidos se perderán.');
                if (!confirmDelete) {
                    event.preventDefault(); // Cancela la acción de eliminación si el usuario cancela
                }
            });
        });
    });
</script>