import os

count = 0
for root, dirs, files in os.walk('terangaguest_app/lib'):
    for file in files:
        if file.endswith('.dart') and file != 'main.dart':
            filepath = os.path.join(root, file)
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            if "'fr_FR'" in content:
                content = content.replace("'fr_FR'", "Localizations.localeOf(context).languageCode")
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(content)
                count += 1
                print(f"Replaced in {filepath}")

print(f"Total files updated: {count}")
