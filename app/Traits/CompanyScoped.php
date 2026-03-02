<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;
use RuntimeException;

trait CompanyScoped
{
    protected static function bootCompanyScoped()
    {
        // 1) Always filter by company_id from session
        static::addGlobalScope('company', function (Builder $builder) {
            $companyId = Session::get('company_id');

            // safer: show nothing if not set
            if (!$companyId) {
                $builder->whereRaw('1=0');
                return;
            }

            $builder->where($builder->getModel()->getTable() . '.company_id', $companyId);
        });

        // 2) Enforce company_id presence and match for all writes
        static::saving(function ($model) {
            $companyId = Session::get('company_id');

            if (!$companyId) {
                throw new RuntimeException('company_id not found in session.');
            }

            if (!empty($model->company_id) && (int) $model->company_id !== (int) $companyId) {
                throw new RuntimeException('company_id mismatch for this session.');
            }
        });

        // 3) Auto-set company_id on create
        static::creating(function ($model) {
            $companyId = Session::get('company_id');

            if (!$companyId) {
                throw new RuntimeException('company_id not found in session.');
            }

            if (empty($model->company_id)) {
                $model->company_id = $companyId;
                return;
            }

            if ((int) $model->company_id !== (int) $companyId) {
                throw new RuntimeException('company_id mismatch for this session.');
            }
        });

        // 4) Prevent changing company_id on update
        static::updating(function ($model) {
            if ($model->isDirty('company_id')) {
                $model->company_id = $model->getOriginal('company_id');
            }
        });

        // 5) Ensure deletes are scoped to the session company_id
        static::deleting(function ($model) {
            $companyId = Session::get('company_id');

            if (!$companyId) {
                throw new RuntimeException('company_id not found in session.');
            }

            if ((int) $model->company_id !== (int) $companyId) {
                throw new RuntimeException('company_id mismatch for this session.');
            }
        });
    }

    public static function withoutCompanyScope()
    {
        return static::withoutGlobalScope('company');
    }
}
