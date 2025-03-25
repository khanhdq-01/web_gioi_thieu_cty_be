<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{

    public function index() {
        $iems = Article::select('id','name','description', 'image','seller_id')->get();
        return response(['data'=> $iems]);
    }

    public function show($id){
        $item = Article::findOrFail($id);
        return response(['data'=>$item]);

    }

    public function destroy($id)
    {
    // Tìm sản phẩm theo ID
    $item = Article::findOrFail($id);

    // Xóa sản phẩm (Soft Delete)
    $item->delete();

    return response()->json([
        'message' => 'Product deleted successfully',
        'data' => $item
    ], 200);
    }
    public function store(Request $request) {
        // Validate các trường
        $request->validate([
            'name' => 'required|max:100',
            'description' => 'required|max:3000',
            'image_file' => 'nullable|mimes:jpg,png|max:2048', // Validate file ảnh
        ]);
    
        // Xử lý file ảnh (nếu có)
        if ($request->file('image_file')) {
            $file = $request->file('image_file');
            $fileName = $file->getClientOriginalName();
            $newName = Carbon::now()->timestamp . '_' . $fileName;
    
            Storage::disk('public')->putFileAs('items', $file, $newName);
            $request['image'] = $newName; // Lưu tên file vào cột image
        }
    
        // Lấy seller_id từ người dùng hiện tại
        $request['seller_id'] = auth()->user()->id;
    
        // Tạo sản phẩm
        $item = Article::create($request->only(['name', 'description', 'image', 'seller_id']));
    
        return response(['data' => $item], 201);
    }

    public function update(Request $request, $id) {
        // Validate các trường
        $request->validate([
            'name' => 'required|max:100',
            'description' => 'nullable|max:300', // Cho phép mô tả có thể không thay đổi
            'image_file' => 'nullable|mimes:jpg,png|max:2048', // Validate file ảnh
        ]);
    
        // Tìm sản phẩm theo ID
        $item = Article::findOrFail($id);
    
        // Xử lý file ảnh (nếu có)
        if ($request->file('image_file')) {
            $file = $request->file('image_file');
            $fileName = $file->getClientOriginalName();
            $newName = Carbon::now()->timestamp . '_' . $fileName;
    
            // Lưu file vào thư mục 'items' trong storage
            Storage::disk('public')->putFileAs('items', $file, $newName);
    
            // Gán tên file mới vào request để cập nhật cột 'image'
            $request['image'] = $newName;
        }
    
        // Chỉ cập nhật các trường cần thiết
        $item->update($request->only(['name', 'description', 'image']));
    
        return response(['data' => $item], 200);
    }
}
