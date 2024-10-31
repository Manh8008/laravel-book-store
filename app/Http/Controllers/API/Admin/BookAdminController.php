<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Books;
use App\Models\Authors;
use App\Models\Images;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Http\Library\HttpResponse;
use Illuminate\Support\Str;

class BookAdminController extends Controller
{
    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'weight' => 'nullable|numeric',
            'size' => 'nullable|string',
            'pages' => 'nullable|integer',
            'language' => 'nullable|string',
            'format' => 'nullable|string',
            'short_summary' => 'nullable|string',
            'publisher' => 'nullable|string',
            'category_id' => 'required|integer',   
            'authorName' => 'required|string|max:255',
            'authorBio' => 'nullable|string',
            // ''images' => 'required|string',
            // 'images' => 'required|array',
            'images.*' => 'required|file|mimes:jpeg,png,jpg,gif',
        ]);

        // Kiểm tra nếu dữ liệu không hợp lệ
        // if ($validator->fails()) {
        //     return response()->json([
        //         'errors' => 'lỗi'
        //     ], 422);
        // }
        // $validated = $validator->validated();
        DB::beginTransaction();
        try {
            $author = Authors::create([
                'name' => $request->authorName,
                'bio' => $request->authorBio ?? null,
            ]);
            $book = Books::create([
                'name' => $request->name,
                'title' => $request->title,
                'description' =>$request->description ?? null,
                'price' => $request->price,
                'stock' => $request->stock,
                'author_id' => $author->id, 
                'category_id' => $request->category_id,
                'weight' => $request->weight ?? null,
                'size' => $request->size ?? null,
                'pages' => $request->pages ?? null,
                'language' => $request->language ?? null,
                'format' => $request->format ?? null,
                'short_summary' => $request->short_summary ?? null,
                'publisher' => $request->publisher ?? null,
            ]);
            // foreach ($request->images as $imageData) {
            //     $imageName = Str::random(32).'.'.$request->images->getClientOriginalExtension();
            //     Storage::disk('public')->put($imageName,file_get_contents($request->images));
            //     Images::create([
            //         'book_id' => $book->id, 
            //         'url' => $imageName,
            //     ]);
            // // }
            // if (isset($request->images) && is_array($request->images)) {
            //     foreach ($request->images as $imageData) {
            //         if ($imageData instanceof \Illuminate\Http\UploadedFile) {
            //             $imageName = Str::random(32) . '.' . $imageData->getClientOriginalExtension();
            //             Storage::disk('public')->put($imageName, file_get_contents($imageData->getRealPath()));
            //             Images::create([
            //                 'book_id' => $book->id,
            //                 'url' => $imageName,
            //             ]);
            //         } else {
            //             // Nếu imageData là một URL
            //             $imageName = Str::random(32) . '.' . pathinfo($imageData, PATHINFO_EXTENSION);
            //             Storage::disk('public')->put($imageName, file_get_contents($imageData));
            //             Images::create([
            //                 'book_id' => $book->id,
            //                 'url' => $imageName,
            //             ]);
            //         }
            //     }
            // }
            if ($request->images instanceof \Illuminate\Http\UploadedFile) {
            $imageName = Str::random(32) . '.' . $request->images->getClientOriginalExtension();
            Storage::disk('public')->put($imageName, file_get_contents($request->images->getRealPath()));
            
                
            $imageUrl = Storage::url($imageName);
            $url = url($imageUrl);
            Images::create([
                'book_id' => $book->id,
                'url' => $url, 
            ]);
            } else {
                $imageName = Str::random(32) . '.' . pathinfo($request->images, PATHINFO_EXTENSION);
                Storage::disk('public')->put($imageName, file_get_contents($request->images));
                
                
                $imageUrl = Storage::url($imageName);
                
                Images::create([
                    'book_id' => $book->id,
                    'url' => $imageUrl, 
                ]);
        }
            // if (isset($request->images) && is_array($request->images)) {
            //     foreach ($request->images as $imageData) {
            //         if (is_string($imageData)) {
            //             $imageName = Str::random(32) . '.' . pathinfo($imageData, PATHINFO_EXTENSION);
            //             Storage::disk('public')->put($imageName, file_get_contents($imageData));
            //             Images::create([
            //                 'book_id' => $book->id,
            //                 'url' => $imageName,
            //             ]);
            //         }
            //     }
            // }
            // foreach ($request->images as $imageData) {
            //     Images::create([
            //         'book_id' => $book->id, 
            //         'url' => $imageData,
            //     ]);
            // }
            // Images::create([
            //         'book_id' => $book->id, 
            //         'url' => $request->images,
            //     ]);
            DB::commit();
            return response()->json($book->load('author', 'category', 'images'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create book: ' . $e->getMessage()], 500);
        }
    }
    
    
}
