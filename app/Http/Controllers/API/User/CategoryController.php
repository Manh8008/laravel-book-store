<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use App\Http\Library\HttpResponse;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Categories::all();
        return HttpResponse::respondWithSuccess($categories);
    }
}
