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
use Illuminate\Support\Facades\Auth;

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
            if ($request->images instanceof \Illuminate\Http\UploadedFile) 
                {
                    $imageName = Str::random(32) . '.' . $request->images->getClientOriginalExtension();
                    Storage::disk('public')->put($imageName, file_get_contents($request->images->getRealPath()));
                    $imageUrl = Storage::url($imageName);
                    $url = url($imageUrl);
                    Images::create([
                        'book_id' => $book->id,
                        'url' => $url, 
                    ]);
                } else 
                {
                    $imageName = Str::random(32) . '.' . pathinfo($request->images, PATHINFO_EXTENSION);
                    Storage::disk('public')->put($imageName, file_get_contents($request->images));
                    $imageUrl = Storage::url($imageName);
                    Images::create([
                        'book_id' => $book->id,
                        'url' => $imageUrl, 
                    ]);
                }
            DB::commit();
            return response()->json($book->load('author', 'category', 'images'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create book: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $book = Books::find($id);
            if (!$book) {
                return response()->json(['message' => 'Book not found'], 404);
            }
            // Validate request data
            $validator = Validator::make($request->all(), [
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
                'images.*' => 'file|mimes:jpeg,png,jpg,gif',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            DB::beginTransaction();
            $book->update([
                'name' => $request->name,
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
                'category_id' => $request->category_id,
                'weight' => $request->weight,
                'size' => $request->size,
                'pages' => $request->pages,
                'language' => $request->language,
                'format' => $request->format,
                'short_summary' => $request->short_summary,
                'publisher' => $request->publisher,
            ]);
            $author = $book->author;
            if ($author) {
                $author->update([
                    'name' => $request->authorName,
                    'bio' => $request->authorBio,
                ]);
            }
            $storage = Storage::disk('public'); // Khởi tạo storage
            // Xóa hình ảnh cũ (nếu có)
            $book->images()->delete();
            
            if ($request->images instanceof \Illuminate\Http\UploadedFile) 
                {
                    $imageName = Str::random(32) . '.' . $request->images->getClientOriginalExtension();
                    Storage::disk('public')->put($imageName, file_get_contents($request->images->getRealPath()));
                    $imageUrl = Storage::url($imageName);
                    $url = url($imageUrl);
                    Images::create([
                        'book_id' => $book->id,
                        'url' => $url, 
                    ]);
                } else 
                {
                    $imageName = Str::random(32) . '.' . pathinfo($request->images, PATHINFO_EXTENSION);
                    Storage::disk('public')->put($imageName, file_get_contents($request->images));
                    $imageUrl = Storage::url($imageName);
                    $url = url($imageUrl);
                    Images::create([
                        'book_id' => $book->id,
                        'url' => $url, 
                    ]);
                }
            DB::commit();
            return response()->json($book->load('author', 'category', 'images'), 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update book: ' . $e->getMessage()], 500);
        }
    }   

    public function destroy($id)
    {
        try {
            $book = Books::find($id);
            if (!$book) {
                return response()->json(['message' => 'Book not found'], 404);
            }
            $book->images()->delete(); 
            if ($book->author) {
                $author = $book->author;
                $author->delete(); 
            }
            $book->delete();
            return response()->json(['message' => 'Book and related author deleted successfully'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to delete book: ' . $e->getMessage()], 500);
        }
    }

}
