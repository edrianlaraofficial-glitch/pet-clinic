@extends('layouts.admin')
@section('title', 'Customers')

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="step-label">Clinic community</p>
            <h1>Customers</h1>
            <p class="muted">Customer contact details are visible to verified admins only.</p>
        </div>
        <span class="admin-count-pill">{{ $customers->total() }} {{ \Illuminate\Support\Str::plural('customer', $customers->total()) }}</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel-heading">
            <div>
                <p class="step-label">Customer directory</p>
                <h2>Pet parents</h2>
            </div>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Appointments</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td><strong>{{ $customer->profile?->full_name ?? $customer->name }}</strong></td>
                            <td>
                                <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                                @if ($customer->profile?->phone)
                                    <br><span class="muted small">{{ $customer->profile->phone }}</span>
                                @endif
                            </td>
                            <td>{{ $customer->appointments_count }}</td>
                            <td>{{ $customer->created_at->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="muted">No customers have registered yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">{{ $customers->links() }}</div>
    </section>
@endsection
