<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MappingClassTeacher extends Model
{
    use HasFactory;

    protected $table = 'mapping_class_teachers';
    public function ClassTransactions(){
        return $this->belongsTo(ClassTransaction::class);
    }

    public function Teachers(){
        return $this->belongsTo(Teacher::class);
    }

    public function User(){
        return $this->belongsTo(User::class);
    }

    public function getUser(){
        return $this->belongsTo(User::class, 'user_id');
    }

    protected $fillable = [];

}
