<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterSocialLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_shows_only_configured_social_links(): void
    {
        SiteSetting::create(['key' => 'social_youtube', 'value' => 'https://www.youtube.com/@himashva', 'group' => 'social']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('https://www.youtube.com/@himashva', false);
        $response->assertDontSee('aria-label="LinkedIn"', false);
    }

    public function test_footer_shows_linkedin_link_when_configured(): void
    {
        SiteSetting::create(['key' => 'social_linkedin', 'value' => 'https://www.linkedin.com/company/himashva/', 'group' => 'social']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('https://www.linkedin.com/company/himashva/', false);
    }
}
