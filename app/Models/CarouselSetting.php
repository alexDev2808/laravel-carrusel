<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['duration', 'quote', 'author'])]
class CarouselSetting extends Model
{
    public static function current(): self
    {
        return self::firstOrCreate(['id' => 1], ['duration' => 5]);
    }
}
