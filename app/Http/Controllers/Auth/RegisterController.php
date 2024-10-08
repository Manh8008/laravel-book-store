<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Library\HttpResponse;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
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
                return HttpResponse::respondError($validatorUser->errors());
            }
            $user = User::Create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            return HttpResponse::respondWithSuccess(null,"Đăng kí thành công");
        } catch (\Throwable $th) {
            return response() -> json([
                'status' => false,
                'message' => $th->getMessage(),
            ],500);
        }
    }
}
