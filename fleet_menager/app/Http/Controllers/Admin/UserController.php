<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()->orderBy('name')->paginate(25);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorize('manage', User::class);

        return view('admin.users.create', ['roles' => UserRole::cases()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage', User::class);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', Rule::in(UserRole::values())],
        ]);

        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Użytkownik został dodany.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('manage', User::class);

        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'Nie możesz usunąć własnego konta.']);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Użytkownik został usunięty.');
    }
}
