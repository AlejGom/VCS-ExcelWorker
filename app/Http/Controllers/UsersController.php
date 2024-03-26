<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    // *************************************************************
    // Show views functions

    public function showLoginForm() {
        return view('login');
    }
    public function showSignupForm() {
        return view('signup');
    }

    // *************************************************************
    // User manager functions

    // Funcion para registrar un nuevo usuario
    public function signup(Request $request) {
        $request->validate([
            'name'                  => 'required|min:4,max:20|unique:users',
            'email'                 => 'required|email|unique:users',
            'password'              => 'required',
            'password_confirmation' => 'required|same:password',
        ]);
        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect('/login');
    }

    // Funcion para iniciar sesion
    public function login(Request $request) {
        $this->validate($request, [
            'name'     => 'required',
            'password' => 'required',
        ]);
        $name     = $request->name;
        $password = $request->password;
        
        $bdUser = User::where('name', $name)->first();

        if($bdUser && Hash::check($password, $bdUser->password)) {

            $credentials = $request->only('name', 'password');

            if(auth()->attempt($credentials)) {
                return redirect('/mainPage');
            }

        } else {
            return back()->withErrors([
                'error'=> 'Credenciales incorrectas',
            ]);
        }
    }

    // Funcion para cerrar sesion
    public function logout(Request $request) {
        Auth::logout();
        return redirect('/login');
    }
}
