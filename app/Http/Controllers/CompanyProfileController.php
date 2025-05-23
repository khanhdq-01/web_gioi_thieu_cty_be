<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class CompanyProfileController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'title' => 'required|max:100',
            'content' => 'required|max:300',
            'image_path' => 'nullable|mimes:jpg,png',
        ]);

        $imageName = null;
        if ($request->file('image_path')) {
            $file = $request->file('image_path');
            $fileName = $file->getClientOriginalName();
            $newName = Carbon::now()->timestamp . '_' . $fileName;
            
            // Store the image in the public disk under the 'companys' folder
            Storage::disk('public')->putFileAs('company-profiles', $file, $newName);
            $imageName = $newName;
        }
    
        // Create the company and store only relevant fields
        $company = CompanyProfile::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'image_path' => $imageName,  // Store the image file name if it exists
        ]);
    
        return response(['data' => $company], 201);  // Added status code for resource creation
    }
    
    

    public function index() {
        $companys = CompanyProfile::select('id', 'title','content', 'image_path')->get();
        return response(['data'=> $companys]);
    }

    public function show($id)
    {
        $companys = CompanyProfile::findOrFail($id);
        return response()->json($companys);
    }

    public function update(Request $request, $id) {
        if ($request->isMethod('POST') && $request->has('_method')) {
            $request->setMethod($request->input('_method'));
        }
        // Validate request
        $request->validate([
            'title' => 'required|max:100',
            'content' => 'required|max:300',
            'image_path' => 'nullable|mimes:jpg,png|max:2048',
        ]);

        // Tìm dữ liệu
        $company = CompanyProfile::findOrFail($id);
        $dataToUpdate = $request->only(['title','content', 'image_path']);
    
        // Xử lý file ảnh
        if ($request->hasFile('image_path')) {
            $file = $request->file('image_path');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath =  $fileName;
    
            // Lưu file vào storage
            Storage::disk('public')->putFileAs('companys', $file, $fileName);
            $dataToUpdate['image_path'] = $filePath;
        }
    
        // Cập nhật dữ liệu
        $company->update($dataToUpdate);
    
        return response(['data' => $company], 200);
    }

    public function destroy($id)
    {
        $company = CompanyProfile::findOrFail($id);

        if (!$company) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $company->delete();
        return response()->json([
            'message' => 'Order deleted successfully',
            'data' => $company
        ]);
    }

}
