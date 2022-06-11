<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Intervention\Image\ImageManagerStatic;

class Base64Image implements Rule
{
    private $height;

    private $width;

    /**
     * Create a new rule instance.
     *
     * @param int $height
     * @param int $width
     */
    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            $image = ImageManagerStatic::make($value);

            $sameHeight = $image->height() === $this->height;
            $sameWidth = $image->width() === $this->width;

            if ($sameHeight && $sameWidth) {
                return true;
            }

            return false;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.base64_image', [
            'dimensions' => "{$this->width}x{$this->height}"
        ]);
    }
}
