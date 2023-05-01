<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name'
    ];

    public const ADMIN = 'admin';

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'roles_users');
    }
}
