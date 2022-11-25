<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailAbsen extends Model
{
    use HasFactory;
    public function HeaderAbsens(){
        return $this->hasMany(HeaderAbsen::class);
    }

    public function Students(){
        return $this->hasMany(Student::class);
    }

    protected $fillable = [];

}
