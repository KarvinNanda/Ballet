<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassTransaction extends Model
{
    use HasFactory;
    public function ClassChild(){
        return $this->hasMany(MappingClassChild::class);
    }

    public function ClassTeacher(){
        return $this->hasMany(MappingClassTeacher::class);
    }

    public function Schedule(){
        return $this->hasMany(Schedule::class);
    }

    public function Transaction(){
        return $this->hasMany(Transaction::class);
    }

    protected $fillable = [];
}
