<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['role', 'nisn', 'username', 'email', 'password'])]
#[Hidden(['password'])]
class Account extends Authenticatable
{
    protected $table = 'accounts';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
