<?php

declare(strict_types=1);

namespace Konekt\Acl\Test;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Konekt\Acl\Traits\HasRoles;

class User extends Model implements AuthorizableContract, AuthenticatableContract
{
    use HasRoles;
    use Authorizable;
    use Authenticatable;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email'];

    protected $table = 'users';
}
