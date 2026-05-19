<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Strona główna kieruje do dashboardu właściwego dla roli.
     */
    public function index(Request $request): RedirectResponse
    {
        $user = $request->user();

        return match (true) {
            $user->isAdmin()   => redirect()->route('admin.dashboard'),
            $user->isManager() => redirect()->route('manager.dashboard'),
            default            => redirect()->route('driver.dashboard'),
        };
    }
}
