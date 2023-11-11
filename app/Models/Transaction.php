<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public function Students(){
        return $this->belongsTo(Student::class);
    }

    public function ClassTransactions(){
        return $this->belongsTo(ClassTransaction::class);
    }

    protected $fillable = ['students_id'];
}
