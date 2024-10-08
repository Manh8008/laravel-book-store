<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Library\HttpResponse;
use Illuminate\Support\Facades\Auth;


class LogoutController extends Controller
{
    public function logout(Request $request)
    {  
        $request->user()->tokens()->delete();
        return HttpResponse::respondWithSuccess(null,"Đăng xuất thành công");
    }
}
