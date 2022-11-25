<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    public function ClassTeacher(){
        return $this->belongsTo(MappingClassTeacher::class);
    }

    protected $fillable = [];
}
