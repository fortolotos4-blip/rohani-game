<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['category_id','image_path','prompt','answer_text','explanation','time_limit_seconds'];

    public function choices()
    {
        return $this->hasMany(Choice::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

