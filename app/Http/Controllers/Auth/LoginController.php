<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Library\HttpResponse;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validatorUser = Validator::make(
                $request->all(),
                [
                    "email" => "required",
                    "password" => "required",
                ]
            );
            if($validatorUser->fails())
            {
                return HttpResponse::respondError($validatorUser->errors());
            }
            if (Auth::attempt(['email' => $request->email, "password" => $request->password])) 
            {
                $user = User::where('email', $request->email)->first();
                $user->last_login_date = now();
                $user->save();
                $token = $user->createToken("access_token", expiresAt: now()->addDay())->plainTextToken;
                return HttpResponse::respondWithSuccess([
                    'token_type' => "Bearer",
                    'access_token' => $token
                ], "Đăng nhập thành công");
            };
            return HttpResponse::respondError("Tài khoản hoặc mật khẩu không hợp lệ");
        } catch (\Throwable $th) {
            return response() -> json([
                'status' => false,
                'message' => $th->getMessage(),
            ],500);
        }
    }

    
}
