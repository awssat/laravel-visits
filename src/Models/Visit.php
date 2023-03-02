<?php

namespace Awssat\Visits\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $guarded = [];
    protected $casts = ['list' => 'array', 'expired_at' => 'datetime'];
}
