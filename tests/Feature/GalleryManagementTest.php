<?php

namespace Tests\Feature;

use App\Models\GalleryImage;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_can_access_the_gallery_manager(): void
    {
        $this->get(route('admin.gallery.index'))->assertRedirect(route('admin.login'));

        $customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@example.test',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($customer)
            ->get(route('admin.gallery.index'))
            ->assertRedirect(route('admin.login'));

        $this->actingAs($customer)
            ->post(route('admin.gallery.store'), [
                'image' => UploadedFile::fake()->image('dog.jpg'),
                'alt_text' => 'A happy dog',
            ])
            ->assertRedirect(route('admin.login'));

        $this->assertDatabaseCount('gallery_images', 0);
    }

    public function test_admin_uploads_gallery_image_that_is_shown_on_homepage(): void
    {
        Storage::fake('public');
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $admin->grantRole(UserRole::ADMIN);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.gallery.store'), [
                'image' => UploadedFile::fake()->image('dog.jpg'),
                'alt_text' => 'A happy dog at the clinic',
                'caption' => 'A lovely clinic visit',
            ])
            ->assertRedirect(route('admin.gallery.index'));

        $galleryImage = GalleryImage::query()->sole();
        Storage::disk('public')->assertExists($galleryImage->path);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('A happy dog at the clinic')
            ->assertSee('A lovely clinic visit')
            ->assertSee(Storage::disk('public')->url($galleryImage->path), false)
            ->assertSee('data-gallery-carousel', false)
            ->assertSee('aria-label="Clinic photo gallery"', false)
            ->assertSee('data-gallery-counter', false)
            ->assertSee('data-gallery-progress', false)
            ->assertSee('data-gallery-previous', false)
            ->assertSee('data-gallery-next', false)
            ->assertDontSee('Photos of our clinic and its lovely visitors will be here soon.');
    }

    public function test_admin_can_remove_gallery_image_and_file(): void
    {
        Storage::fake('public');
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $admin->grantRole(UserRole::ADMIN);
        Storage::disk('public')->put('gallery/dog.jpg', 'test-image');
        $galleryImage = GalleryImage::query()->create([
            'path' => 'gallery/dog.jpg',
            'alt_text' => 'A happy dog',
            'caption' => null,
        ]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.gallery.destroy', $galleryImage))
            ->assertRedirect(route('admin.gallery.index'));

        $this->assertDatabaseMissing('gallery_images', ['id' => $galleryImage->id]);
        Storage::disk('public')->assertMissing('gallery/dog.jpg');
    }

    public function test_admin_gallery_rejects_non_image_uploads(): void
    {
        Storage::fake('public');
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $admin->grantRole(UserRole::ADMIN);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.gallery.index'))
            ->post(route('admin.gallery.store'), [
                'image' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
                'alt_text' => 'A document',
            ])
            ->assertRedirect(route('admin.gallery.index'))
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('gallery_images', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('gallery'));
    }
}
