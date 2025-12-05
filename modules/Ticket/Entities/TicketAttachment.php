<?php

namespace Modules\Ticket\Entities;

use Illuminate\Support\Facades\Storage;
use Modules\Support\Eloquent\Model;

class TicketAttachment extends Model
{
    protected $guarded = [];

    protected $appends = ['url'];

    public function message()
    {
        return $this->belongsTo(TicketMessage::class, 'message_id');
    }

    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }
}
