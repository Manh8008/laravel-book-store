<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\Books;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Validator;
use App\Http\Library\HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderAdminController extends Controller
{
    public function getAllOrder()
    {
        $order = Orders::orderBy('created_at', 'desc')->get();
        return HttpResponse::respondWithSuccess($order,"Success");
    }

    public function getAllPendingOrders()
    {
        $order = Orders::where('order_status', 'Chờ xác nhận')->orderBy('order_date', 'asc')->get();
        if ($order->isEmpty()) {
            return HttpResponse::respondWithSuccess(NULL,'Không có đơn hàng chờ xác nhận');
        }  
        return HttpResponse::respondWithSuccess($order,"Lấy tất cả đơn hàng chờ xác nhận thành công");
    }

    // public function getAllPendingOrders()
    // {
    //     $order = Orders::where('order_status', 'Chờ xác nhận')->orderBy('order_date', 'asc')->get();
    //     if ($order->isEmpty()) {
    //         return HttpResponse::respondWithSuccess(NULL,'Không có đơn hàng chờ xác nhận');
    //     }  
    //     return HttpResponse::respondWithSuccess($order,"Lấy tất cả đơn hàng chờ xác nhận thành công");
    // }

    // public function getAllPendingOrders()
    // {
    //     $order = Orders::where('order_status', 'Chờ xác nhận')->orderBy('order_date', 'asc')->get();
    //     if ($order->isEmpty()) {
    //         return HttpResponse::respondWithSuccess(NULL,'Không có đơn hàng chờ xác nhận');
    //     }  
    //     return HttpResponse::respondWithSuccess($order,"Lấy tất cả đơn hàng chờ xác nhận thành công");
    // }
    // public function updateOrderStatus(Request $request,$id)
    // {
    //     if (Auth::user()->role !== 'admin') return HttpResponse::respondError('Bạn không có quyền truy cập');
    //     $validator = Validator::make($request->all(), [
    //         'order_status' => 'required|string|max:20',
    //     ]);
    //     if ($validator->fails()) {
    //         return HttpResponse::respondError($validator->errors());
    //     }
    //     $order = Orders::find($id);
    //     if (!$order) {
    //         return HttpResponse::respondError("Đơn hàng không tồn tại");
    //     }
    //     $order->order_status = $request->order_status;
    //     $order->save();
    //     return HttpResponse::respondWithSuccess($order,"Cật nhật trạng thái thành công");
    // }

    public function updateOrderStatus(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') return HttpResponse::respondError('Bạn không có quyền truy cập');
        $validator = Validator::make($request->all(), [
            'order_status' => 'required|string|max:20',
        ]);
        if ($validator->fails()) return HttpResponse::respondError($validator->errors());
        $order = Orders::find($id);
        if (!$order) return HttpResponse::respondError("Đơn hàng không tồn tại");
        $currentStatus = $order->order_status;
        $newStatus = $request->order_status;
        DB::beginTransaction();
        try {
            if ($currentStatus === 'Chờ xác nhận' && $newStatus === 'Đã xác nhận') {
                $orderDetails = OrderDetail::where('order_id', $order->id)->get();
                foreach ($orderDetails as $detail) {
                    $book = Books::find($detail->book_id);
                    if ($book && $book->stock >= $detail->quantity) {
                        $book->decrement('stock', $detail->quantity);
                        $book->increment('sales_count', $detail->quantity);
                    } else {
                        return HttpResponse::respondError("Không đủ số lượng sách");
                    }
                }
            }
            $order->order_status = $newStatus;
            $order->save();
            DB::commit();
            return HttpResponse::respondWithSuccess($order, "Cập nhật trạng thái thành công");
        } catch (\Exception $e) {
            DB::rollBack();
            return HttpResponse::respondError($e->getMessage());
        }
    }
    
    public function searchOrders(Request $request)
    {
        $query = $request->input('query');
        $order = Orders::where('order_code', 'like', '%' . $query . '%')->get();
        if ($order->isEmpty()) {
            return HttpResponse::respondError("Không tìm thấy đơn hàng nào");
        }            
        return HttpResponse::respondWithSuccess($order,"Tìm kiếm thành công");
    }

}
