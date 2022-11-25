<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MappingClassChild extends Model
{
    use HasFactory;
    public function ClassTransactions(){
        return $this->hasMany(ClassTransaction::class);
    }

    public function Students(){
        return $this->hasMany(Student::class);
    }

    protected $fillable = [];
}
