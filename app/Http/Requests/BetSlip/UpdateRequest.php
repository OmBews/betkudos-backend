<?php

namespace App\Http\Requests\BetSlip;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'selections' => 'required|array',
            'selections.*.id' => 'required|integer|exists:App\Models\Markets\MarketOdd,id',
            'selections.*.match_id' => 'required|integer|exists:App\Models\Events\Event,id',
            'selections.*.odds' => 'required|numeric',
        ];
    }
}
