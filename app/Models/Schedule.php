<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;
    public function ClassTransactions(){
        return $this->hasMany(ClassTransaction::class);
    }

    public function HeaderAbsen(){
        return $this->belongsTo(HeaderAbsen::class);
    }

    protected $fillable = [];
}
