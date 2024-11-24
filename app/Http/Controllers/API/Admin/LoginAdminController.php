<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Library\HttpResponse;
use Illuminate\Support\Facades\Hash;



class LoginAdminController extends Controller
{
    public function loginAdmin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
            ]);
            if ($validator->fails()) return HttpResponse::respondError($validator->errors());
            // dd(Auth::attempt(['email' => $request->email, 'password' => $request->password]));
            $staff = Staff::where('email', $request->email)->first();
            if ($staff && Hash::check($request->password, $staff->password)) {
                if ($staff->role !== 'admin') {
                    return HttpResponse::respondError("Bạn không có quyền truy cập.");
                }
                // Tạo token
                $token = $staff->createToken("admin_access_token")->plainTextToken;
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
