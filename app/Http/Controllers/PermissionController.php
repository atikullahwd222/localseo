<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }
    
    /**
     * Display a listing of the permissions grouped by module.
     */
    public function index()
    {
        $permissions = Permission::all()->groupBy('module');
        $roles = Role::all();
        
        return view('admin.permissions.index', compact('permissions', 'roles'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        $modules = Permission::select('module')
            ->distinct()
            ->pluck('module')
            ->filter()
            ->toArray();
            
        return view('admin.permissions.create', compact('modules'));
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'module' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $slug = Str::slug($request->module . '-' . $request->name, '.');
        
        // Check if permission with slug already exists
        if (Permission::where('slug', $slug)->exists()) {
            return redirect()->back()
                ->withErrors(['name' => 'Permission already exists.'])
                ->withInput();
        }
        
        Permission::create([
            'name' => $request->name,
            'slug' => $slug,
            'module' => $request->module,
            'description' => $request->description,
        ]);
        
        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        $roles = Role::with('permissions')
            ->get()
            ->map(function($role) use ($permission) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'has_permission' => $role->permissions->contains('id', $permission->id)
                ];
            });
            
        return view('admin.permissions.show', compact('permission', 'roles'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        $modules = Permission::select('module')
            ->distinct()
            ->pluck('module')
            ->filter()
            ->toArray();
            
        return view('admin.permissions.edit', compact('permission', 'modules'));
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'module' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $slug = Str::slug($request->module . '-' . $request->name, '.');
        
        // Check if permission with slug already exists (excluding current permission)
        if (Permission::where('slug', $slug)
            ->where('id', '!=', $permission->id)
            ->exists()) {
            return redirect()->back()
                ->withErrors(['name' => 'Permission already exists.'])
                ->withInput();
        }
        
        $permission->update([
            'name' => $request->name,
            'slug' => $slug,
            'module' => $request->module,
            'description' => $request->description,
        ]);
        
        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Permission $permission)
    {
        // Check if permission is in use
        $permissionInUse = DB::table('role_permission')
            ->where('permission_id', $permission->id)
            ->exists();
            
        if ($permissionInUse) {
            return response()->json([
                'success' => false,
                'message' => 'Permission is in use by one or more roles and cannot be deleted.',
            ]);
        }
        
        $permission->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully.'
        ]);
    }
    
    /**
     * Assign or revoke a permission for a role
     */
    public function assignToRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
            'has_permission' => 'required|boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $role = Role::findOrFail($request->role_id);
        $permission = Permission::findOrFail($request->permission_id);
        
        // Don't allow modifying default admin role
        if ($role->name === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify permissions for the admin role.'
            ], 403);
        }
        
        if ($request->has_permission) {
            $role->givePermissionTo($permission);
            $message = 'Permission granted successfully.';
        } else {
            $role->removePermission($permission);
            $message = 'Permission revoked successfully.';
        }
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
    
    /**
     * Get permissions for a specific role
     */
    public function getRolePermissions($roleId)
    {
        $role = Role::with('permissions')->findOrFail($roleId);
        $allPermissions = Permission::all()->groupBy('module');
        
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return response()->json([
            'success' => true,
            'role' => $role,
            'permissions' => $allPermissions,
            'rolePermissions' => $rolePermissions
        ]);
    }
    
    /**
     * Sync permissions for a role
     */
    public function syncRolePermissions(Request $request, $roleId)
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $role = Role::findOrFail($roleId);
        
        // Don't allow modifying default admin role
        if ($role->name === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify permissions for the admin role.'
            ], 403);
        }
        
        $role->permissions()->sync($request->permissions);
        
        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully for ' . $role->name . ' role.'
        ]);
    }
}
