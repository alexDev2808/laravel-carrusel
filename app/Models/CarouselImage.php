<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['path', 'title', 'order'])]
class CarouselImage extends Model
{
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    protected function casts(): array
    {
        return ['order' => 'integer'];
    }
}
