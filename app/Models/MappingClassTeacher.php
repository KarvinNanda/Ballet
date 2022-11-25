<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MappingClassTeacher extends Model
{
    use HasFactory;
    public function ClassTransactions(){
        return $this->hasMany(ClassTransaction::class);
    }

    public function Teachers(){
        return $this->hasMany(Teacher::class);
    }

    protected $fillable = [];

}
