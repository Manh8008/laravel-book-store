<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Orders;
use Illuminate\Support\Facades\Validator;
use App\Http\Library\HttpResponse;
use Illuminate\Support\Facades\Auth;

class OrderAdminController extends Controller
{
    public function getAllOrder()
    {
        $order = Orders::orderBy('created_at', 'desc')->get();
        return HttpResponse::respondWithSuccess($order);
    }

    public function getAllPendingOrders()
    {
        $order = Orders::where('order_status', 'Chờ xác nhận')->orderBy('order_date', 'asc')->get();
        if ($order->isEmpty()) {
            return HttpResponse::respondWithSuccess(NULL,'Không có đơn hàng chờ xác nhận');
        }  
        return HttpResponse::respondWithSuccess($order,"Lấy tất cả đơn hàng chờ xác nhận thành công");
    }

    public function updateOrderStatus(Request $request,$id)
    {
        if (Auth::user()->role !== 'admin') return HttpResponse::respondError('Bạn không có quyền truy cập');
        $validator = Validator::make($request->all(), [
            'order_status' => 'required|string|max:20',
        ]);
        if ($validator->fails()) {
            return HttpResponse::respondWithError($validator->errors());
        }
        $order = Orders::find($id);
        if (!$order) {
            return HttpResponse::respondError("Đơn hàng không tồn tại");
        }
        $order->order_status = $request->order_status;
        $order->save();
        return HttpResponse::respondWithSuccess($order,"Cật nhật trạng thái thành công");
    }

    public function searchOrders(Request $request)
    {
        $query = $request->input('query');
        $order = Orders::where('order_code', 'like', '%' . $query . '%')->get();
        if ($order->isEmpty()) {
            return HttpResponse::respondWithError("Không tìm thấy đơn hàng nào");
        }            
        return HttpResponse::respondWithSuccess($order,"Tìm kiếm thành công");
    }

}
