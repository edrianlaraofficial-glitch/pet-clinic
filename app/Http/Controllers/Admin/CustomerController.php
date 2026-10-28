<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Contracts\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = User::query()
            ->select(['id', 'name', 'email', 'created_at'])
            ->whereDoesntHave('roles', fn ($query) => $query->where('role', UserRole::ADMIN))
            ->with('profile:id,user_id,full_name,phone')
            ->withCount('appointments')
            ->latest()
            ->paginate(20);

        return view('admin.customers.index', [
            'customers' => $customers,
        ]);
    }
}
