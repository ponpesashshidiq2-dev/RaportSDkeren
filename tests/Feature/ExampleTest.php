<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Tamu (belum login) diarahkan ke halaman login, bukan dashboard.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    /**
     * Halaman login dapat diakses tamu.
     */
    public function test_login_page_is_available_for_guests(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Halaman profil hanya untuk user login.
     */
    public function test_profile_requires_authentication(): void
    {
        $response = $this->get('/profile');

        $response->assertRedirect(route('login'));
    }
}
