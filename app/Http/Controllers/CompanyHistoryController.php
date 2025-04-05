<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyHistory;

class CompanyHistoryController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'nullable|string|max:20',
            'event' => 'required|string'
        ]);


        $history = CompanyHistory::create($request->all());
        return response(['data' => $history], 201);
    }

    public function index() {
        $history = CompanyHistory::all();
        return response(['data'=> $history]);
    }

    public function show($id) {
        $history = CompanyHistory::findOrFail($id);
        return response()->json($history);
    }

    public function update(Request $request, $id) {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'nullable|string|max:20',
            'event' => 'required|string'
        ]);

        $history = CompanyHistory::findOrFail($id);

        $history->update($request->all());

        return response(['data' => $history], 200);
    }

    public function destroy($id) {
        $history = CompanyHistory::findOrFail($id);
        $history->delete();

        return response()->json([
            'message' => 'Company history deleted successfully',
            'data' => $history
        ]);
    }
}
