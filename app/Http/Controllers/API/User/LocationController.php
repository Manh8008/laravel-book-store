<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Addresses;
use Illuminate\Support\Facades\Auth;
use App\Http\Library\HttpResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return HttpResponse::respondError('Bạn chưa đăng nhập');
            }
            $validator = Validator::make($request->all(), [
                'address_line' => 'required|string|max:255',
                'name' => 'required|string|max:100',
                'phone' => 'nullable|string|max:15',
                'town' => 'required|string|max:100',
                'district' => 'required|string|max:100',
                'province' => 'required|string|max:100',
                'townCode' => 'nullable|string|max:100',       
                'districtCode' => 'nullable|string|max:100',  
                'provinceCode' => 'nullable|string|max:100',
                'default' => 'nullable|boolean',
            ]);
            if ($validator->fails()) {
                return HttpResponse::respondError($validator->errors());
            }
            if($request->default == 1)
            {
                Addresses::where('user_id', $user->id)
                ->where('default', true)
                ->update(['default' => false]);
            }
            $address = Addresses::create([
                'address_line' => $request->address_line,
                'name' => $request->name,
                'phone' => $request->phone,
                'town' => $request->town,
                'district' => $request->district,
                'province' => $request->province,
                'townCode' => $request->townCode,             
                'districtCode' => $request->districtCode,     
                'provinceCode' => $request->provinceCode,
                'user_id' => $user->id,
                'default' => $request->default,
            ]);
            return HttpResponse::respondWithSuccess($address, 'Địa chỉ đã được tạo thành công');
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }

    public function update(Request $request, $id)
    {
        try {
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
                'name' => 'required|string|max:100',
                'phone' => 'nullable|string|max:15',
                'town' => 'required|string|max:100',
                'district' => 'required|string|max:100',
                'province' => 'required|string|max:100',
                'townCode' => 'nullable|string|max:100',       
                'districtCode' => 'nullable|string|max:100',  
                'provinceCode' => 'nullable|string|max:100',
                'default' => 'nullable|boolean',
            ]);
            if ($validator->fails()) {
                return HttpResponse::respondError($validator->errors());
            }
            if($request->default == 1)
            {
                Addresses::where('user_id', $user->id)
                ->where('default', true)
                ->update(['default' => false]);
            }
            $address->update([
                'address_line' => $request->address_line,
                'name' => $request->name,
                'phone' => $request->phone,
                'town' => $request->town,
                'district' => $request->district,
                'province' => $request->province,
                'townCode' => $request->townCode,             
                'districtCode' => $request->districtCode,     
                'provinceCode' => $request->provinceCode,
                'default' => $request->default,
            ]);
            return HttpResponse::respondWithSuccess($address, 'Địa chỉ đã được cập nhật thành công');
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }   

    public function destroy($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return HttpResponse::respondError('Bạn chưa đăng nhập');
            }
            $address = Addresses::find($id);
            $address->delete();
            return HttpResponse::respondWithSuccess(null,'Địa chỉ đã được xóa thành công');
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }

    public function defaultUpdate(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return HttpResponse::respondError('Bạn chưa đăng nhập');
        }
        $address = Addresses::find($id);
        if (!$address || $address->user_id !== $user->id) {
            return HttpResponse::respondError('Địa chỉ không tồn tại hoặc không thuộc về bạn');
        }
        if ($address->default) {
            $address->update(['default' => false]);
            return HttpResponse::respondWithSuccess($address, 'Địa chỉ đã cập nhật thành công và không còn là mặc định');
        } else {
            $user->address()->update(['default' => false]);
            $address->update(['default' => true]);
            return HttpResponse::respondWithSuccess($address, 'Địa chỉ đã cập nhật thành công và trở thành mặc định');
        }
    }

    public function getAddressesById($id)
    {
        try {
            $address = Addresses::find($id); 
            if (!$address) {
                return HttpResponse::respondError('Không tìm thấy địa chỉ');
            }
            return HttpResponse::respondWithSuccess($address);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to retrieve address: ' . $e->getMessage()], 500);
        }
    }
}
