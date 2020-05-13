<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['user_1_id', 'user_2_id', 'board', 'open', 'win'];
}
