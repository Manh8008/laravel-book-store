<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Library\HttpResponse;
use Illuminate\Support\Facades\Validator;


class ProfileController extends Controller
{
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

    public function upload(Request $request)
    {
        // dd($request);
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:15',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            $user = Auth::user();
            // Update profile
            $user->name = $request->input('name');
            $user->phone = $request->input('phone');
            $user->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }  
}
