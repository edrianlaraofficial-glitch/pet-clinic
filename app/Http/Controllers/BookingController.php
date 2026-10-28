<?php

namespace App\Http\Controllers;

use App\Models\BusinessHour;
use App\Models\Service;
use App\Services\AppointmentBooker;
use App\Services\AvailabilityService;
use App\Services\SlotUnavailableException;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Request $request, AvailabilityService $availability): View
    {
        $user = $request->user();
        $validated = $request->validate([
            'service_id' => [
                'nullable',
                'integer',
                Rule::exists('services', 'id')->where('is_active', true),
            ],
            'pet_id' => [
                'nullable',
                'integer',
                Rule::exists('pets', 'id')->where('user_id', $user->getKey()),
            ],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $service = isset($validated['service_id'])
            ? Service::query()->where('is_active', true)->find($validated['service_id'])
            : null;
        $pet = isset($validated['pet_id']) ? $user->pets()->find($validated['pet_id']) : null;
        $date = isset($validated['date'])
            ? CarbonImmutable::createFromFormat('!Y-m-d', $validated['date'], config('app.timezone'))
            : null;
        $slots = collect();
        $dayClosed = false;
        $dayHours = null;

        if ($service && $pet && $date) {
            $dayClosed = ! $availability->isDayOpen($date);
            $dayHours = $availability->getDayHours($date);

            if (! $dayClosed) {
                $slots = $availability->slotsFor($service, $date);
            }
        }

        return view('booking.create', [
            'services' => Service::query()->where('is_active', true)->orderBy('name')->get(),
            'pets' => $user->pets()->orderBy('name')->get(),
            'service' => $service,
            'pet' => $pet,
            'date' => $date,
            'today' => now(config('app.timezone'))->toDateString(),
            'dayClosed' => $dayClosed,
            'dayHours' => $dayHours,
            'slots' => $slots,
        ]);
    }

    public function store(Request $request, AppointmentBooker $booker): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')->where('is_active', true),
            ],
            'pet_id' => [
                'required',
                'integer',
                Rule::exists('pets', 'id')->where('user_id', $user->getKey()),
            ],
            'starts_at' => ['required', 'date_format:Y-m-d H:i'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $service = Service::query()->findOrFail($validated['service_id']);
        $pet = $user->pets()->findOrFail($validated['pet_id']);
        $startsAt = CarbonImmutable::createFromFormat(
            '!Y-m-d H:i',
            $validated['starts_at'],
            config('app.timezone'),
        );

        try {
            $booker->book($user, $pet, $service, $startsAt, $validated['customer_notes'] ?? null);
        } catch (SlotUnavailableException $exception) {
            return back()->withErrors(['starts_at' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('my-bookings')->with('status', 'Your appointment request was submitted.');
    }
}