<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CompanyProfileController extends Controller
{
    // Lấy danh sách tất cả hồ sơ công ty
    public function index()
    {
        return response()->json(CompanyProfile::all());
    }

    // Lấy chi tiết một hồ sơ công ty
    public function show($id)
    {
        $profile = CompanyProfile::findOrFail($id);
        return response()->json($profile);
    }

    // Tạo mới hồ sơ công ty
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->only(['title', 'content']);
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/company-profiles');
            $data['image_path'] = str_replace('public/', 'storage/', $path);
        }

        $profile = CompanyProfile::create($data);
        
        return response()->json($profile, 201);
    }

    // Cập nhật hồ sơ công ty
    public function update(Request $request, $id) {
        if ($request->isMethod('POST') && $request->has('_method')) {
            $request->setMethod($request->input('_method'));
        }
    
        // Validate request
        $request->validate([
            'title' => 'required|max:100',
            'content' => 'nullable|max:1000',
            'image_file' => 'nullable|mimes:jpg,png|max:2048',
        ]);
    
        // Tìm dữ liệu
        $item = CompanyProfile::findOrFail($id);
        $dataToUpdate = $request->only(['title', 'content']);
    
        // Xử lý file ảnh
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = 'items/' . $fileName;
    
            // Lưu file vào storage
            Storage::disk('public')->putFileAs('items', $file, $fileName);
            $dataToUpdate['image_path'] = $filePath;
        }
    
        // Cập nhật dữ liệu
        $item->update($dataToUpdate);
    
        return response(['data' => $item], 200);
    }
    
    
    // Xóa hồ sơ công ty
    public function destroy($id)
    {
        $profile = CompanyProfile::findOrFail($id);
        
        if ($profile->image_path) {
            Storage::delete(str_replace('storage/', 'public/', $profile->image_path));
        }
        
        $profile->delete();
        
        return response()->json(['message' => 'Company profile deleted successfully']);
    }
}
