<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Library\HttpResponse;

class UserAdminController extends Controller
{
    public function getAllUsers()
    {
        try {
            $users = User::with('address')->orderBy('created_at', 'desc')->get();
            if ($users->isEmpty()) {
                return HttpResponse::respondWithSuccess([], "Không có người dùng nào.");
            }
            $usersData = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'address' => $user->address->map(function ($address) {
                        return [
                            'id' => $address->id,
                            'address_line' => $address->address_line,
                            'phone' => $address->phone,
                            'name' => $address->name,
                            'town' => $address->town,
                            'district' => $address->district,
                            'province' => $address->province,
                            'default' => $address->default, 
                            'townCode' => $address->townCode,
                            'districtCode' => $address->districtCode,
                            'provinceCode' => $address->provinceCode
                        ];
                    })
                ];
            });
            return HttpResponse::respondWithSuccess($usersData, "Lấy danh sách người dùng và địa chỉ thành công.");
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound("Lỗi K thìm thấy");
        }
    }

}
