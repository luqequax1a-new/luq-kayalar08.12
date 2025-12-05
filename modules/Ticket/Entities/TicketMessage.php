<?php

namespace Modules\Ticket\Entities;

use Modules\Support\Eloquent\Model;

class TicketMessage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_internal' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'message_id');
    }
}
