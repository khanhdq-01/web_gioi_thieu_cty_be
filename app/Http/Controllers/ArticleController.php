<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|max:100',
            'description' => 'required|max:300',
            'image' => 'nullable|mimes:jpg,png',
        ]);

        $imageName = null;
        if ($request->file('image')) {
            $file = $request->file('image');
            $fileName = $file->getClientOriginalName();
            $newName = Carbon::now()->timestamp . '_' . $fileName;
            
            // Store the image in the public disk under the 'articles' folder
            Storage::disk('public')->putFileAs('articles', $file, $newName);
            $imageName = $newName;
        }
    
        // Create the articles and store only relevant fields
        $article = Article::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'image' => $imageName,  // Store the image file name if it exists
        ]);
    
        return response(['data' => $article], 201);  // Added status code for resource creation
    }
    
    

    public function index() {
        $articles = Article::select('id', 'name','description', 'image')->get();
        return response(['data'=> $articles]);
    }

    public function show($id)
    {
        $articles = Article::findOrFail($id);
        return response()->json($articles);
    }

    public function update(Request $request, $id) {
        if ($request->isMethod('POST') && $request->has('_method')) {
            $request->setMethod($request->input('_method'));
        }
        // Validate request
        $request->validate([
            'name' => 'required|max:100',
            'description' => 'required|max:300',
            'image' => 'nullable|mimes:jpg,png|max:2048',
        ]);

        // Tìm dữ liệu
        $article = Article::findOrFail($id);
        $dataToUpdate = $request->only(['name','description', 'image']);
    
        // Xử lý file ảnh
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath =  $fileName;
    
            // Lưu file vào storage
            Storage::disk('public')->putFileAs('articles', $file, $fileName);
            $dataToUpdate['image'] = $filePath;
        }
    
        // Cập nhật dữ liệu
        $article->update($dataToUpdate);
    
        return response(['data' => $article], 200);
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);

        if (!$article) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $article->delete();
        return response()->json([
            'message' => 'Order deleted successfully',
            'data' => $article
        ]);
    }

}
