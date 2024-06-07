<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;

Route::post('/register-native', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
        'phone' => 'required|string|max:15',
        'address' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    // Menentukan role berdasarkan email
    $role = (strpos($request->email, 'admin') !== false) ? 1 : 2;

    $user = User::create([
        'fullname' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'phone' => $request->phone,
        'address' => $request->address,
        'role' => $role,
        'verified_code' => Str::random(40),
    ]);

    // Kirim email verifikasi
    Mail::to($user->email)->send(new VerifyEmail($user));

    return response()->json(['message' => 'User registered successfully, please verify your email.'], 201);
});
