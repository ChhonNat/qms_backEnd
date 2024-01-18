<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TbQueue extends Model
{
    use HasFactory, SoftDeletes;

    public $fillable = [
        'counter_id',
        'service_id',
        'q_no',
        'q_name',
        'noted',
        'is_called'
    ];
}
