<?php

namespace Modules\Ticket\Entities;

use Modules\User\Entities\User;
use Modules\Order\Entities\Order;
use Modules\Support\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = [];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }
}
