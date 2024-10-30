<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Addresses;
use Illuminate\Support\Facades\Auth;
use App\Http\Library\HttpResponse;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return HttpResponse::respondError('Bạn chưa đăng nhập');
        }
        // Xác thực dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'address_line' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'phone' => 'nullable|string|max:15',
            'town' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'province' => 'required|string|max:100',
        ]);
        if ($validator->fails()) {
            return HttpResponse::respondError($validator->errors());
        }
        $address = Addresses::create([
            'address_line' => $request->address_line,
            'city' => $request->city,
            'phone' => $request->phone,
            'town' => $request->town,
            'district' => $request->district,
            'province' => $request->province,
            'user_id' => $user->id,
        ]);
        return HttpResponse::respondWithSuccess($address, 'Địa chỉ đã được tạo thành công');
    }


    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return HttpResponse::respondError('Bạn chưa đăng nhập');
        }
        $address = Addresses::find($id);
        if (!$address) {
            return HttpResponse::respondError('Địa chỉ không tồn tại');
        }
        if ($address->user_id !== $user->id) {
            return HttpResponse::respondError('Bạn không có quyền cập nhật địa chỉ này');
        }
        $validator = Validator::make($request->all(), [
            'address_line' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'phone' => 'nullable|string|max:15',
            'town' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'province' => 'required|string|max:100',
        ]);
        if ($validator->fails()) {
            return HttpResponse::respondError($validator->errors());
        }
        $address->update([
            'address_line' => $request->address_line,
            'city' => $request->city,
            'phone' => $request->phone,
            'town' => $request->town,
            'district' => $request->district,
            'province' => $request->province,
        ]);
        return HttpResponse::respondWithSuccess($address, 'Địa chỉ đã được cập nhật thành công');
    }   

    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user) {
            return HttpResponse::respondError('Bạn chưa đăng nhập');
        }
        $address = Addresses::find($id);
        $address->delete();
        return HttpResponse::respondWithSuccess(null,'Địa chỉ đã được xóa thành công');
    }

    public function defaultUpdate(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return HttpResponse::respondError('Bạn chưa đăng nhập');
        }
        $address = Addresses::find($id);
        if (!$address || $address->id_user !== $user->id) {
            return HttpResponse::respondError('Địa chỉ không tồn tại hoặc không thuộc về bạn');
        }
        $currentDefault = $address->default;
        if ($currentDefault) {
            $address->update(['default' => false]);
            return HttpResponse::respondWithSuccess($address, 'Địa chỉ đã cật nhật thành công');
        } else {
            $user->addresses()->update(['default' => false]);
            $address->update(['default' => true]);
            return HttpResponse::respondWithSuccess($address, 'Địa chỉ đã cật nhật thành công');
        }
    }


}
