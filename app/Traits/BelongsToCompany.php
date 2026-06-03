<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToCompany
{
    public static function bootBelongsToCompany()
    {
        static::addGlobalScope('company_id', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('company_id', auth()->user()->company_id);
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
