<?php
namespace App\Http\Library;

use App\Http\Controllers\Controller;
use App\Http\Library\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;
use Nette\Utils\DateTime;

class SendMailUser extends Controller
{
    public static function sendOrderConfirmation($order, $payment)
    {
        $user = $order->user; // Giả sử đơn hàng có quan hệ với người dùng (user)
        
        // Lấy thông tin chi tiết đơn hàng (ví dụ: danh sách sản phẩm, tổng giá trị...)
        $orderDetails = $order->items; // Giả sử đơn hàng có mối quan hệ items với các sản phẩm
        
        // Thông tin thanh toán
        $paymentStatus = $payment->status; // Trạng thái thanh toán
        $paymentMethod = $payment->method; // Phương thức thanh toán
        
        // Gửi email xác nhận đơn hàng cho người dùng
        Mail::to($user->email)->send(new OrderConfirmation($order, $payment));
        
        // Kiểm tra xem email có gửi thành công không
        if (Mail::failures()) {
            // Xử lý nếu gửi email không thành công
            return HttpResponse::errorResponse('Không thể gửi email xác nhận đơn hàng');
        }

        // Trả về phản hồi thành công nếu email gửi thành công
        return HttpResponse::successResponse('Email xác nhận đơn hàng đã được gửi');
    }
}
