<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller {
    public function store(Request $request) {
        $request->validate([
            'job_id' => 'required|exists:jobs,id',
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required',
            'cv' => 'required|mimes:pdf,doc,docx|max:2048',
        ]);

        $cvPath = $request->file('cv')->store('cvs');

        $application = Application::create([
            'job_id' => $request->job_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'cv' => $cvPath,
        ]);

        return response()->json($application, 201);
    }

    public function index() {
        return response()->json(Application::with('job')->get());
    }

    public function show($id) {
        return response()->json(Application::with('job')->findOrFail($id));
    }

    public function downloadCV($id) {
        $application = Application::findOrFail($id);
        return Storage::download($application->cv);
    }
}
