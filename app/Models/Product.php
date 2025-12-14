<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function ordes(): HasMany{
        return $this->hasMany(Order::class);
    }

    public function category(): BelongsTo{
        return $this->belongsTo(Category::class);
    }
}
