<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{

    public function index() {
        $iems = Product::select('id','name','description', 'price','stock' ,'image','seller_id')->get();
        return response(['data'=> $iems]);
    }

    public function show($id){
        $item = Product::findOrFail($id);
        return response(['data'=>$item]);

    }

    public function destroy($id)
    {
    // Tìm sản phẩm theo ID
    $item = Product::findOrFail($id);

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
            'description' => 'required|max:300',
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0', // Validate stock
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
        $item = Product::create($request->only(['name', 'description', 'price', 'stock', 'image', 'seller_id']));
    
        return response(['data' => $item], 201);
    }

    public function update(Request $request, $id) {
        // Validate các trường
        $request->validate([
            'name' => 'required|max:100',
            'description' => 'nullable|max:300', // Cho phép mô tả có thể không thay đổi
            'price' => 'required|integer|min:0',
            'stock' => 'nullable|integer|min:0', // Cho phép cập nhật tồn kho
            'image_file' => 'nullable|mimes:jpg,png|max:2048', // Validate file ảnh
        ]);
    
        // Tìm sản phẩm theo ID
        $item = Product::findOrFail($id);
    
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
        $item->update($request->only(['name', 'description', 'price', 'stock', 'image']));
    
        return response(['data' => $item], 200);
    }
}
