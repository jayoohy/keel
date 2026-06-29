<?php

namespace App\Http\Controllers;

use App\Models\Insight;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InsightController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('insights/index', [
            'insights' => $request->user()->insights()
                ->whereNull('dismissed_at')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function update(Request $request, Insight $insight): RedirectResponse
    {
        abort_unless($insight->user_id === $request->user()->id, 403);

        $insight->update(['is_read' => true]);

        return back();
    }

    public function destroy(Request $request, Insight $insight): RedirectResponse
    {
        abort_unless($insight->user_id === $request->user()->id, 403);

        $insight->update(['dismissed_at' => now()]);

        return back();
    }
}
