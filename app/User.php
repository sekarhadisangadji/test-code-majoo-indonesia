<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    public $incrementing    = true;

    protected $primaryKey   = 'id';

    protected $table        = 'Users';

    protected $fillable     = [
                                'id',
                                'name',
                                'user_name',
                                'password',
                                'created_by',
                                'updated_by',
                                'created_at',
                                'updated_at'
                            ];

    protected $hidden       = [
                                'password'
                            ];
}
