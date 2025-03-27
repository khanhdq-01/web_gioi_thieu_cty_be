<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slide;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class SlideController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'title' => 'required|max:100',
            'image_path' => 'nullable|mimes:jpg,png',
        ]);
    
        $imageName = null;
        if ($request->file('image_path')) {
            $file = $request->file('image_path');
            $fileName = $file->getClientOriginalName();
            $newName = Carbon::now()->timestamp . '_' . $fileName;
            
            // Store the image in the public disk under the 'slides' folder
            Storage::disk('public')->putFileAs('slides', $file, $newName);
            $imageName = $newName;
        }
    
        // Create the slide and store only relevant fields
        $slide = Slide::create([
            'title' => $request->input('title'),
            'image_path' => $imageName,  // Store the image file name if it exists
        ]);
    
        return response(['data' => $slide], 201);  // Added status code for resource creation
    }
    
    

    public function index() {
        $slides = Slide::select('id', 'title', 'image_path')->get();
        return response(['data'=> $slides]);
    }

    public function destroy($id)
    {
        $slide = Slide::findOrFail($id);

        if (!$slide) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $slide->delete();
        return response()->json([
            'message' => 'Order deleted successfully',
            'data' => $slide
        ]);
    }

}
