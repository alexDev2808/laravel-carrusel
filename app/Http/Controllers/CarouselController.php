<?php

namespace App\Http\Controllers;

use App\Models\CarouselImage;
use App\Models\CarouselSetting;
use Inertia\Inertia;
use Inertia\Response;

class CarouselController extends Controller
{
    public function index(): Response
    {
        $setting = CarouselSetting::current();
        $images = CarouselImage::orderBy('order')->orderBy('id')->get()->map(fn ($img) => [
            'id' => $img->id,
            'url' => $img->url,
            'title' => $img->title,
        ]);

        return Inertia::render('Carrusel', [
            'images' => $images,
            'duration' => $setting->duration,
            'quote' => $setting->quote,
            'author' => $setting->author,
        ]);
    }
}
