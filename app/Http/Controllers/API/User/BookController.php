<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Books;
use App\Models\Authors;
use App\Http\Library\HttpResponse;
use Illuminate\Support\Facades\Validator;


class BookController extends Controller
{
    public function getAllProducts()
    {
        try {
            $books = Books::with(['category','author','images'])->get();
            return HttpResponse::respondWithSuccess($books);
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }

    public function filterByPrice(Request $request)
    {
        $rules = [
            'min_price' => 'required|numeric|min:0',
            'max_price' => 'required|numeric|min:0|gte:min_price',
        ];
        $messages = [
            'min_price.required' => 'Giá tối thiểu là bắt buộc.',
            'min_price.numeric' => 'Giá tối thiểu phải là số.',
            'min_price.min' => 'Giá tối thiểu không được âm.',
            'max_price.required' => 'Giá tối đa là bắt buộc.',
            'max_price.numeric' => 'Giá tối đa phải là số.',
            'max_price.min' => 'Giá tối đa không được âm.',
            'max_price.gte' => 'Giá tối đa phải lớn hơn hoặc bằng giá tối thiểu.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) return HttpResponse::respondError($validator->errors());
        try {
            $minPrice = $request->input('min_price');
            $maxPrice = $request->input('max_price');
            $books = Books::whereBetween('price', [$minPrice, $maxPrice])->get();
            return HttpResponse::respondWithSuccess($books);
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }

    public function getBooksSortedByPriceDesc()
    {
        try {
            $books = Books::orderBy('price', 'desc')->get();
        return HttpResponse::respondWithSuccess($books);
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }

    public function getBooksSortedByPriceAsc()
    {
        try {
            $books = Books::orderBy('price', 'asc')->get();
        return HttpResponse::respondWithSuccess($books);
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }

    public function getNewBook()
    {
        try {
            $books = Books::with(['author', 'images'])
                    ->orderBy('created_at', 'desc')  
                    ->get();
        return HttpResponse::respondWithSuccess($books);
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }

    public function getBestSellers()
    {
        try {
            $bestSellers = Books::with(['author', 'images']) 
            ->take(10)
            ->get();
            return HttpResponse::respondWithSuccess($bestSellers);
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }

    public function getBookDetails($id)
    {
        try {
            $books = Books::with(['category','author','images'])->find($id);
            if(!$books)
            {
                return HttpResponse::respondNotFound();
            }else
            {
                return HttpResponse::respondWithSuccess($books);
            }
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }

    public function getBookByCategory($category_id)
    {
        try {
            $books = Books::where('category_id',$category_id)->with(['author','images'])->get();
            if(!$books)
            {
                return HttpResponse::respondNotFound();
            }else
            {
                return HttpResponse::respondWithSuccess($books);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return HttpResponse::respondNotFound();
        }
    }

    public function search(Request $request)
    {
        try {
            $query = $request->input('query');
            if (empty($query)) return HttpResponse::respondError('Query is required.');
            $books = Books::with(['category','author', 'images'])
                ->where('name', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%")
                ->orWhereHas('author', function ($queryBuilder) use ($query) 
                {
                    $queryBuilder->where('name', 'LIKE', "%{$query}%");
                })->get();
            if ($books->isEmpty()) return HttpResponse::respondError('No books found');
            return HttpResponse::respondWithSuccess($books);
        } catch (\Throwable $th) {
            return HttpResponse::respondNotFound();
        }
    }
}
