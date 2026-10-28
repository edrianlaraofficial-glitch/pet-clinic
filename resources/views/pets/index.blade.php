@extends('layouts.app')
@section('title', 'My dogs')

@section('content')
    <div class="page-head">
        <div>
            <p class="step-label">Your family</p>
            <h1>My dogs</h1>
            <p class="muted">Add each dog once, then pick them when you book.</p>
        </div>
    </div>

    <div class="grid" style="grid-template-columns: minmax(0, 1fr) minmax(280px, 380px); align-items:start">
        <section class="stack">
            @forelse ($pets as $pet)
                <article class="card card-tight row row-between">
                    <div>
                        <h3 style="margin:0">🐾 {{ $pet->name }}</h3>
                        <div class="muted small">
                            {{ $pet->breed ?: 'Breed not set' }}
                            @if ($pet->date_of_birth)
                                · born {{ $pet->date_of_birth->format('M Y') }}
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <a class="btn btn-outline btn-sm" href="{{ route('pets.edit', $pet) }}">Edit</a>
                        <form method="POST" action="{{ route('pets.destroy', $pet) }}" data-confirm="Remove {{ $pet->name }} from your account?">
                            @csrf @method('DELETE')
                            <button class="btn btn-ghost btn-sm" type="submit">Remove</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="card">
                    <h3>No dogs yet</h3>
                    <p class="muted">Add your first dog to the right, then you're ready to book.</p>
                </div>
            @endforelse

            @error('pet') <div class="alert alert-error">{{ $message }}</div> @enderror
        </section>

        <section class="card">
            <h2>Add a dog</h2>
            <form method="POST" action="{{ route('pets.store') }}">
                @csrf
                @include('pets._form', ['pet' => null])
                <button class="btn btn-primary" type="submit">Save dog</button>
            </form>
        </section>
    </div>
@endsection
