<?php

namespace Domain\Admin\Models;

use Domain\Products\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Model
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $guard_name = 'web';

    protected $casts = [
        'status' => Status::class,
        'data' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function (Admin $model) {
            $model->assignRole('admin');
        });
    }
}
