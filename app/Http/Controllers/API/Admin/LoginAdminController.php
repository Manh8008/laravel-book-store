<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Library\HttpResponse;


class LoginAdminController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
            ]);
            if ($validator->fails()) return HttpResponse::respondError($validator->errors());
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) 
            {
                $staff = Staff::where('email', $request->email)->first();
                if (!$staff) return HttpResponse::respondError("Bạn không có quyền truy cập");
                if ($staff->role !== 'admin') return HttpResponse::respondError("Bạn không có quyền truy cập.");
                $staff->save();
                $token = $staff->createToken("admin_access_token", expiresAt: now()->addDay())->plainTextToken;
                return HttpResponse::respondWithSuccess([
                    'token_type' => "Bearer",
                    'access_token' => $token
                ], "Đăng nhập admin thành công");
            }
            return HttpResponse::respondError("Email hoặc mật khẩu không hợp lệ");
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
