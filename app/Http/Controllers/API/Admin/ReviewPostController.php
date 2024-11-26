<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reviews;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Http\Library\HttpResponse;

class ReviewPostController extends Controller
{
    public function store(Request $request)
    {
        try {
            if (!Auth::check() || Auth::user()->role !== 'admin') return HttpResponse::respondError('Bạn không có quyền truy cập');
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // Hình ảnh không bắt buộc
            ]);

            if ($validator->fails()) return HttpResponse::respondError($validator->errors());
            $imageUrl = null;
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $imageName = Str::random(32) . '.' . $request->image->getClientOriginalExtension();
                Storage::disk('public')->put($imageName, file_get_contents($request->image->getRealPath()));
                $imageUrl = url(Storage::url($imageName));
            } elseif ($request->filled('image') && filter_var($request->image, FILTER_VALIDATE_URL)) {
                $response = Http::get($request->image);
                if ($response->successful() && str_contains($response->header('Content-Type'), 'image')) {
                    $imageName = Str::random(32) . '.' . pathinfo(parse_url($request->image, PHP_URL_PATH), PATHINFO_EXTENSION);
                    Storage::disk('public')->put($imageName, $response->body());
                    $imageUrl = url(Storage::url($imageName));
                } else {
                    return response()->json(['success' => false, 'message' => 'URL không hợp lệ hoặc không phải là ảnh'], 422);
                }
            }
            $review = Reviews::create([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'image_url' => $imageUrl
            ]);
            return HttpResponse::respondWithSuccess($review,'Tạo thành công');
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo review.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request,$id)
    {
        try {
            if (!Auth::check() || Auth::user()->role !== 'admin') return HttpResponse::respondError('Bạn không có quyền truy cập');
            $review = Reviews::find($id);
            if (!$review) return HttpResponse::respondError('Review không tồn tại');
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'nullable' 
            ]);
            if ($validator->fails()) return HttpResponse::respondError($validator->errors());
            $dataToUpdate = [
                'title' => $request->title,
                'description' => $request->description,
            ];
            if ($request->image) {
                $storage = Storage::disk('public');
                if ($review->image) {
                    $oldImagePath = str_replace('/storage/', 'public/', $review->image);
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
                    $dataToUpdate['image_url'] = $url ;
                } elseif (filter_var($request->image, FILTER_VALIDATE_URL)) {
                    // Nếu image là một URL, tải ảnh về từ URL
                    $imageUrl = $request->image;
                    $imageContent = file_get_contents($imageUrl);
                    $imageName = Str::random(32) . '.' . pathinfo($imageUrl, PATHINFO_EXTENSION);
                    Storage::disk('public')->put($imageName, $imageContent);
                    $imageUrl = Storage::url($imageName);
                    $url = url($imageUrl);
                    $dataToUpdate['image_url'] = $url ;
                } else {
                    return HttpResponse::respondError('Dữ liệu hình ảnh không hợp lệ');
                }
            }
            $review->update($dataToUpdate);
            return HttpResponse::respondWithSuccess($dataToUpdate,'Update thành công');
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật review.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            if (!Auth::check() || Auth::user()->role !== 'admin') return HttpResponse::respondError('Bạn không có quyền truy cập');
            $review = Reviews::findOrFail($id);
            if ($review->image_url) {
                $imagePath = str_replace('/storage/', '', $review->image_url);
                Storage::disk('public')->delete($imagePath);
            }
            $review->delete();
            return HttpResponse::respondWithSuccess(null,'Xóa thành công');
        } catch (\Throwable $th) {
            return response()->json(['error' => 'ReviewsPost not found'], 404);
        }
    }

    public function getAllPost()
    {
        try {
            $reviews = Reviews::all();
            if ($reviews->isEmpty()) return HttpResponse::respondError('Không có bài viết nào');
            return HttpResponse::respondWithSuccess($reviews, 'Lấy tất cả bài viết thành công');
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách bài viết',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getPostById($id)
    {
        try {
            $review = Reviews::findOrFail($id);
            return HttpResponse::respondWithSuccess($review, 'Lấy bài viết thành công');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return HttpResponse::respondError('Bài viết không tồn tại');
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy bài viết',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
