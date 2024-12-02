<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banners;
use App\Http\Library\HttpResponse;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banners::all();
        return HttpResponse::respondWithSuccess($banners,"Lấy thành công");
    }
}
