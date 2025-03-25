<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserInfo;

class UserInfoController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'nullable|string',
        ]);

        // Save the data to the database
        $userInfo = UserInfo::create($validatedData);

        // Return a response
        return response()->json([
            'message' => 'User information saved successfully!',
            'data' => $userInfo,
        ], 201);
        
    }
    public function index()
    {
        // Lấy tất cả thông tin người dùng từ database
        $userInfos = UserInfo::all();

        // Trả về danh sách thông tin người dùng
        return response()->json([
            'message' => 'User information retrieved successfully!',
            'data' => $userInfos,
        ]);
    }

    public function destroy($id)
    {
        $userInfo = UserInfo::find($id);

        if (!$userInfo) {
            return response()->json([
                'code' => 404,
                'message' => 'Người dùng không tồn tại'
            ], 404);
        }

        $userInfo->delete();

        return response()->json([
            'code' => 200,
            'message' => 'Xóa thông tin người dùng thành công'
        ], 200);
    }

}