<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    public $incrementing    = true;

    protected $primaryKey   = 'id';

    protected $table        = 'Merchants';

    protected $fillable     = [
                                'id',
                                'user_id',
                                'merchant_name',
                                'created_by',
                                'updated_by',
                                'created_at',
                                'updated_at'
                            ];
}
