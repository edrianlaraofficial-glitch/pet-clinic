@extends('layouts.app')
@section('title', 'My bookings')

@section('content')
    <div class="page-head">
        <div>
            <p class="step-label">Your visits</p>
            <h1>My bookings</h1>
        </div>
        <a href="{{ route('booking.create') }}" class="btn btn-primary">+ Book another visit</a>
    </div>

    <h2>Upcoming</h2>
    @include('appointments._list', ['items' => $upcoming, 'empty' => 'No upcoming visits. Ready to book one?', 'showActions' => true])

    <h2 style="margin-top:2.25rem">Past</h2>
    @include('appointments._list', ['items' => $past, 'empty' => 'Your past visits will show up here.', 'showActions' => false])
@endsection
