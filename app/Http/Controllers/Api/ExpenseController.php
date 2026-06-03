<?php

namespace App\Http\Controllers\Api;

use App\Models\AuditLog;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ExpenseController
{
    public function index(Request $request)
    {
        $query = Expense::with(['user', 'company'])
            ->where('company_id', $request->user()->company_id);

        // Search by title or category
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        $cacheKey = 'expenses_company_' . $request->user()->company_id . '_page_' . ($request->input('page', 1));
        
        $expenses = Cache::remember($cacheKey, 3600, function() use ($query) {
            return $query->paginate(15);
        });

        return response()->json($expenses);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:100',
        ]);

        $expense = Expense::create([
            'company_id' => $request->user()->company_id,
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'category' => $validated['category'],
        ]);

        // Log audit
        AuditLog::create([
            'user_id' => $request->user()->id,
            'company_id' => $request->user()->company_id,
            'expense_id' => $expense->id,
            'action' => 'created',
            'changes' => $expense->toArray(),
        ]);

        Cache::forget('expenses_company_' . $request->user()->company_id . '_*');

        return response()->json($expense, 201);
    }

    public function show(Request $request, Expense $expense)
    {
        if ($expense->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($expense->load(['user', 'company']));
    }

    public function update(Request $request, Expense $expense)
    {
        if ($expense->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!in_array($request->user()->role, ['Manager', 'Admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0.01',
            'category' => 'sometimes|string|max:100',
        ]);

        $oldValues = $expense->toArray();
        $expense->update($validated);

        // Log audit with changes
        AuditLog::create([
            'user_id' => $request->user()->id,
            'company_id' => $request->user()->company_id,
            'expense_id' => $expense->id,
            'action' => 'updated',
            'changes' => [
                'old' => $oldValues,
                'new' => $expense->toArray(),
            ],
        ]);

        Cache::forget('expenses_company_' . $request->user()->company_id . '_*');

        return response()->json($expense);
    }

    public function destroy(Request $request, Expense $expense)
    {
        if ($expense->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $expenseData = $expense->toArray();

        $expense->delete();

        // Log audit
        AuditLog::create([
            'user_id' => $request->user()->id,
            'company_id' => $request->user()->company_id,
            'expense_id' => $expense->id,
            'action' => 'deleted',
            'changes' => $expenseData,
        ]);

        Cache::forget('expenses_company_' . $request->user()->company_id . '_*');

        return response()->json(['message' => 'Expense deleted successfully']);
    }
}
