<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class AuthManager extends Controller
{
    function login(){
        return view('login');
    }

    function registration(){
        return view('registration');
    }

    function loginPost(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'captcha' => 'required|integer'
        ]);
    
        $email = $request->email;
        $password = $request->password;
        $captcha_answer = $request->captcha_answer;
        $user_answer = $request->captcha;
    
        if ($captcha_answer != $user_answer) {
            return redirect(route(name: 'login'))->withErrors(['captcha' => 'Please enter the correct answer.']);
        }
    
        $attempts = Cache::get($email.'_attempts', 0);
        if ($attempts >= 3) {
            $cooldown = Cache::get($email.'_cooldown', 0);
            $remaining_time = $cooldown - time();
            if ($remaining_time > 0) {
                return redirect(route(name: 'login'))->withErrors(['cooldown' => 'Too many login attempts. Please try again in '.$remaining_time.' seconds.']);
            } else {
                Cache::forget($email.'_attempts');
                Cache::forget($email.'_cooldown');
            }
        }
    
        $credentials = $request->only(keys: ['email', 'password']);
        if (!User::where('email', $email)->exists()) {
            Cache::increment($email.'_attempts');
            Cache::put($email.'_cooldown', time() + 30, 30);
            return redirect(route(name: 'login'))->withErrors(['email' => 'Invalid email.']);
        }
        if (!Auth::attempt($credentials)) {
            Cache::increment($email.'_attempts');
            Cache::put($email.'_cooldown', time() + 30, 30);
            return redirect(route(name: 'login'))->withErrors(['password' => 'Invalid password.']);
        }
    
        Cache::forget($email.'_attempts');
        Cache::forget($email.'_cooldown');
    
        return redirect()->intended(route(name: 'home'));
    }

    function registrationPost(Request $request){
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'min:10',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/'
            ]
        ], [    'password.required' => 'The password field is required.',    'password.min' => 'The password field must be at least 10 characters.',    'password.regex' => 'The password field format is invalid. It must contain at least one lowercase letter, one uppercase letter, one number, and one symbol.']
    );
    
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['password'] = Hash::make($request->password);
    
        $user = User::create($data);
    
        if(!$user){
            return redirect(route(name: 'registration'));
        }
        return redirect(route(name: 'login'));
    }

    function logout(){
        Session::flush();
        Auth::logout();
        return redirect(route(name: 'login'));
    }

}
