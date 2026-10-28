<?php

namespace App\Http\Controllers;

use App\Models\BusinessHour;
use App\Models\GalleryImage;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $hours = BusinessHour::query()
            ->orderBy('weekday')
            ->get();
        $timezone = config('app.timezone');
        $now = CarbonImmutable::now($timezone);
        $todayHours = $hours->firstWhere('weekday', $now->dayOfWeek);
        $todayOpensAt = null;
        $todayClosesAt = null;

        if ($todayHours && ! $todayHours->is_closed && $todayHours->opens_at && $todayHours->closes_at) {
            $todayOpensAt = $now->startOfDay()->setTimeFromTimeString($todayHours->opens_at);
            $todayClosesAt = $now->startOfDay()->setTimeFromTimeString($todayHours->closes_at);

            if (! $todayClosesAt->greaterThan($todayOpensAt)) {
                $todayOpensAt = null;
                $todayClosesAt = null;
            }
        }

        $clinicIsOpen = $todayOpensAt
            && $todayClosesAt
            && $now->greaterThanOrEqualTo($todayOpensAt)
            && $now->lessThan($todayClosesAt);
        $nextOpeningAt = null;

        if (! $todayOpensAt || $now->greaterThanOrEqualTo($todayClosesAt)) {
            for ($daysAhead = 1; $daysAhead <= 7; $daysAhead++) {
                $candidateDate = $now->startOfDay()->addDays($daysAhead);
                $candidateHours = $hours->firstWhere('weekday', $candidateDate->dayOfWeek);

                if ($candidateHours && ! $candidateHours->is_closed && $candidateHours->opens_at && $candidateHours->closes_at) {
                    $candidateOpening = $candidateDate->setTimeFromTimeString($candidateHours->opens_at);
                    $candidateClosing = $candidateDate->setTimeFromTimeString($candidateHours->closes_at);

                    if ($candidateClosing->greaterThan($candidateOpening)) {
                        $nextOpeningAt = $candidateOpening;

                        break;
                    }
                }
            }
        }

        if ($clinicIsOpen) {
            $clinicStatus = 'Open now';
            $clinicStatusDetails = 'Today until '.$todayClosesAt->format('g:i A');
        } elseif ($todayOpensAt && $now->lessThan($todayOpensAt)) {
            $clinicStatus = 'Opens today';
            $clinicStatusDetails = 'Today at '.$todayOpensAt->format('g:i A');
        } elseif (! $todayHours) {
            $clinicStatus = 'Hours not posted';
            $clinicStatusDetails = $nextOpeningAt
                ? 'Next opening '.$nextOpeningAt->format('l \a\t g:i A')
                : 'Please check the schedule below.';
        } else {
            $clinicStatus = 'Closed today';
            $clinicStatusDetails = $nextOpeningAt
                ? 'Opens '.$nextOpeningAt->format('l \a\t g:i A')
                : 'Please check the schedule below.';
        }

        return view('home', [
            'services' => Service::query()
                ->where('is_active', true)
                ->orderByRaw(
                    'CASE LOWER(name) WHEN ? THEN 0 WHEN ? THEN 1 WHEN ? THEN 2 WHEN ? THEN 3 WHEN ? THEN 4 ELSE 5 END',
                    ['general checkup', 'vaccination', 'grooming', 'dental cleaning', 'spay / neuter consultation'],
                )
                ->orderBy('name')
                ->get(),
            'hours' => $hours,
            'todayWeekday' => $now->dayOfWeek,
            'clinicIsOpen' => $clinicIsOpen,
            'clinicStatus' => $clinicStatus,
            'clinicStatusDetails' => $clinicStatusDetails,
            'galleryImages' => GalleryImage::query()
                ->latest()
                ->get(),
        ]);
    }
}
