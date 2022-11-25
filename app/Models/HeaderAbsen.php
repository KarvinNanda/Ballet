<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderAbsen extends Model
{
    use HasFactory;
    public function Schedules(){
        return $this->hasMany(Schedule::class);
    }

    public function DetailAbsen(){
        return $this->belongsTo(DetailAbsen::class);
    }

    protected $fillable = [];
}
