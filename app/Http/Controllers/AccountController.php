<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $accounts = $request->user()->accounts()->with('bankConnection')->get();

        return Inertia::render('accounts/index', [
            'accounts' => $accounts,
            'totalBalance' => $accounts->sum('balance'),
            'accountCount' => $accounts->count(),
        ]);
    }
}
