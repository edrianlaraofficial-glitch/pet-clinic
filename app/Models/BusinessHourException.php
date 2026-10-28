<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['date', 'is_closed', 'opens_at', 'closes_at', 'reason'])]
class BusinessHourException extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_closed' => 'boolean',
        ];
    }
}