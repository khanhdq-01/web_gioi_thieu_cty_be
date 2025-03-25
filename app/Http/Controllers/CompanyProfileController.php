<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Storage;

class CompanyProfileController extends Controller
{
// app/Http/Controllers/CompanyProfileController.php
public function index()
{
    return response()->json(CompanyProfile::all());
}

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
