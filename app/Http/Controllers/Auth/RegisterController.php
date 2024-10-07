<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // return response()->json([
        //     'success' => false,
        //     'errors' => $message,
        // ], $code);
        try {
            $validatorUser = Validator::make(
                $request->all(),
                [
                    "email" => "required|email|unique:users",
                    "name" => "required",
                    "password" => "required|max:30|min:8",
                    'password_confirm' => "required|same:password"
                ]
            );
    
            if($validatorUser->fails())
            {
                return response() -> json([
                    'status' => false,
                    'message' => 'Validate errors',
                    'errors' => $validatorUser->errors()
                ],401);
            }
    
            $user = User::Create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
    
            return response() -> json([
                'status' => true,
                'message' => 'Đăng kí thành công',
            ],200);
        } catch (\Throwable $th) {
            return response() -> json([
                'status' => false,
                'message' => $th->getMessage(),
            ],500);
        }
    
    }
}
