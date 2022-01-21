<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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

    /**
     * @param int $amount
     *
     * @return $this
     * @throws \Throwable
     */
    public function deposit(int $amount): self
    {
        throw_unless($amount > 0, new Exception('Please provide a positive amount', 422));

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
        throw_unless($amount > 0, new Exception('Please provide a positive amount', 422));

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
        throw_unless($amount % 5 === 0, new Exception('Amount should be divisible by 5', 422));
        throw_if($amount < 0 && abs($amount) > $this->balance, new Exception('Insufficient balance. Needed funds: ' . (abs($amount) - $this->balance), 422));

        return tap($this->forceFill([
            'balance' => $this->balance + $amount,
        ]))->save();
    }
}
