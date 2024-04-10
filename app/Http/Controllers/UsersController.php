<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SharedFile;
use App\Models\File;

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
        
        $bdUser   = User::where('name', $name)->first();

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

    // Funcion para mostrar la configuracion del usuario
    public function showUserConf() {
        $user = auth()->user();

        return view('user' , [
            'user' => $user
        ]);
    }

    // Funcion para actualizar la configuracion del usuario
    public function saveUserConfig(Request $request) {
        $request->validate([
            'name'                  => 'required|min:4|max:20',
            'email'                 => 'required|email',
        ]);

        $user = auth()->user();
        
        $user->name     = $request->name;
        $user->email    = $request->email;
        $user->password = $request->password;

        $user->save();

        return redirect('/mainPage');
    }

    // Funcion para eliminar la cuenta del usuario
    public function deleteUser($id) {
        
        $user  = User::find($id);
        $files = File::where('user_id', $id)->get();

        $filesShared  = SharedFile::where('id_user', $id)->get();

        $sharedToUser = SharedFile::where('shared', $user->id)->get();

        foreach ($sharedToUser as $shared) {
            $shared->delete();
        }

        foreach ($filesShared as $fileShared) {
            $fileShared->delete();
        }

        foreach ($files as $file) {
            $file->delete();
        }

        $user->delete();
        Auth::logout();
        return redirect('/login');
    }
}
