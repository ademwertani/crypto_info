<?php

namespace Tests\Feature;

use App\Mail\AdvertiserInquiryAck;
use App\Mail\AdvertiserInquiryReceived;
use App\Models\AdFormat;
use App\Models\AdvertiserLead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdvertiseTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jane Advertiser',
            'email' => 'jane@example.com',
            'company' => 'Acme Corp',
            'budget_range' => AdvertiserLead::BUDGET_RANGES[1],
            'message' => 'We would like to sponsor an article about our new exchange product.',
        ], $overrides);
    }

    public function test_show_page_lists_ad_formats_and_never_exposes_the_contact_email(): void
    {
        config(['services.advertise.contact_email' => 'contact@cryptoinfo-test.com']);

        AdFormat::create([
            'slug' => 'sponsored-articles',
            'name' => 'Sponsored Articles',
            'description' => 'A dedicated article about your project.',
            'specs' => ['Up to 1,000 words', 'Includes 2 links'],
            'price_range' => 'Contact us for current rates',
            'sort_order' => 1,
            'status' => 'published',
        ]);

        $response = $this->get('/advertise');

        $response->assertOk();
        $response->assertSee('Sponsored Articles');
        $response->assertSee('Contact us for current rates');
        $response->assertDontSee('contact@cryptoinfo-test.com');
    }

    public function test_valid_submission_creates_a_lead_and_sends_two_emails(): void
    {
        config(['services.advertise.contact_email' => 'contact@cryptoinfo-test.com']);
        Mail::fake();

        $response = $this->post('/advertise', $this->validPayload());

        $response->assertRedirect('/advertise');
        $response->assertSessionHas('advertise_sent', true);

        $this->assertDatabaseHas('advertiser_leads', [
            'email' => 'jane@example.com',
            'status' => 'new',
        ]);

        Mail::assertQueued(AdvertiserInquiryReceived::class, function (AdvertiserInquiryReceived $mail) {
            return $mail->hasTo('contact@cryptoinfo-test.com');
        });

        Mail::assertQueued(AdvertiserInquiryAck::class, function (AdvertiserInquiryAck $mail) {
            return $mail->hasTo('jane@example.com');
        });
    }

    public function test_honeypot_submission_is_silently_dropped(): void
    {
        Mail::fake();

        $response = $this->post('/advertise', $this->validPayload([
            'hp_website' => 'https://spambot.example.com',
        ]));

        $response->assertRedirect('/advertise');
        $this->assertDatabaseCount('advertiser_leads', 0);
        Mail::assertNothingQueued();
        Mail::assertNothingSent();
    }

    public function test_missing_required_fields_fail_validation_and_create_no_lead(): void
    {
        $response = $this->post('/advertise', $this->validPayload([
            'name' => '',
            'email' => '',
            'message' => '',
        ]));

        $response->assertSessionHasErrors(['name', 'email', 'message']);
        $this->assertDatabaseCount('advertiser_leads', 0);
    }

    public function test_repeated_submissions_are_rate_limited(): void
    {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/advertise', $this->validPayload(['email' => "lead{$i}@example.com"]));
            $response->assertStatus(302);
        }

        $sixth = $this->post('/advertise', $this->validPayload(['email' => 'lead6@example.com']));
        $sixth->assertStatus(429);
    }

    public function test_guests_are_redirected_away_from_the_admin_panel(): void
    {
        $this->get('/admin/ad-formats')->assertRedirect('/admin/login');
        $this->get('/admin/advertiser-leads')->assertRedirect('/admin/login');
    }

    public function test_authenticated_user_can_view_admin_lists_including_submitted_leads(): void
    {
        $user = User::factory()->create();

        AdvertiserLead::create([
            'name' => 'Jane Advertiser',
            'email' => 'jane@example.com',
            'message' => 'Interested in banner ads.',
            'status' => 'new',
        ]);

        $this->actingAs($user)
            ->get('/admin/advertiser-leads')
            ->assertOk()
            ->assertSee('Jane Advertiser');

        $this->actingAs($user)
            ->get('/admin/ad-formats')
            ->assertOk();
    }
}
