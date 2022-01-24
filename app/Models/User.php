<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function otherTokens(PersonalAccessToken $except = null): MorphMany
    {
        $token = $except ?? $this->accessToken;

        return $this->tokens()
            ->when(
                $token instanceof PersonalAccessToken,
                fn($query) => $query->where('id', '!=', $token->id),
                function (Builder $query) {
                    // currentAccessToken() works weirdly and sometimes it returns another type of token
                    // So let's scrape token from headers, which contains token ID and use it directly
                    $token = request()->header('Authorization');

                    if (!$token) {
                        return $query;
                    }

                    $id = trim(explode('|', $token)[0], 'Bearer ');

                    if (!$id) {
                        return $query;
                    }

                    return $query->where('id', '!=', $id);
                });
    }

    /**
     * @param int $amount
     *
     * @return $this
     * @throws \Throwable
     */
    public function deposit(int $amount): self
    {
        throw_unless($amount > 0, ValidationException::withMessages(['amount' => 'Please provide a positive amount']));

        return $this->addBalance($amount);
    }

    /**
     * @param int $amount
     *
     * @return $this
     * @throws \Throwable
     */
    public function withdraw(int $amount): self
    {
        throw_unless($amount > 0, ValidationException::withMessages(['amount' => 'Please provide a positive amount']));

        return $this->addBalance(-$amount);
    }

    /**
     * @param int $amount
     *
     * @return $this
     * @throws \Throwable
     */
    protected function addBalance(int $amount): self
    {
        throw_unless($amount % 5 === 0, ValidationException::withMessages(['amount' => 'Amount should be divisible by 5']));
        throw_if($amount < 0 && abs($amount) > $this->balance, ValidationException::withMessages(['balance' => 'Insufficient balance. Needed funds: ' . (abs($amount) - $this->balance)]));

        return tap($this->forceFill([
            'balance' => $this->balance + $amount,
        ]))->save();
    }

    public function getChangeAttribute(): array
    {
        $balance = $this->balance;
        $coins = config('app.coins');
        rsort($coins);

        return array_reduce($coins, function ($change, $coin) use (&$balance) {
            $amount = floor($balance / $coin);

            if ($amount > 0) {
                $change[$coin] = $amount;
                $balance -= $amount * $coin;
            }

            return $change;
        }, []);
    }
}
