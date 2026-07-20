# Debug Session: vitrine-rooms-500
- **Status**: [OPEN]
- **Issue**: L'endpoint `/api/vitrine/rooms` retourne HTTP 500 dans l'application mobile sur l'écran des tarifs.
- **Debug Server**: Pending
- **Log File**: Pending

## Reproduction Steps
1. Ouvrir l'application mobile.
2. Aller dans le module `Chambre`.
3. Ouvrir `Tarifs des chambres`.
4. Observer l'appel `GET /api/vitrine/rooms`.
5. Constater la réponse HTTP 500.

## Hypotheses & Verification
| ID | Hypothesis | Likelihood | Effort | Evidence |
|----|------------|------------|--------|----------|
| A | Une valeur `type_name` ou `description` en base n'est pas un JSON traduisible valide et casse la sérialisation. | High | Low | Pending |
| B | Le getter `getTypeNameAttribute()` du modèle `Room` provoque une erreur quand `type_name` est vide ou mal formé. | High | Low | Pending |
| C | Le cast ou le format de `amenities` / `price_per_night` contient une valeur inattendue sur une ligne seedée existante. | Medium | Low | Pending |
| D | Le mapping de l'endpoint `rooms()` mélange attribut brut et attribut calculé, ce qui provoque une exception Laravel à l'accès d'un champ. | Medium | Low | Pending |
| E | Le seeding n'a pas été appliqué sur l'environnement ciblé, et une ancienne donnée incompatible déclenche le 500. | Medium | Medium | Pending |

## Log Evidence
- Pending

## Verification Conclusion
- Pending
