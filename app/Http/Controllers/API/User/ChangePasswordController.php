<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Library\HttpResponse;
use Illuminate\Validation\ValidationException;

class ChangePasswordController extends Controller
{
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => "required",
            'password' => "required|min:8|max:30",
            'confirm_password' => "required|same:password"
        ]);
        $user = Auth::user();
        if (!Hash::check($request->old_password, $user->password)) {
            return HttpResponse::respondError('Mật khẩu cũ không chính xác');
        }
        $user->password = Hash::make($request->password);
        $user->save();
        return HttpResponse::respondWithSuccess('Thay đổi mật khẩu thành công');
    }
}
