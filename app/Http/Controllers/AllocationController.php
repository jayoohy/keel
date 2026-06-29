<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Allocation;
use App\Models\Goal;
use App\Services\Allocation\AllocationEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AllocationController extends Controller
{
    public function store(Request $request, Goal $goal, AllocationEngine $allocationEngine): RedirectResponse
    {
        abort_unless($goal->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        try {
            $allocationEngine->allocate($request->user(), $goal, (float) $validated['amount']);
        } catch (InsufficientBalanceException $e) {
            throw ValidationException::withMessages(['amount' => $e->getMessage()]);
        }

        return back();
    }

    public function destroy(Request $request, Allocation $allocation, AllocationEngine $allocationEngine): RedirectResponse
    {
        abort_unless($allocation->user_id === $request->user()->id, 403);

        $allocationEngine->deallocate($allocation);

        return back();
    }
}
