<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailAbsen extends Model
{
    use HasFactory;
    public function HeaderAbsens(){
        return $this->belongsTo(HeaderAbsen::class);
    }

    public function Students(){
        return $this->belongsTo(Student::class);
    }

    protected $fillable = [];

}
