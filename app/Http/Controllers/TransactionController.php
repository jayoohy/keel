<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Services\Transactions\TransactionCategorizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'account_id' => ['nullable', 'integer'],
            'category_id' => ['nullable', 'integer'],
            'type' => ['nullable', 'string'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'amount_min' => ['nullable', 'numeric'],
            'amount_max' => ['nullable', 'numeric'],
        ]);

        $transactions = $request->user()->transactions()
            ->with(['account', 'category'])
            ->when($filters['account_id'] ?? null, fn ($query, $value) => $query->where('account_id', $value))
            ->when($filters['category_id'] ?? null, fn ($query, $value) => $query->where('category_id', $value))
            ->when($filters['type'] ?? null, fn ($query, $value) => $query->where('type', $value))
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('transacted_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('transacted_at', '<=', $value))
            ->when($filters['amount_min'] ?? null, fn ($query, $value) => $query->where('amount', '>=', $value))
            ->when($filters['amount_max'] ?? null, fn ($query, $value) => $query->where('amount', '<=', $value))
            ->latest('transacted_at')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('transactions/index', [
            'transactions' => $transactions,
            'accounts' => $request->user()->accounts()->get(['id', 'name']),
            'categories' => Category::where('is_default', true)->orWhere('user_id', $request->user()->id)->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function update(Request $request, Transaction $transaction, TransactionCategorizer $categorizer): RedirectResponse
    {
        abort_unless($transaction->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ]);

        $category = Category::findOrFail($validated['category_id']);

        $transaction->update(['category_id' => $category->id]);
        $categorizer->remember($transaction, $category);

        return back();
    }
}
