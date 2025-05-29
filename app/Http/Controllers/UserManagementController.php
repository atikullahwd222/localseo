<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Users need to be admin to manage all users, editors can only approve users
        $this->middleware(['auth', 'role:admin,editor']);
    }
    
    /**
     * Display a listing of pending users
     */
    public function pendingUsers()
    {
        $pendingUsers = User::where('status', 'inactive')
                           ->with('role')
                           ->orderBy('created_at', 'desc')
                           ->get();
                           
        return view('admin.users.pending', compact('pendingUsers'));
    }
    
    /**
     * Display a listing of all users
     */
    public function index()
    {
        // Only admins can view all users
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('admin.users.pending')
                ->with('error', 'You do not have permission to view all users.');
        }
        
        $users = User::with('role')->orderBy('created_at', 'desc')->get();
        $roles = Role::all();
        
        return view('admin.users.index', compact('users', 'roles'));
    }
    
    /**
     * Approve a user
     */
    public function approveUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // If user is already active, no need to change
        if ($user->status === 'active') {
            return response()->json([
                'status' => 400,
                'message' => 'User is already active.',
            ]);
        }
        
        $user->status = 'active';
        $user->save();
        
        return response()->json([
            'status' => 200,
            'message' => 'User approved successfully!',
            'user' => $user,
        ]);
    }
    
    /**
     * Change user role
     */
    public function changeRole(Request $request, $id)
    {
        // Only admins can change roles
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'status' => 403,
                'message' => 'You do not have permission to change user roles.',
            ]);
        }
        
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }
        
        $user = User::findOrFail($id);
        
        // Prevent changing your own role to prevent lock-out
        if ($user->id === auth()->id()) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot change your own role.',
            ]);
        }
        
        $user->role_id = $request->role_id;
        $user->save();
        
        return response()->json([
            'status' => 200,
            'message' => 'User role changed successfully!',
            'user' => $user,
        ]);
    }
    
    /**
     * Change user status
     */
    public function changeStatus(Request $request, $id)
    {
        // Only admins can change status
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'status' => 403,
                'message' => 'You do not have permission to change user status.',
            ]);
        }
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,suspended',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }
        
        $user = User::findOrFail($id);
        
        // Prevent changing your own status to prevent lock-out
        if ($user->id === auth()->id()) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot change your own status.',
            ]);
        }
        
        $user->status = $request->status;
        $user->save();
        
        return response()->json([
            'status' => 200,
            'message' => 'User status changed successfully!',
            'user' => $user,
        ]);
    }
}
