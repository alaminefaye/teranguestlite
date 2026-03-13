<?php
require __DIR__.'/vendor/autoload.php';

use Stichoza\GoogleTranslate\GoogleTranslate;

$fr_file = 'terangaguest_app/lib/l10n/app_fr.arb';
$ar_file = 'terangaguest_app/lib/l10n/app_ar.arb';
$es_file = 'terangaguest_app/lib/l10n/app_es.arb';

$missing_file = 'missing_ar.json';

$fr = json_decode(file_get_contents($fr_file), true);
$ar = json_decode(file_get_contents($ar_file), true);
$es = json_decode(file_get_contents($es_file), true);
$missing = json_decode(file_get_contents($missing_file), true);

$tr_ar = new GoogleTranslate('ar', 'fr');
$tr_es = new GoogleTranslate('es', 'fr');

echo "Translating " . count($missing) . " keys...\n";

foreach ($missing as $key) {
    if (!isset($fr[$key])) continue;
    $val = $fr[$key];
    
    // Ignore placeholders or very short strings if needed, but we'll translate all
    try {
        echo "Translating $key...\n";
        $ar[$key] = $tr_ar->translate($val);
        $es[$key] = $tr_es->translate($val);
    } catch (\Exception $e) {
        echo "Error on $key: " . $e->getMessage() . "\n";
    }
}

file_put_contents($ar_file, json_encode($ar, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
file_put_contents($es_file, json_encode($es, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo "Done writing to ARB files.\n";
