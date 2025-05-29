<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }
    
    /**
     * Display a listing of the roles
     */
    public function index()
    {
        $roles = Role::withCount('users')->get();
        return view('admin.roles.index', compact('roles'));
    }
    
    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        return view('admin.roles.create');
    }
    
    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }
        
        $role = Role::create([
            'name' => strtolower($request->input('name')),
            'description' => $request->input('description'),
        ]);
        
        return response()->json([
            'status' => 200,
            'message' => 'Role created successfully!',
            'role' => $role,
        ]);
    }
    
    /**
     * Get role details
     */
    public function edit($id)
    {
        $role = Role::with('users')->findOrFail($id);
        
        return response()->json([
            'status' => 200,
            'role' => $role,
        ]);
    }
    
    /**
     * Update role
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }
        
        $role = Role::findOrFail($id);
        
        // Prevent editing core roles (admin, editor, user)
        if (in_array($role->name, ['admin', 'editor', 'user'])) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot modify core system roles.',
            ]);
        }
        
        $role->update([
            'name' => strtolower($request->input('name')),
            'description' => $request->input('description'),
        ]);
        
        return response()->json([
            'status' => 200,
            'message' => 'Role updated successfully!',
            'role' => $role,
        ]);
    }
    
    /**
     * Delete role
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        
        // Prevent deleting core roles (admin, editor, user)
        if (in_array($role->name, ['admin', 'editor', 'user'])) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot delete core system roles.',
            ]);
        }
        
        // Move users to default 'user' role
        $defaultRole = Role::where('name', 'user')->first();
        if ($defaultRole) {
            User::where('role_id', $role->id)->update(['role_id' => $defaultRole->id]);
        }
        
        $role->delete();
        
        return response()->json([
            'status' => 200,
            'message' => 'Role deleted successfully!',
        ]);
    }
}
