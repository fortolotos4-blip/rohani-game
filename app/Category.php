<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['slug','name','description'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}

