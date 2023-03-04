<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderAbsen extends Model
{
    use HasFactory;
    protected $fillable = ['schedules_id'];
    public function Schedules(){
        return $this->belongsTo(Schedule::class);
    }

    public function DetailAbsen(){
        return $this->hasMany(DetailAbsen::class);
    }
}
