<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MappingClassChild extends Model
{
    use HasFactory;
    public function ClassTransactions(){
        return $this->belongsTo(ClassTransaction::class);
    }

    public function Students(){
        return $this->belongsTo(Student::class);
    }

    protected $fillable = [];
}
