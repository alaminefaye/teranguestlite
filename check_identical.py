import json

fr = json.load(open('terangaguest_app/lib/l10n/app_fr.arb'))
ar = json.load(open('terangaguest_app/lib/l10n/app_ar.arb'))
es = json.load(open('terangaguest_app/lib/l10n/app_es.arb'))

untranslated_ar = []
untranslated_es = []

for k, v in fr.items():
    if not k.startswith('@') and isinstance(v, str):
        if k in ar and ar[k] == v:
            untranslated_ar.append((k, v))
        if k in es and es[k] == v:
            untranslated_es.append((k, v))

print("Untranslated in AR (Value == French):")
for k, v in untranslated_ar:
    print(f"  {k}: {v}")
    
print("\nUntranslated in ES (Value == French):")
for k, v in untranslated_es:
    print(f"  {k}: {v}")
