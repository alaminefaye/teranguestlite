<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait EnterpriseScopeTrait
{
    /**
     * Boot le trait et ajouter le scope global
     */
    protected static function bootEnterpriseScopeTrait()
    {
        static::addGlobalScope('enterprise', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->isSuperAdmin()) {
                $builder->where($builder->getModel()->getTable() . '.enterprise_id', auth()->user()->enterprise_id);
            }
        });
    }

    /**
     * Scope pour désactiver le filtre enterprise (pour super admin)
     */
    public function scopeWithoutEnterpriseScope(Builder $query)
    {
        return $query->withoutGlobalScope('enterprise');
    }

    /**
     * Scope pour filtrer par enterprise spécifique
     */
    public function scopeForEnterprise(Builder $query, $enterpriseId)
    {
        return $query->withoutGlobalScope('enterprise')->where('enterprise_id', $enterpriseId);
    }
}
