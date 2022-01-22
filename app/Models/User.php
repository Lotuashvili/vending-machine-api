<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;
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
}
