<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    public function ClassChild(){
        return $this->hasMany(MappingClassChild::class);
    }

    public function Transaction(){
        return $this->hasMany(Transaction::class);
    }

    public function DetailAbsen(){
        return $this->hasMany(DetailAbsen::class);
    }

    public function Rekening(){
        return $this->hasOne(Rekenings::class);
    }

    protected $fillable = [];
}
