<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;

class SharedCart extends Model
{
    protected $table = 'shared_carts';

    protected $fillable = [
        'token',
        'data',
        'customer_id',
        'created_by_admin_id',
    ];

    public function getDataAttribute($value)
    {
        return unserialize($value);
    }

    public function setDataAttribute($value)
    {
        $this->attributes['data'] = serialize($value);
    }
}

