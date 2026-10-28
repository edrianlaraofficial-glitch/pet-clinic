<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['weekday', 'opens_at', 'closes_at', 'is_closed'])]
class BusinessHour extends Model
{
    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
            'is_closed' => 'boolean',
        ];
    }

    public function dayName(): string
    {
        return Carbon::now()
            ->startOfWeek(Carbon::SUNDAY)
            ->addDays($this->weekday)
            ->format('l');
    }
}
