import json

fr = json.load(open('terangaguest_app/lib/l10n/app_fr.arb'))
missing = json.load(open('missing_ar.json'))

for k in missing:
    print(f"{k}: {fr[k]}")
