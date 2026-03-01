<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

/**
 * Scope global SaaS : toutes les requêtes sont filtrées par l'entreprise de l'utilisateur connecté.
 * Garantit qu'aucune donnée d'une autre entreprise ne soit visible ou modifiable.
 */
trait EnterpriseScopeTrait
{
    /**
     * Boot le trait et ajouter le scope global (isolation multi-tenant).
     */
    protected static function bootEnterpriseScopeTrait()
    {
        static::addGlobalScope('enterprise', function (Builder $builder) {
            if (!auth()->check()) {
                return;
            }
            $user = auth()->user();
            if ($user->isSuperAdmin()) {
                return; // super admin voit tout
            }
            $table = $builder->getModel()->getTable();
            if ($user->enterprise_id) {
                $builder->where($table . '.enterprise_id', $user->enterprise_id);
            } else {
                // Utilisateur sans entreprise (hors super admin) : ne voir aucune donnée
                $builder->whereRaw('1 = 0');
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
