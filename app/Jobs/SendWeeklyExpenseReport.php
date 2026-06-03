<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWeeklyExpenseReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $admins = User::where('company_id', $company->id)
                ->where('role', 'Admin')
                ->get();

            // Get expenses from the last 7 days
            $expenses = Expense::where('company_id', $company->id)
                ->whereBetween('created_at', [now()->subDays(7), now()])
                ->get();

            $totalAmount = $expenses->sum('amount');
            $expenseCount = $expenses->count();

            $emailBody = "
            <h2>Weekly Expense Report for {$company->name}</h2>
            <p>Period: Last 7 days</p>
            <p>Total Expenses: {$expenseCount}</p>
            <p>Total Amount: \${$totalAmount}</p>
            <h3>Expenses by Category:</h3>
            ";

            $categorySummary = $expenses->groupBy('category')->map(function($items) {
                return [
                    'count' => $items->count(),
                    'total' => $items->sum('amount'),
                ];
            });

            foreach ($categorySummary as $category => $data) {
                $emailBody .= "<p>{$category}: {$data['count']} expenses, \${$data['total']}</p>";
            }

            foreach ($admins as $admin) {
                // In a real application, you would send an email here
                // For now, we'll just log it
                \Log::info("Weekly expense report sent to {$admin->email} for company {$company->name}");
            }
        }
    }
}
