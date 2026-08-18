<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_no', 'subject', 'requester_name', 'requester_email', 'priority', 'status', 'message',
    ];
}
