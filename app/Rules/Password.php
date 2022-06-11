<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Password implements Rule
{
    protected $username;

    protected $doesNotHaveOneUppercase;
    protected $doesNotHaveOneLowercase;
    protected $doesNotHaveOneNumber;
    protected $hasMoreThanHalfOfUsername;
    /**
     * Create a new rule instance.
     *
     * @param string $username
     */
    public function __construct(string $username)
    {
        $this->username = $username;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (!$this->includeAtLeastOneUppercase($value)) {
            $this->doesNotHaveOneUppercase = true;
        } elseif (!$this->includeAtLeastOneLowercase($value)) {
            $this->doesNotHaveOneLowercase = true;
        } elseif (!$this->includeAtLeastOneNumber($value)) {
            $this->doesNotHaveOneNumber = true;
        } elseif (!$this->containMaximumHalfOfUsername($value)) {
            $this->hasMoreThanHalfOfUsername = true;
        }

        return $this->includeAtLeastOneUppercase($value)
            && $this->includeAtLeastOneLowercase($value)
            && $this->includeAtLeastOneNumber($value)
            && $this->containMaximumHalfOfUsername($value);
    }

    protected function includeAtLeastOneUppercase(string $value): bool
    {
        return (bool) preg_match('/[A-Z]/', $value);
    }

    protected function includeAtLeastOneLowercase(string $value): bool
    {
        return (bool) preg_match('/[a-z]/', $value);
    }

    protected function includeAtLeastOneNumber(string $value): bool
    {
        return (bool) preg_match('/[0-9]/', $value);
    }

    protected function containMaximumHalfOfUsername(string $value): bool
    {
        if (empty($this->username)) {
            return false;
        }

        $length = strlen($this->username);
        $matches = [];

        foreach (str_split($this->username) as $char) {
            if (strpos($value, $char) === false) {
                continue;
            }

            $matches[$char] = true;
        }

        return count($matches) <= floor($length / 2);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $key = '';

        if ($this->doesNotHaveOneLowercase) {
            $key = 'lowercase';
        } elseif ($this->doesNotHaveOneUppercase) {
            $key = 'uppercase';
        } elseif ($this->doesNotHaveOneNumber) {
            $key = 'number';
        } elseif ($this->hasMoreThanHalfOfUsername) {
            $key = 'matches_username';
        }

        $message = trans('validation.custom.password.' . $key);

        return $message === 'validation.custom.password.' ? '' : $message;
    }
}
