<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use App\Http\Library\HttpResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class CatalogAdminController extends Controller
{
    // public function store(Request $request)
    // {
    //     if (Auth::user()->role !== 'admin') return HttpResponse::respondError('Bạn không có quyền truy cập');
    //     $request->validate([
    //         'name' => 'required|string',
    //         'image' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048',
    //     ]);
    //     $imageName = Str::random(32) . '.' . $request->image->getClientOriginalExtension();
    //     Storage::disk('public')->put($imageName, file_get_contents($request->image->getRealPath()));
    //     $imageUrl = Storage::url($imageName);
    //     $url = url($imageUrl);
    //     $category = Categories::create([
    //         'name' => $request->name,
    //         'image' => $url,
    //     ]);
    //     return response()->json($category, 201);
    // }
    public function store(Request $request)
    {
        // if (!Auth::check() || Auth::user()->role !== 'admin') {
        //     return response()->json(['error' => 'Bạn không có quyền truy cập'], 403);
        // }
        
        try {
            $request->user()->tokens()->delete();
            $request->validate([
                'name' => 'required|string|max:255',
                'image.*' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
            $imageUrl = null;
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $imageName = Str::random(32) . '.' . $request->image->getClientOriginalExtension();
                Storage::disk('public')->put($imageName, file_get_contents($request->image->getRealPath()));
                $imageUrl = url(Storage::url($imageName));
            } 
            elseif (filter_var($request->image, FILTER_VALIDATE_URL)) {
                try {
                    $response = Http::get($request->image);
                    if ($response->status() !== 200 || !str_contains($response->header('Content-Type'), 'image')) {
                        return HttpResponse::respondError('URL không hợp lệ hoặc không phải là ảnh');
                    }
                    $imageContent = $response->body();
                    $imageName = Str::random(32) . '.' . pathinfo(parse_url($request->image, PHP_URL_PATH), PATHINFO_EXTENSION);
                    Storage::disk('public')->put($imageName, $imageContent);
                    $imageUrl = url(Storage::url($imageName));
                } catch (\Exception $e) {
                    return HttpResponse::respondError('Không thể kết nối đến URL');
                }
            } 
            else {
                return HttpResponse::respondError('Dữ liệu hình ảnh không hợp lệ');
            }
            $category = Categories::create([
                'name' => $request->name,
                'image' => $imageUrl,
            ]);
            return HttpResponse::respondWithSuccess($category,'Thêm thành công');
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
        
    }

    public function show($id)
    {
        $category = Categories::findOrFail($id);
        return HttpResponse::respondWithSuccess($category);
    }

    public function update(Request $request, $id)
    {
        // if (Auth::user()->role !== 'admin') return HttpResponse::respondError('Bạn không có quyền truy cập');
        $category = Categories::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable', // Cho phép URL hoặc file ảnh
        ]);
        $dataToUpdate = ['name' => $request->name];
        if ($request->image) {
            $storage = Storage::disk('public');
            if ($category->image) {
                $oldImagePath = str_replace('/storage/', 'public/', $category->image);
                if ($storage->exists($oldImagePath)) {
                    $storage->delete($oldImagePath);
                }
            }
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                // Lưu file ảnh từ request vào storage
                $image = $request->file('image');
                Storage::disk('public')->put($imageName, file_get_contents($request->image->getRealPath()));
                $imageUrl = Storage::url($imageName);
                $url = url($imageUrl);
                $dataToUpdate['image'] = $url ;
            } elseif (filter_var($request->image, FILTER_VALIDATE_URL)) {
                // Nếu image là một URL, tải ảnh về từ URL
                $imageUrl = $request->image;
                $imageContent = file_get_contents($imageUrl);
                $imageName = Str::random(32) . '.' . pathinfo($imageUrl, PATHINFO_EXTENSION);
                Storage::disk('public')->put($imageName, $imageContent);
                $imageUrl = Storage::url($imageName);
                $url = url($imageUrl);
                $dataToUpdate['image'] = $url ;
            } else {
                return HttpResponse::respondError('Dữ liệu hình ảnh không hợp lệ');
            }
        }
        $category->update($dataToUpdate);
        return HttpResponse::respondWithSuccess($category,'Update thành công');
    }

    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') return HttpResponse::respondError('Bạn không có quyền truy cập');
        $category = Categories::findOrFail($id);
        $category->delete();
        return HttpResponse::respondWithSuccess(null,'Xóa thành công');
    }
}
