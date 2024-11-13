<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Library\HttpResponse;


class ProfileController extends Controller
{
    // public function profile()
    // {
    //     try {
    //         $userData = Auth::user();
    //         if (!$userData) {
    //             return HttpResponse::respondError('Người dùng chưa đăng nhập');
    //         }
    //         $address = $userData->address;
    //         return HttpResponse::respondWithSuccess($userData, 'Thông tin người dùng được lấy thành công');
    //     } catch (\Throwable $th) {
    //         return HttpResponse::respondUnAuthenticated();
    //     }
    // }
    public function profile()
    {
        try {
            $userData = Auth::user();
            if (!$userData) {
                return HttpResponse::respondError('Người dùng chưa đăng nhập');
            }
            $address = $userData->address;
            $orders = $userData->orders()->orderBy('created_at', 'desc')->get();
            return HttpResponse::respondWithSuccess([
                'user' => $userData,
                'orders' => $orders
            ], 'Thông tin người dùng, địa chỉ và đơn hàng được lấy thành công');
        } catch (\Throwable $th) {
            return HttpResponse::respondUnAuthenticated();
        }
    }
}
