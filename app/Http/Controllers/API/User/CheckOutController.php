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
use Carbon\Carbon;

class CheckOutController extends Controller
{
    public function checkoutCOD(Request $request)
    {
        if (!Auth::check()) {
            return HttpResponse::respondError('Bạn phải đăng nhập để đặt hàng.');
        }
        $user = Auth::user();
        $selectedAddressId = $request->input('selected_address_id');

        if ($selectedAddressId) {
            $userAddress = Addresses::where('id', $selectedAddressId)
                                    ->where('user_id', $user->id)
                                    ->first();
        } else {
            $userAddress = Addresses::where('user_id', $user->id)
                                    ->where('default', true) 
                                    ->first();
        }
        // $userAddress = Addresses::where('user_id', $user->id)->first();
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
                'name' => $userAddress->name,
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

    public function vnpayPayment(Request $request)
    {
        if (!Auth::check()) {
            return HttpResponse::respondError('Bạn phải đăng nhập để đặt hàng.');
        }
        $user = Auth::user();
        $selectedAddressId = $request->input('selected_address_id');
        if ($selectedAddressId) {
            $userAddress = Addresses::where('id', $selectedAddressId)
                                    ->where('user_id', $user->id)
                                    ->first();
        } else {
            $userAddress = Addresses::where('user_id', $user->id)
                                    ->where('default', true) // Giả sử cột `is_default` xác định địa chỉ mặc định
                                    ->first();
        }
        
        // $userAddress = Addresses::where('user_id', $user->id)->first();
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
                'payment_method' => 'VNPAY',
                'payment_status' => 'Chưa thanh toán',
            ]);
            $order = Orders::create([
                'user_id' => $user->id,
                'address_id' => $userAddress->id,
                'payment_id' => $payment->id,
                'order_date' => now(),
                'order_code' => 'ORDER-' . time(),
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
            // Tạo chi tiết đơn hàng
            foreach ($request->items as $item) {
                $book = Books::find($item['book_id']);
                if (!$book) {
                    return HttpResponse::respondError("Sản phẩm với ID " . $item['book_id'] . " không tồn tại.");
                }
                if ($item['quantity'] <= 0) {
                    return HttpResponse::respondError("Số lượng sản phẩm phải lớn hơn 0.");
                }
                OrderDetail::create([
                    'order_id' => $order->id,
                    'book_id' => $item['book_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
            DB::commit();
            // Cấu hình VNPAY
            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
            $vnp_Returnurl = "http://127.0.0.1:8000/api/vnpay-return"; 
            $vnp_TmnCode = 'HKJWG7M6'; // Mã website tại VNPAY 
            $vnp_HashSecret = '6X771W24XNIWWMDM4IXA8I7HIVDPXF4G'; // Chuỗi bí mật
            // Thông tin thanh toán
            $vnp_TxnRef = $order->order_code; // Mã đơn hàng
            $vnp_OrderInfo = "Thanh toán đơn hàng " . $order->order_code;
            $vnp_OrderType = "billpayment";
            $vnp_Amount = $request->total_amount * 100; // Đơn vị VNĐ
            $vnp_Locale = "vn";
            $vnp_IpAddr = $request->ip();
            $vnp_ExpireDate = Carbon::now()->addMinutes(30)->format('YmdHis');
            // Tạo dữ liệu thanh toán
            $inputData = [
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
                "vnp_ExpireDate" => $vnp_ExpireDate,
            ];
            // Tạo URL thanh toán VNPAY
            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }
            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);//  
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }
            // Trả về URL thanh toán
            return HttpResponse::respondWithSuccess(['payment_url' => $vnp_Url], 'Đơn hàng đã được tạo thành công! Vui lòng thanh toán qua VNPAY.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function vnpayReturn(Request $request)
    {
            if ($request->vnp_TransactionStatus == '00') {
                $orderCode = $request['vnp_TxnRef'];
                $order = Orders::where('order_code', $orderCode)->first();
                if ($order) {
                    $order->payment_status = 'Đã thanh toán';
                    // $order->order_status = 'Chờ xác nhận';
                    $order->save();
                    $payment = Payments::find($order->payment_id);
                    if ($payment) {
                        $payment->payment_status = 'Đã thanh toán';
                        $payment->save();
                    }
                    $successUrl = "http://localhost:3000/checkout/payment/payment-result?status=success&order_code=" . $order->order_code;
                    return redirect()->to($successUrl);
                    // return HttpResponse::respondWithSuccess(['order_code' => $order->order_code], 'Thanh toán thành công! Đơn hàng của bạn đã được xác nhận.');
                } else {
                    $errorUrl = "http://localhost:3000/checkout/payment/payment-result?status=error&message=Không tìm thấy đơn hàng.";
                    return redirect()->to($errorUrl);
                    // return HttpResponse::respondError('Không tìm thấy đơn hàng.');
                }
            } else {
                $errorUrl = "http://localhost:3000/checkout/payment/payment-result?status=error&message=Thanh toán thất bại. Vui lòng thử lại.";
                return redirect()->to($errorUrl);
                // return HttpResponse::respondError('Thanh toán thất bại. Vui lòng thử lại.');
            }
        // } else {
        //     return HttpResponse::respondError('Dữ liệu không hợp lệ.');
        // }
    }
    
    public function cancelOrder(Request $request, $id)
    {
        if (!Auth::check()) {
            return HttpResponse::respondError('Bạn phải đăng nhập để hủy đơn hàng');
        }
    
        // Kiểm tra sự tồn tại của đơn hàng
        $order = Orders::where('id', $id)
                       ->where('user_id', Auth::id())
                       ->first();
    
        if (!$order) {
            return HttpResponse::respondError('Không tìm thấy đơn hàng.');
        }
    
        // Kiểm tra trạng thái đơn hàng
        if ($order->order_status == 'Đã xác nhận' || $order->payment_status == 'Đã thanh toán') {
            return HttpResponse::respondError('Đơn hàng đã được xác nhận hoặc đã thanh toán, không thể hủy');
        }
    
        // Cập nhật trạng thái đơn hàng
        $order->order_status = 'Đã hủy';
    
        // Kiểm tra việc lưu thay đổi
        if ($order->save()) {
            return HttpResponse::respondWithSuccess('Đơn hàng đã được hủy thành công.');
        } else {
            return HttpResponse::respondError('Có lỗi xảy ra khi hủy đơn hàng.');
        }
    }
    
}
