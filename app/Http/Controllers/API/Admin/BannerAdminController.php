<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banners;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Http\Library\HttpResponse;


class BannerAdminController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') return HttpResponse::respondError('Bạn không có quyền truy cập');
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'image.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048' // Hình ảnh không bắt buộc
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
            $banner = Banners::create([
                'title' => $request->input('title') ?? null,
                'description' => $request->input('description') ?? null,
                'image_url' => $imageUrl
            ]);
            return HttpResponse::respondWithSuccess($banner,'Tạo thành công');
        } catch (\Throwable $th) {return response()->json([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi tạo banner.',
            'error' => $th->getMessage()
        ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            if (!Auth::check() || Auth::user()->role !== 'admin') {
                return HttpResponse::respondError('Bạn không có quyền truy cập');
            }
            $banner = Banners::find($id);
            if (!$banner) {
                return HttpResponse::respondError('Banner không tồn tại');
            }
            if ($banner->image_url) {
                $storage = Storage::disk('public');
                $imagePath = str_replace('/storage/', '', $banner->image_url); // Loại bỏ tiền tố '/storage/'
                if ($storage->exists($imagePath)) {
                    $storage->delete($imagePath);
                }
            }
            $banner->delete();
            return HttpResponse::respondWithSuccess(null, 'Xóa banner thành công');
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa banner.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

}
