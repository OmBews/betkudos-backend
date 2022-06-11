<?php

namespace Tests\Unit\Rules;

use App\Rules\Password;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    protected function build(string $username)
    {
        return new Password($username);
    }

    public function testCanValidateAPassword()
    {
        $rule = $this->build('johndoe');
        $this->assertTrue($rule->passes('password', 'Passwor3Secure'));
        $this->assertEmpty($rule->message());

        $rule = $this->build('johndoe');
        $this->assertFalse($rule->passes('password', 'secret'));
        $this->assertEquals(trans('validation.custom.password.uppercase'), $rule->message());

        $rule = $this->build('johndoe');
        $this->assertFalse($rule->passes('password', 'Secret'));
        $this->assertEquals(trans('validation.custom.password.number'), $rule->message());

        $rule = $this->build('johndoe');
        $this->assertFalse($rule->passes('password', 'Johndoepasswor3'));
        $this->assertEquals(trans('validation.custom.password.matches_username'), $rule->message());
    }
}
