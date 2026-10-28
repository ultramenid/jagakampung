<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrontendAuthTest extends TestCase
{
    public function test_frontend_requires_basic_auth_when_password_set(): void
    {
        config(['app.frontend_auth' => ['user' => 'u', 'password' => 'p']]);

        $this->get('/wms-feature-info')->assertStatus(401)->assertHeader('WWW-Authenticate');
        $this->withHeaders(['Authorization' => 'Basic ' . base64_encode('u:wrong')])
            ->get('/wms-feature-info')->assertStatus(401);
        $this->assertNotEquals(401, $this->withHeaders(['Authorization' => 'Basic ' . base64_encode('u:p')])
            ->get('/wms-feature-info')->status());
    }

    public function test_frontend_open_when_password_empty(): void
    {
        config(['app.frontend_auth' => ['user' => 'u', 'password' => null]]);

        $this->assertNotEquals(401, $this->get('/wms-feature-info')->status());
    }
}
