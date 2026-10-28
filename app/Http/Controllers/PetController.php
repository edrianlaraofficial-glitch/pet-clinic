<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetController extends Controller
{
    public function index(Request $request): View
    {
        return view('pets.index', [
            'pets' => $request->user()->pets()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'breed' => ['nullable', 'string', 'max:80'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->pets()->create($validated);

        return redirect()->route('pets.index')->with('status', 'Your dog was added.');
    }

    public function edit(Request $request, Pet $pet): View
    {
        $this->ensureOwnedBy($request, $pet);

        return view('pets.edit', ['pet' => $pet]);
    }

    public function update(Request $request, Pet $pet): RedirectResponse
    {
        $this->ensureOwnedBy($request, $pet);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'breed' => ['nullable', 'string', 'max:80'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $pet->update($validated);

        return redirect()->route('pets.index')->with('status', 'Your dog was updated.');
    }

    public function destroy(Request $request, Pet $pet): RedirectResponse
    {
        $this->ensureOwnedBy($request, $pet);

        if ($pet->appointments()->exists()) {
            return back()->withErrors(['pet' => 'Dogs with appointment history cannot be removed.']);
        }

        $pet->delete();

        return redirect()->route('pets.index')->with('status', 'Your dog was removed.');
    }

    private function ensureOwnedBy(Request $request, Pet $pet): void
    {
        abort_unless((int) $pet->user_id === (int) $request->user()->getKey(), 404);
    }
}
