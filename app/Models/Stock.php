<?php

namespace App\Models;

use App\Support\Like;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    public function reportStock(){
        return $this->hasMany(ReportStock::class);
    }

    /** Name filter shared by a list page and its sort action, so a sort link keeps the search. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when(filled($term), fn (Builder $q) => $q->where('name', 'like', Like::contains($term)));
    }
}
