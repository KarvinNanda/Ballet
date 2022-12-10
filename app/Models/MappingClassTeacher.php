<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MappingClassTeacher extends Model
{
    use HasFactory;
    public function ClassTransactions(){
        return $this->belongsTo(ClassTransaction::class);
    }

    public function Teachers(){
        return $this->belongsTo(Teacher::class);
    }

    public function User(){
        return $this->belongsTo(User::class);
    }

    protected $fillable = [];

}
