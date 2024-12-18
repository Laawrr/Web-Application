<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function index()
    {
        $users = User::all(); // Get all users
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $user = User::create($request->all());

        // Log user creation
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'User created: ' . $user->name,
            'action_time' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());

        // Log user update
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'User updated: ' . $user->name,
            'action_time' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        // Log user deletion
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'User deleted: ' . $user->name,
            'action_time' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);

        return response()->json(null, 204);
    }
    public function getID(){
        $userID = Auth::id();
        return response()->json(['id' => $userID]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }
}
