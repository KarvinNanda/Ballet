<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;
    public function ClassTransactions(){
        return $this->belongsTo(ClassTransaction::class);
    }

    public function HeaderAbsen(){
        return $this->hasMany(HeaderAbsen::class);
    }

    protected $fillable = [];
}
