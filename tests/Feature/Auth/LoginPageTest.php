<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class LoginPageTest extends TestCase
{
    public function test_login_page_uses_the_new_guest_layout(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('assets/css/theme.css', false)
            ->assertSee('autocomplete="current-password"', false)
            ->assertSee('type="email"', false)
            ->assertDontSee('assets/vendor/echarts', false);
    }

    public function test_flash_message_is_shown(): void
    {
        $this->withSession(['msg' => 'Pesan uji'])->get('/login')->assertSee('Pesan uji');
    }
}
