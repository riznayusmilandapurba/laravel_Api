<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\VerifyEmail;

class ApiAuthController extends Controller
{
    public function register(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
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

        // Buat pengguna baru
        $user = User::create([
            'fullname' => $request->fullname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'verified_code' => Str::random(40),
            'role' => $role,
            
        ]);

        // Kirim email verifikasi
        Mail::to($user->email)->send(new VerifyEmail($user));

        return response()->json(['message' => 'User registered successfully, please verify your email.'], 201);
    }  //
}
