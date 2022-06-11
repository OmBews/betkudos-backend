<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // now re-register all the roles and permissions
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();

        $this->artisan('passport:keys');
    }
}
