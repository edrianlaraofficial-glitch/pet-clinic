@extends('layouts.app')
@section('title', 'Edit '.$pet->name)

@section('content')
    <div class="page-head">
        <div>
            <p class="step-label">My dogs</p>
            <h1>{{ $pet->name }}</h1>
        </div>
        <a href="{{ route('pets.index') }}" class="btn btn-ghost">← Back to my dogs</a>
    </div>

    <section class="card" style="max-width:720px">
        <form method="POST" action="{{ route('pets.update', $pet) }}">
            @csrf @method('PUT')
            @include('pets._form', ['pet' => $pet])
            <button class="btn btn-primary" type="submit">Save changes</button>
        </form>
    </section>
@endsection
