<?php

namespace App\Http\Controllers\Api;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController
{
    public function index(Request $request)
    {
        // Only admins can list users
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::where('company_id', $request->user()->company_id)
            ->paginate(15);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        // Only admins can create users
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:Admin,Manager,Employee',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'company_id' => $request->user()->company_id,
            'role' => $validated['role'],
        ]);

        // Log audit
        AuditLog::create([
            'user_id' => $request->user()->id,
            'company_id' => $request->user()->company_id,
            'action' => 'user_created',
            'changes' => $user->toArray(),
        ]);

        return response()->json($user, 201);
    }

    public function show(Request $request, User $user)
    {
        if ($user->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$request->user()->isAdmin() && $request->user()->id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        if ($user->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only admins can update user roles
        if ($request->has('role') && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|in:Admin,Manager,Employee',
        ]);

        $oldValues = $user->toArray();
        $user->update($validated);

        // Log audit
        AuditLog::create([
            'user_id' => $request->user()->id,
            'company_id' => $request->user()->company_id,
            'action' => 'user_updated',
            'changes' => [
                'old' => $oldValues,
                'new' => $user->toArray(),
            ],
        ]);

        return response()->json($user);
    }
}
