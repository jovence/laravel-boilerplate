<?php

namespace App\Models;


use App\Traits\HasUuidAndFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use SoftDeletes, HasUuidAndFilters;

    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = ['name', 'email', 'password'];
}
