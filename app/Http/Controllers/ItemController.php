<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{

    public function index() {
        $iems = Item::select('id', 'name', 'price', 'image')->get();
        return response(['data'=> $iems]);
    }

    public function show($id){
        $item = Item::findOrFail($id);
        return response(['data'=>$item]);

    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        if (!$item) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $item->delete();
        return response()->json([
            'message' => 'Order deleted successfully',
            'data' => $item
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|max:100',
            'price' => 'required|integer',
            'image_file' => 'nullable|mimes:jpg,png',
        ]);

        if($request->file('image_file')) {
           $file = $request->file('image_file');
           $fileName = $file->getClientOriginalName();
           $newName = Carbon::now()->timestamp.'_'.$fileName;

            Storage::disk('public')->putFileAs('items',$file, $newName);
            $request['image'] =$newName;
        }
            $item = Item::create($request->all());
            return response(['data' => $item]);
    }

    public function update(Request $request, $id) {
        $request->validate([
            'name' => 'required|max:100',
            'price' => 'required|integer',
            'image_file' => 'nullable|mimes:jpg,png',
        ]);

        if($request->file('image_file')) {
            $file = $request->file('image_file');
            $fileName = $file->getClientOriginalName();
            $newName = Carbon::now()->timestamp.'_'.$fileName;
 
             Storage::disk('public')->putFileAs('items',$file, $newName);
             $request['image'] =$newName;
         }

        $item = Item::findOrFail($id);
        $item->update($request->all());
        return response(['data' => $item]);
    }
}
