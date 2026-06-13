<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Customer extends User
{
    protected $table = 'users';

    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope('role_customer', function (Builder $builder) {
            $builder->where('role', 'customer');
        });

        static::creating(function ($model) {
            $model->role = 'customer';
            if (empty($model->password)) {
                $model->password = bcrypt(\Illuminate\Support\Str::random(16));
            }
        });
    }
}
