<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>When</th>
                <th>Dog</th>
                <th>Service</th>
                <th>Status</th>
                @if ($showActions) <th></th> @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $appointment)
                <tr>
                    <td>
                        <strong>{{ $appointment->starts_at->format('D, M j') }}</strong><br>
                        <span class="muted small">{{ $appointment->starts_at->format('g:i A') }} – {{ $appointment->ends_at->format('g:i A') }}</span>
                    </td>
                    <td>🐾 {{ $appointment->pet->name }}</td>
                    <td>{{ $appointment->service->name }}</td>
                    <td><span class="badge badge-{{ $appointment->status }}">{{ $appointment->statusLabel() }}</span></td>
                    @if ($showActions)
                        <td>
                            @if ($appointment->isCancellable())
                                <div class="row">
                                    <a class="btn btn-outline btn-sm" href="{{ route('appointments.reschedule', $appointment) }}">Reschedule</a>
                                    <form method="POST" action="{{ route('appointments.cancel', $appointment) }}" data-confirm="Cancel this appointment?">
                                        @csrf
                                        <button class="btn btn-ghost btn-sm" type="submit">Cancel</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="5" class="muted">{{ $empty }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
