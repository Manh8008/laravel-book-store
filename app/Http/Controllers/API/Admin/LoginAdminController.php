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
    // public function registerAdmin(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             "name" => "required",
    //             'email' => 'required|email|unique:staff',
    //             'role' => 'required',
    //             'password' => 'required|min:8', 
    //             // 'password_confirm' => "required:"
    //         ]);

    //         if ($validator->fails()) return HttpResponse::respondError($validator->errors());

    //         // Mã hóa mật khẩu trước khi lưu
    //         $staff = new Staff();
    //         $staff->name = $request->name;
    //         $staff->email = $request->email;
    //         $staff->password = Hash::make($request->password); // Mã hóa mật khẩu
    //         $staff->role = $request->role;
    //         $staff->save();

    //         return HttpResponse::respondWithSuccess([], "Đăng ký thành công");
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $th->getMessage(),
    //         ], 500);
    //     }
    // }
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
