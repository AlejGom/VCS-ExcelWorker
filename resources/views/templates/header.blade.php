<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Vcs</title>
    <link rel="stylesheet" href="{{ asset('../resources/css/home.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <!-- <div class="containerPruebaCentrado">
        <div class="pruebaCentrado"></div>
        <div class="pruebaCentrado"></div>
    </div> -->
    <div class="menuContainer">
    
        @if (!auth()->user())
            <div class="menu2">
                <div class="homeImageMenu">
                    <img class="vcsLogo" src="{{ asset('../resources/images/pajaro-logo1.png') }}">
                </div>
                <div class="links">
                    <a href="{{ route('login') }}">Iniciar sesión</a>
                    <a href="{{ route('signup') }}">Registrarse</a>    
                </div>
            </div>
        @else            
    <div class="menu2">
        <div class="imageMenu">
            <img class="vcsLogo" src="{{ asset('../resources/images/pajaro-logo1.png') }}">
        </div>
        <div class="menuLinks">
            <a href="{{ route('uploadFile') }}">Subir archivo</a>
            <a href="{{ route('mainPage') }}">Pagina principal</a>
            <a href="{{ route('mySharedFiles') }}">Archivos compartidos</a>
        </div>
        <div class="userMenu">
            <a class="user" href="{{ route('user') }}">{{ auth()->user()->name }}</a>
            <a class="logout" href="{{ route('logout') }}">Cerrar sesión</a>     
        </div>
    </div>
    @endif
    </div>
    @yield('body')
</body>
</html>