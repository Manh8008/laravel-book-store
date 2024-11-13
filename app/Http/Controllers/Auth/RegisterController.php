<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Library\HttpResponse;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

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
            if($validatorUser->fails()) return HttpResponse::respondError($validatorUser->errors());
            $otpCode = rand(100000, 999999);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'otp_code' => $otpCode,
                'otp_expires_at' => Carbon::now()->addMinutes(10), // OTP hết hạn sau 10 phút
            ]);
            Mail::to($user->email)->send(new \App\Mail\SendOtpMail($otpCode));
            return HttpResponse::respondWithSuccess(null,"Đăng kí thành công, Mời bạn xác thưc OTP");
        } catch (\Throwable $th) {
            return response() -> json([
                'status' => false,
                'message' => $th->getMessage(),
            ],500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|integer',
        ]);

        $user = User::where('email', $request->email)
                    ->where('otp_code', $request->otp_code)
                    ->first();
        if (!$user) return HttpResponse::respondError("Mã OTP không hợp lệ.");
        if (Carbon::now()->greaterThan($user->otp_expires_at)) return HttpResponse::respondError("Mã OTP đã hết hạn.");
        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null,
            'is_verified' => true,
        ]);
        return HttpResponse::respondWithSuccess(null, "Xác thực OTP thành công. Bạn có thể đăng nhập.");
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user) return HttpResponse::respondError("Email không tồn tại.");
        if ($user->is_verified) return HttpResponse::respondError("Tài khoản đã được xác thực.");
        $otpCode = rand(100000, 999999);
        $user->update([
            'otp_code' => $otpCode,
            'otp_expires_at' => Carbon::now()->addMinutes(10), // OTP mới hết hạn sau 10 phút
        ]);
        Mail::to($user->email)->send(new \App\Mail\SendOtpMail($otpCode));
        return HttpResponse::respondWithSuccess(null, "Mã OTP mới đã được gửi. Vui lòng kiểm tra email.");
    }


}
