<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassTransaction extends Model
{
    use HasFactory;
    public function ClassChild(){
        return $this->belongsTo(MappingClassChild::class);
    }

    public function ClassTeacher(){
        return $this->belongsTo(MappingClassTeacher::class);
    }

    public function Schedule(){
        return $this->belongsTo(Schedule::class);
    }

    protected $fillable = [];
}
