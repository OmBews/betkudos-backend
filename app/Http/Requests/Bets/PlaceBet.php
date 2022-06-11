<?php

namespace App\Http\Requests\Bets;

use App\Models\Wallets\Wallet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Class PlaceBet
 * @package App\Http\Requests\Bets
 *
 * @property array multiples
 * @property array singles
 * @property int|float multipleStake
 * @property string|null betUniqueId
 * @property int walletId
 */
class PlaceBet extends FormRequest
{
    private $wallet;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::guard('api')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->wallet = $this->wallet();
        $minStake = $this->wallet->currency->min_bet;
        $maxStake = $this->wallet->currency->max_bet;

        return [
            'betUniqueId' => 'string|nullable',

            'multipleStake' => [
                Rule::requiredIf(is_array($this->multiples) && count($this->multiples)),
                'numeric',
                "min:$minStake",
                "max:$maxStake"
            ],

            'multiples' => 'array',
            'multiples.*.match_id' => "required|int",
            'multiples.*.odd_id' => "required|numeric",
            'multiples.*.market_id' => "required|numeric",
            'multiples.*.odds' => "required|numeric",

            'singles' => 'array',
            'singles.*.stake' => "required|numeric|min:$minStake|max:$maxStake",
            'singles.*.match_id' => "required|int",
            'singles.*.odd_id' => "required|numeric",
            'singles.*.market_id' => "required|numeric",
            'singles.*.odds' => "required|numeric",

            'walletId' => "bail|required|exists:wallets,id"
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            if ($this->hasMultiple()) {
                if ($this->thereAreLessThan2MatchesToPlaceMultiple()) {
                    $validator->errors()->add('matches', trans('bets.multiple.invalid_count'));
                } elseif ($this->thereAreMoreThanASelectionFromTheSameEvent()) {
                    $validator->errors()->add('matches', trans('bets.multiple.duplicated_event'));
                }
            }

            if ($this->userBalanceIsLessThanMinStake()) {
                $validator->errors()->add('balance', trans('user.insufficient_funds'));
            }

            if ($this->theSumOfAllStakesIsGreaterThanUserBalance()) {
                $validator->errors()->add('balance', trans('bets.stake.greater_than_balance'));
            }
        });
    }

    protected function userBalanceIsLessThanMinStake(): bool
    {
        return $this->wallet->balance < $this->wallet->currency->min_bet;
    }

    protected function theSumOfAllStakesIsGreaterThanUserBalance(): bool
    {
        $sumStakes = function ($accumulator, $match) {
            $accumulator += $match['stake'] ?? 0;

            return $accumulator;
        };

        return array_reduce(
            $this->singles ?? [],
            $sumStakes,
            $this->multipleStake ?? 0
        ) > $this->wallet->balance;
    }

    protected function thereAreMoreThanASelectionFromTheSameEvent(): bool
    {
        $ids = array_map(function ($single) {
            if (isset($single['match_id'])) {
                return $single['match_id'];
            }
        }, $this->multiples);

        return count(array_unique(array_diff_assoc($ids, array_unique($ids)))) > 0;
    }

    protected function hasMultiple(): bool
    {
        return isset($this->multiples) && is_array($this->multiples);
    }

    protected function thereAreLessThan2MatchesToPlaceMultiple(): bool
    {
        return $this->hasMultiple() && count($this->multiples) < 2;
    }

    public function wallet()
    {
        return Wallet::query()
            ->where('user_id', $this->user()->getKey())
            ->whereKey($this->walletId)
            ->with('currency')
            ->first();
    }

    public function messages()
    {
        $minStake = $this->wallet->currency->min_bet;
        $maxStake = $this->wallet->currency->max_bet;

        return [
            'singles.*.stake.min' => trans('bets.stake.min', ['stake' => $minStake, 'currency' => $this->wallet->currency->ticker]),
            'singles.*.stake.max' => trans('bets.stake.max', ['stake' => $maxStake, 'currency' => $this->wallet->currency->ticker]),
            'multipleStake.min' => trans('bets.stake.min', ['stake' => $minStake, 'currency' => $this->wallet->currency->ticker]),
            'multipleStake.max' => trans('bets.stake.max', ['stake' => $maxStake, 'currency' => $this->wallet->currency->ticker]),
        ];
    }
}
