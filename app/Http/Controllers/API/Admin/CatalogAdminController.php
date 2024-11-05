<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use App\Http\Library\HttpResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogAdminController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'image' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048|unique:categories,image',
        ]);
        $imageName = Str::random(32) . '.' . $request->image->getClientOriginalExtension();
        Storage::disk('public')->put($imageName, file_get_contents($request->image->getRealPath()));
        $imageUrl = Storage::url($imageName);
        $url = url($imageUrl);
        $category = Categories::create([
            'name' => $request->name,
            'image' => $url,
        ]);
        return response()->json($category, 201);
    }

    public function show($id)
    {
        $category = Categories::findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
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
                return response()->json(['error' => 'Invalid image data'], 400);
            }
        }
        $category->update($dataToUpdate);
        return response()->json($category, 200);
    }

    public function destroy($id)
    {
        $category = Categories::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Category deleted']);
    }
}
