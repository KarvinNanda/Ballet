<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public function Type()
    {
        return $this->belongsTo(ClassType::class, 'class_type_id');
    }

    public function mapping()
    {
        return $this->hasMany(MappingClassTeacher::class, 'class_id');
    }

    /** "Course – Teacher" for page titles; the first mapped teacher by user id. */
    public function label(): string
    {
        $teacher = DB::table('mapping_class_teachers')
            ->join('users', 'users.id', 'mapping_class_teachers.user_id')
            ->where('mapping_class_teachers.class_id', $this->id)
            ->orderBy('users.id')
            ->value('users.name');

        return ($this->Type?->class_name ?? 'Class').($teacher ? ' – '.$teacher : '');
    }

    protected $fillable = [];
}
