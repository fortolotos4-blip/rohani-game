<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    protected $fillable = ['question_id','user_id','choice_id','correct','time_taken_seconds'];

    public function question() { return $this->belongsTo(Question::class); }
    public function choice() { return $this->belongsTo(Choice::class); }
}

