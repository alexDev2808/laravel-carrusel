<?php

namespace App\Http\Controllers;

use App\Events\CarouselUpdated;
use App\Models\CarouselImage;
use App\Models\CarouselSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CarouselAdminController extends Controller
{
    public function index(): Response
    {
        $setting = CarouselSetting::current();
        $images = CarouselImage::orderBy('order')->orderBy('id')->get()->map(fn ($img) => [
            'id' => $img->id,
            'url' => $img->url,
            'title' => $img->title,
            'order' => $img->order,
        ]);

        return Inertia::render('carrusel/Admin', [
            'images' => $images,
            'duration' => $setting->duration,
            'quote' => $setting->quote,
            'author' => $setting->author,
        ]);
    }

    public function storeImage(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120'],
            'title' => ['nullable', 'string', 'max:100'],
        ]);

        $path = $request->file('image')->store('carousel', 'public');
        $maxOrder = CarouselImage::max('order') ?? 0;

        CarouselImage::create([
            'path' => $path,
            'title' => $request->title,
            'order' => $maxOrder + 1,
        ]);

        $this->broadcastState();

        return back();
    }

    public function destroyImage(CarouselImage $image): RedirectResponse
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();

        $this->broadcastState();

        return back();
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'duration' => ['required', 'integer', 'min:1', 'max:60'],
            'quote'    => ['nullable', 'string', 'max:500'],
            'author'   => ['nullable', 'string', 'max:100'],
        ]);

        CarouselSetting::current()->update($request->only('duration', 'quote', 'author'));

        $this->broadcastState();

        return back();
    }

    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        foreach ($request->order as $position => $id) {
            CarouselImage::where('id', $id)->update(['order' => $position]);
        }

        $this->broadcastState();

        return back();
    }

    private function broadcastState(): void
    {
        $setting = CarouselSetting::current();
        $images = CarouselImage::orderBy('order')->orderBy('id')->get()->map(fn ($img) => [
            'id' => $img->id,
            'url' => $img->url,
            'title' => $img->title,
        ])->values()->all();

        broadcast(new CarouselUpdated($images, $setting->duration, $setting->quote, $setting->author));
    }
}
