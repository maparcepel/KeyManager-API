<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    /** @test */
    public function testUsuarioPuedeLogarse()
    {
        $body = [
            'username' => 'maparcepel@gmail.com',
            'web_password' => '124'
        ];
        $this->json('POST', '/api/login', $body)
            ->assertStatus('200');
        
    }
}
