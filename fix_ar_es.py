import json

ar_path = 'terangaguest_app/lib/l10n/app_ar.arb'
es_path = 'terangaguest_app/lib/l10n/app_es.arb'

# Arabic dictionary of manual fixes
ar_fixes = {
    'requestDoctor': 'طلب طبيب',
    'amenitiesConcierge': 'وسائل الراحة والكونسيرج',
    'wellnessSportLeisure': 'العافية، الرياضة والترفيه',
    'leisureCategory': 'الترفيه',
    'sportCategory': 'الرياضة'
}

# Spanish dictionary of manual fixes
es_fixes = {
    'requestDoctor': 'Solicitar un médico',
    'amenitiesConcierge': 'Amenities y Conserjería',
    'wellnessSportLeisure': 'Bienestar, Deporte y Ocio',
    'leisureCategory': 'Ocio',
    'sportCategory': 'Deporte'
}

for path, fixes in [(ar_path, ar_fixes), (es_path, es_fixes)]:
    with open(path, 'r', encoding='utf-8') as f:
        data = json.load(f)
        
    for k, v in fixes.items():
        if k in data:
            data[k] = v
            print(f"Fixed {k} in {path}")
            
    with open(path, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
