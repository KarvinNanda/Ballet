<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rekenings extends Model
{
    use HasFactory;

    public function Bank(){
        return $this->belongsTo(Banks::class,'banks_id','id');
    }

    public function Student(){
        return $this->belongsTo(Student::class);
    }
}
