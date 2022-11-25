<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    public function ClassChild(){
        return $this->belongsTo(MappingClassChild::class);
    }

    public function Transaction(){
        return $this->belongsTo(Transaction::class);
    }

    public function DetailAbsen(){
        return $this->belongsTo(DetailAbsen::class);
    }

    protected $fillable = [];
}
