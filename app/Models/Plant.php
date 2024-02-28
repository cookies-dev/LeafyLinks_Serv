<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin Builder
 */
class Plant extends Model
{
    use HasFactory;

    /**
     * accessor for image attribute
     *
     * @param string $value
     * @return string
     */
    public function getImageAttribute(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        return asset("storage/$value");
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'location_id',
        'trefle_id',
        'name',
        'desc',
        'image',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
