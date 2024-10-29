<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Books;
use App\Models\Authors;
use App\Http\Library\HttpResponse;

class BookController extends Controller
{
    public function getAllProducts()
    {
        $books = Books::with(['author','images'])->get();
        return HttpResponse::respondWithSuccess($books);
    }

    public function getBookDetails($id)
    {
        $books = Books::with(['author','images'])->find($id);
        if(!$books)
        {
            return HttpResponse::respondNotFound();
        }else
        {
            return HttpResponse::respondWithSuccess($books);
        }
    }

    public function getBookByCategory($category_id)
    {
        $books = Books::where('category_id',$category_id)->with(['author','images'])->get();
        if(!$books)
        {
            return HttpResponse::respondNotFound();
        }else
        {
            return HttpResponse::respondWithSuccess($books);
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        if (empty($query)) 
        {
            return HttpResponse::respondError('Query is required.');
        }
        $books = Books::with(['author', 'images'])
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orWhereHas('author', function ($queryBuilder) use ($query) 
            {
                $queryBuilder->where('name', 'LIKE', "%{$query}%");
            })->get();
        if ($books->isEmpty()) {
            return response()->json([
                'message' => 'No books found.',
            ], 404);
        }
        return HttpResponse::respondWithSuccess($books);
    }
}
