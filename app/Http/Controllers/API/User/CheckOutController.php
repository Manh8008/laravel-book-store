<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\OrderDetail;
use App\Models\Addresses;
use App\Models\Payments;
use App\Models\Books;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Library\HttpResponse;


class CheckOutController extends Controller
{
    public function checkoutCOD(Request $request)
    {
        if (!Auth::check()) {
            return HttpResponse::respondError('Bạn phải đăng nhập để đặt hàng.');
        }
        $user = Auth::user();
        $userAddress = Addresses::where('user_id', $user->id)->first();
        if (!$userAddress) {
            return HttpResponse::respondError('Người dùng chưa có địa chỉ. Vui lòng thêm địa chỉ trước khi đặt hàng.');
        }
        $request->validate([
            'items' => 'required|array', // Dữ liệu sản phẩm
            'total_amount' => 'required|numeric', // Tổng tiền
        ]);
        DB::beginTransaction();
        
        try {
            $payment = Payments::create([
                'payment_method' => 'COD',
                'payment_status' => 'Chưa thanh toán',
            ]);
            $order = Orders::create([
                'user_id' => $user->id,
                'address_id' => $userAddress->id,
                'payment_id' => $payment->id,
                'order_date' => now(),
                'order_code' => 'ORDER' . time(),
                'total_amount' => $request->total_amount,
                'order_status' => 'Chờ xác nhận',
                'payment_status' => $payment->payment_status,
                'address_line' => $userAddress->address_line,
                'town' => $userAddress->town,
                'district' => $userAddress->district,
                'province' => $userAddress->province,
                'phone' => $userAddress->phone,
                'name' => $user->name,
            ]);
            foreach ($request->items as $item) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'book_id' => $item['book_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
            DB::commit();
            return HttpResponse::respondWithSuccess($order,'Đơn hàng đã được tạo thành công!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
