<?php

namespace Domain\Products\Models;

use Domain\Products\database\factories\ProductFactory;
use Domain\Products\Enums\Status;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $article
 * @property string $name
 * @property string $data
 * @method Builder availableProducts()
 */
class Product extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
    ];
    protected $casts = [
        'status' => Status::class,
        'data' => 'array',
    ];

    public function scopeAvailableProducts(Builder $query): void
    {
        $query->where('status', Status::AVAILABLE);
    }

    protected static function newFactory(): Factory
    {
        return ProductFactory::new();
    }
}