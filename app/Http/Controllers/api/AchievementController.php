<?php

namespace App\Http\Controllers\Api;

use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AchievementController extends Controller
{
    public function index()
    {
        $achievements = Achievement::orderBy('date', 'desc')->get();
        return response()->json($achievements);
    }
    public function show($id)
    {
        $achievement = Achievement::find($id);

        if (!$achievement) {
            return response()->json(['message' => 'Không tìm thấy thành tựu'], 404);
        }

        return response()->json($achievement);
    }


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

        $validated['is_featured'] = $request->has('is_featured') ? $request->is_featured : false;

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('public/achievements', $fileName);
            $validated['image_path'] = Storage::url($path);
        }

        $achievement = Achievement::create($validated);

        return response()->json([
            'message' => 'Thành tựu đã được thêm thành công',
            'data' => $achievement
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if ($request->isMethod('POST') && $request->has('_method')) {
            $request->setMethod($request->input('_method'));
        }
        $achievement = Achievement::findOrFail($id);
        
        Log::info('Dữ liệu nhận được:', $request->all());

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:100',
            'date' => 'sometimes|date',
            'summary' => 'sometimes|string|max:500',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_featured' => 'boolean'
        ]);




        $validated['is_featured'] = $request->has('is_featured') ? $request->is_featured : false;

        if ($request->hasFile('image_file')) {
            if ($achievement->image_path) {
                $oldImage = str_replace('/storage', 'public', $achievement->image_path);
                Storage::delete($oldImage);
            }
            $file = $request->file('image_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('public/achievements', $fileName);
            $validated['image_path'] = Storage::url($path);
        }
        

        if (!$request->has('is_featured')) {
            $validated['is_featured'] = false;
        }

        $achievement->fill($validated);
        $achievement->save();
        

        return response()->json([
            'message' => 'Thành tựu đã được cập nhật',
            'data' => $achievement
        ]);
    }

    public function destroy($id)
    {
        $achievement = Achievement::findOrFail($id);

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
