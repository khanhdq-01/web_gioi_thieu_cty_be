<?php

// app/Http/Controllers/Api/AchievementController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AchievementController extends Controller
{
    // Danh sách thành tựu
    public function index()
    {
        $achievements = Achievement::orderBy('date', 'desc')->get();
        return response()->json($achievements);
    }

    // Thêm thành tựu mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'date' => 'required|date',
            'summary' => 'required|string|max:500',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_featured' => 'boolean'
        ]);

        // Xử lý upload ảnh
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('public/achievements');
            $validated['image_path'] = Storage::url($path);
        }

        $achievement = Achievement::create($validated);
        
        return response()->json([
            'message' => 'Thành tựu đã được thêm thành công',
            'data' => $achievement
        ], 201);
    }

    // Cập nhật thành tựu
    public function update(Request $request, $id)
    {
        $achievement = Achievement::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:100',
            'date' => 'sometimes|date',
            'summary' => 'sometimes|string|max:500',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_featured' => 'boolean'
        ]);

        // Xử lý upload ảnh mới
        if ($request->hasFile('image_file')) {
            // Xóa ảnh cũ nếu có
            if ($achievement->image_path) {
                $oldImage = str_replace('/storage', 'public', $achievement->image_path);
                Storage::delete($oldImage);
            }
            
            $path = $request->file('image_file')->store('public/achievements');
            $validated['image_path'] = Storage::url($path);
        }

        $achievement->update($validated);
        
        return response()->json([
            'message' => 'Thành tựu đã được cập nhật',
            'data' => $achievement
        ]);
    }

    // Xóa thành tựu
    public function destroy($id)
    {
        $achievement = Achievement::findOrFail($id);

        // Xóa ảnh nếu có
        if ($achievement->image_path) {
            $oldImage = str_replace('/storage', 'public', $achievement->image_path);
            Storage::delete($oldImage);
        }

        $achievement->delete();
        
        return response()->json([
            'message' => 'Thành tựu đã được xóa'
        ]);
    }
}