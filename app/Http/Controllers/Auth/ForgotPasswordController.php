<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Library\HttpResponse;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    // Phương thức gửi link đặt lại mật khẩu
    public function forgotPassword(Request $request)
    {
        // Validation kiểm tra định dạng email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        if ($validator->fails()) 
        {
            return HttpResponse::respondError($validator->errors()->first());
        }

        try {
            // Gửi liên kết đặt lại mật khẩu
            $status = Password::sendResetLink($request->only('email'));
            if ($status == Password::RESET_LINK_SENT) 
            {
                return HttpResponse::respondWithSuccess(__($status));
            } else 
            {
                return HttpResponse::respondError(__($status));
            }
        } catch (\Exception $e) {
            return HttpResponse::respondError($e->getMessage());
        }
    }
    // Phương thức đặt lại mật khẩuconfirm_password
    public function resetPassword(ResetPasswordRequest $request)
    {
        // Đặt lại mật khẩu thông qua token
        $status = Password::reset(
            $request->only('email', 'password', 'confirm_password', 'token'),
            function ($user, $password) {
                // Mã hóa mật khẩu trước khi lưu
                $user->password = Hash::make($password);
                $user->save();
            }
        );
        if ($status == Password::PASSWORD_RESET) {
            return HttpResponse::respondWithSuccess(null, __($status));
        }
        return HttpResponse::respondError(__($status));
    }
}
