<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;

class GalleryImageController extends Controller
{
    public function index(): View
    {
        return view('admin.gallery', [
            'galleryImages' => GalleryImage::query()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'alt_text' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
        ]);

        $path = $validated['image']->storePublicly('gallery', 'public');

        if (! $path) {
            throw new RuntimeException('The gallery image could not be stored.');
        }

        GalleryImage::query()->create([
            'path' => $path,
            'alt_text' => $validated['alt_text'],
            'caption' => $validated['caption'] ?? null,
        ]);

        return to_route('admin.gallery.index')->with('status', 'The image was added to the public gallery.');
    }

    public function destroy(GalleryImage $galleryImage): RedirectResponse
    {
        if (! Storage::disk('public')->delete($galleryImage->path)) {
            throw new RuntimeException('The gallery image could not be removed from storage.');
        }

        $galleryImage->delete();

        return to_route('admin.gallery.index')->with('status', 'The image was removed from the public gallery.');
    }
}
