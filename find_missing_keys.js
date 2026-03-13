const fs = require('fs');
const fr = JSON.parse(fs.readFileSync('terangaguest_app/lib/l10n/app_fr.arb', 'utf8'));
const ar = JSON.parse(fs.readFileSync('terangaguest_app/lib/l10n/app_ar.arb', 'utf8'));
const es = JSON.parse(fs.readFileSync('terangaguest_app/lib/l10n/app_es.arb', 'utf8'));

const missingInAr = [];
const missingInEs = [];

for (const key of Object.keys(fr)) {
    if (!key.startsWith('@') && typeof fr[key] === 'string') {
        if (!ar[key]) missingInAr.push(key);
        if (!es[key]) missingInEs.push(key);
    }
}

console.log('Missing in AR:', missingInAr.length);
console.log('Missing in ES:', missingInEs.length);

fs.writeFileSync('missing_ar.json', JSON.stringify(missingInAr, null, 2));
fs.writeFileSync('missing_es.json', JSON.stringify(missingInEs, null, 2));
console.log("Details written to missing_ar.json and missing_es.json");
