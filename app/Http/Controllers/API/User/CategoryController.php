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


    public function search(Request $request)
    {
        try {
            $query = $request->input('query');
            if (empty($query)) return HttpResponse::respondError('Query is required.');
            $categories = Categories::where('name', 'LIKE', "%{$query}%")->get();
            if ($categories->isEmpty()) return HttpResponse::respondError('No books found');
            return HttpResponse::respondWithSuccess($categories);
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }
}
