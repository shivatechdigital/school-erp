<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use BelongsToBranch, BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id',
        'branch_id',
        'book_category_id',
        'title',
        'isbn_no',
        'author',
        'publisher',
        'edition',
        'rack_no',
        'total_copies',
        'available_copies',
        'price',
        'cover_image',
    ];

    protected $casts = [
        'total_copies' => 'integer',
        'available_copies' => 'integer',
        'price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'book_category_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(BookIssue::class);
    }
}
