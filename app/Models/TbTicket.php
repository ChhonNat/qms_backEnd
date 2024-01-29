<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TbTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'ticket_no',
        'is_call'
    ];
}
