<?php

namespace App\Models\Users;

use App\Models\Bets\Bet;
use App\Models\Casino\Games\CasinoBet;
use App\Models\Currencies\CryptoCurrency;
use App\Models\kyc\Document;
use App\Models\kyc\UserKyc;
use App\Models\Sessions\Session;
use App\Models\Users\Traits\Mutators;
use App\Models\Users\Traits\Relationships;
use App\Models\Wallets\Wallet;
use App\Notifications\Auth\ResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Agent\Agent;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 * @package App\Models\Users
 *
 * @property int|float balance
 * @property Wallet|null wallet
 * @property Collection|Bet[] bets
 * @method static Builder withTotalStaked(CryptoCurrency $currency, array $statuses)
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasRoles;
    use Notifiable;
    use Mutators;
    use Relationships;

    public const STATUS_ACTIVE = 'Active';
    public const STATUS_RESTRICTED = 'Restricted';

    public const PER_PAGE = 30;

    public Wallet|Model $wallet;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password',
        'ip_address'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'ip_address',
        'restricted', 'updated_at', 'created_at',
        'google2fa_secret', 'notes', 'email_verified_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'google2fa_enabled' => 'boolean'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function bet(){
        return $this->belongsTo(CasinoBet::class, 'player_id', 'id');
    }

    public function markEmailAsUnverified(): bool
    {
        $this->email_verified_at = null;

        return $this->save();
    }

    public function enable2FA(): bool
    {
        $this->google2fa_enabled = true;

        return $this->save();
    }

    public function disable2FA(): bool
    {
        $this->google2fa_enabled = false;

        return $this->save();
    }

    public function is2FAEnabled(): bool
    {
        return $this->google2fa_enabled;
    }

    public function setStatus(bool $restrict)
    {
        $this->setAttribute('restricted', $restrict);
    }

    public function status()
    {
        return $this->restricted ? self::STATUS_RESTRICTED : self::STATUS_ACTIVE;
    }

    public function isRestricted()
    {
        return $this->restricted;
    }

    /**
     * Find the user instance for the given username.
     *
     * @param  string  $username
     * @return \App\Models\Users\User
     */
    public function findForPassport($username)
    {
        return $this->where('username', $username)->first();
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token, new Agent()));
    }

    /**
     * @param Wallet|Model|null $wallet
     */
    public function setWallet(Wallet|Model|null $wallet): void
    {
        $this->wallet = $wallet;
    }

    public function scopeWithTotalStaked(Builder $query, CryptoCurrency $currency, array $statues)
    {
        return $query->addSelect(['btc_total_staked' => Bet::select('stake')
            ->whereColumn('bets.user_id', 'users.id')
            ->whereIn('status', $statues)
            ->whereHas('wallet', function ($query) use ($currency) {
                $query->where('crypto_currency_id', $currency->getKey());
            })
            ->sum('stake')
        ]);
    }

    public function userKyc(){
        return $this->hasOne(UserKyc::class);
    }

    public function userDocs(){
        return $this->hasMany(Document::class);
    }
}
