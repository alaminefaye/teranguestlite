import json
import os

files = {
    'fr': 'terangaguest_app/lib/l10n/app_fr.arb',
    'en': 'terangaguest_app/lib/l10n/app_en.arb',
    'ar': 'terangaguest_app/lib/l10n/app_ar.arb',
    'es': 'terangaguest_app/lib/l10n/app_es.arb'
}

for lang, path in files.items():
    with open(path, 'r', encoding='utf-8') as f:
        data = json.load(f)
    
    if lang == 'fr':
        data['halfDayOption'] = 'Demi-journée'
        data['fullDayOption'] = '1 Journée'
        data['multipleDaysOption'] = 'Plusieurs jours'
        data['allOption'] = 'Tous'
    elif lang == 'en':
        data['halfDayOption'] = 'Half-day'
        data['fullDayOption'] = '1 Day'
        data['multipleDaysOption'] = 'Multiple days'
        data['allOption'] = 'All'
    elif lang == 'es':
        data['halfDayOption'] = 'Medio día'
        data['fullDayOption'] = '1 Día'
        data['multipleDaysOption'] = 'Varios días'
        data['allOption'] = 'Todos'
    elif lang == 'ar':
        data['halfDayOption'] = 'نصف يوم'
        data['fullDayOption'] = 'يوم واحد'
        data['multipleDaysOption'] = 'أيام متعددة'
        data['allOption'] = 'الكل'
        
    with open(path, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)

print('Added vehicle dropdown keys to all ARBs.')
