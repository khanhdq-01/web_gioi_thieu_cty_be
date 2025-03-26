<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller {
    public function index() {
        return response()->json(Job::all());
    }

    public function store(Request $request) {
        $job = Job::create($request->all());
        return response()->json($job, 201);
    }

    public function show($id) {
        return response()->json(Job::findOrFail($id));
    }

    public function update(Request $request, $id) {
        $job = Job::findOrFail($id);
        $job->update($request->all());
        return response()->json($job);
    }

    public function destroy($id) {
        Job::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
