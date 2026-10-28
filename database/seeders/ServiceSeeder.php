<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * Placeholder services. Edit names, prices and durations later from
 * /admin/services, or change them here before seeding.
 */
class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'General Checkup',
                'description' => 'A full health exam with weight check, heart and lung listen, and a chat about diet and behaviour.',
                'duration_minutes' => 30,
                'price' => 800,
            ],
            [
                'name' => 'Vaccination',
                'description' => 'Core vaccines for puppies and adult dogs, with a vaccination card updated on the spot.',
                'duration_minutes' => 20,
                'price' => 1200,
            ],
            [
                'name' => 'Grooming',
                'description' => 'Bath, blow-dry, brush-out, nail trim, and ear cleaning. Breed-specific cuts on request.',
                'duration_minutes' => 90,
                'price' => 1500,
            ],
            [
                'name' => 'Dental Cleaning',
                'description' => 'Scale and polish under light sedation, with a check of teeth and gums.',
                'duration_minutes' => 60,
                'price' => 3500,
            ],
            [
                'name' => 'Spay / Neuter Consultation',
                'description' => 'Pre-surgery consultation to plan the procedure, pain relief, and recovery at home.',
                'duration_minutes' => 30,
                'price' => 500,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['name' => $service['name']], $service + ['is_active' => true]);
        }
    }
}
