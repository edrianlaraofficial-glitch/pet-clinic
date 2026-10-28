<?php

namespace Database\Seeders;

use App\Models\BusinessHour;
use Illuminate\Database\Seeder;

/**
 * Monday to Saturday, 9:00 to 18:00. Sunday closed.
 * weekday: 0 = Sunday ... 6 = Saturday.
 */
class BusinessHourSeeder extends Seeder
{
    public function run(): void
    {
        for ($weekday = 0; $weekday <= 6; $weekday++) {
            $closed = $weekday === 0;

            BusinessHour::updateOrCreate(
                ['weekday' => $weekday],
                [
                    'is_closed' => $closed,
                    'opens_at' => $closed ? null : '09:00:00',
                    'closes_at' => $closed ? null : '18:00:00',
                ],
            );
        }
    }
}
