<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Books;
use App\Models\Orders;
use App\Http\Library\HttpResponse;

class ListController extends Controller
{
    // lấy số lượng user
    public function countUsers()
    {
        try {
            $userCount = User::count();
            return HttpResponse::respondWithSuccess(['total_users' => $userCount]);
        } catch (\Throwable $th) {
            return HttpResponse::respondUnAuthenticated();
        }
    }

    // lấy số lượng sách
    public function countBooks()
    {
        try {
            $bookCount = Books::count();
            return HttpResponse::respondWithSuccess(['total_books' => $bookCount]);
        } catch (\Throwable $th) {
            return HttpResponse::respondUnAuthenticated();
        }
    }


    // Lây số lượng đơn hàng
    public function countOrders()
    {
        try {
            $orderCount = Orders::count();
            return HttpResponse::respondWithSuccess(['total_orders' => $orderCount]);
        } catch (\Throwable $th) {
            return HttpResponse::respondUnAuthenticated();
        }
    }

    public function pendingOrdersCount()
    {
        try {
            $pendingOrderCount = Orders::where('order_status','chờ xác nhận')->count();
            return HttpResponse::respondWithSuccess(['pending_orders' => $pendingOrderCount]);
        } catch (\Throwable $th) {
            return HttpResponse::respondUnAuthenticated();
        }
    }
}
