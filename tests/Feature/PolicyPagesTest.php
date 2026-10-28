<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_pages_are_public_and_marked_for_clinic_review(): void
    {
        $pages = [
            ['policies.privacy', 'Privacy Policy'],
            ['policies.cookies', 'Cookie Policy'],
            ['policies.terms', 'Terms and Conditions'],
            ['policies.refunds', 'Refund and Cancellation Policy'],
            ['policies.veterinary', 'Veterinary and Emergency Disclaimer'],
        ];

        foreach ($pages as [$route, $title]) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee($title)
                ->assertSee('Clinic review required')
                ->assertSee('Effective date:')
                ->assertSee('[To be approved]');
        }
    }

    public function test_site_footer_and_registration_surface_policy_links(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('policies.privacy'), false)
            ->assertSee(route('policies.cookies'), false)
            ->assertSee(route('policies.terms'), false)
            ->assertSee(route('policies.refunds'), false)
            ->assertSee(route('policies.veterinary'), false);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Before joining, read our')
            ->assertSee(route('policies.privacy'), false)
            ->assertSee(route('policies.terms'), false);
    }
}
