<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Choice extends Model
{
    protected $fillable = ['question_id','text','is_correct'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}

