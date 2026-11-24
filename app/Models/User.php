<?php

declare(strict_types=1);

namespace App\Models;

use Air\Database\Model;

class User extends Model
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';
    /** @var array<int,string> */
    protected array $fillable = ['name', 'email', 'password'];
}
