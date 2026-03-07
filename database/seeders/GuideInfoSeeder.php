<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GuideInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Numéros d’urgence',
                'order' => 1,
                'items' => [
                    ['title' => 'Pompiers', 'phone' => '18'],
                    ['title' => 'Pompiers de Malick Sy', 'phone' => '+221 33 823 03 59'],
                    ['title' => 'Police et gendarmerie', 'phone' => '17'],
                    ['title' => 'Gendarmerie nationale', 'phone' => '+221 800 00 20 20'],
                    ['title' => 'Groupe Intervention Rapide', 'phone' => '+221 77 529 01 52'],
                    ['title' => 'Chef Brigade accidents', 'phone' => '+221 77 529 01 03'],
                    ['title' => 'Signalement contre les tentatives de racket', 'phone' => '+221 33 821 24 31'],
                    ['title' => 'Commissariat central de Dakar', 'phone' => '+221 33 842 33 41'],
                    ['title' => 'Gendarmerie nationale de Saly', 'phone' => '+221 33 957 19 61'],
                ]
            ],
            [
                'name' => 'Santé & Hôpitaux',
                'order' => 2,
                'items' => [
                    ['title' => 'SOS Médecin', 'phone' => '+221 33 889 15 15'],
                    ['title' => 'Clinique de la Madeleine', 'address' => '18 Av des Jambaars, Dakar', 'phone' => '+221 33 889 94 70'],
                    ['title' => 'Hôpital Principal de Dakar', 'address' => '1, avenue Nelson Mandela, Dakar', 'phone' => '+221 33 839 50 50'],
                    ['title' => 'Hôpital Le Dantec', 'phone' => '+221 33 889 38 00'],
                    ['title' => 'Clinique Casahous', 'phone' => '+221 33 889 72 00'],
                    ['title' => 'Clinique du Cap', 'phone' => '+221 33 889 02 02'],
                    ['title' => 'URGENCE 24 (Saly)', 'address' => '79E Rte de Saly', 'phone' => '+221 33 957 47 47'],
                    ['title' => 'Centre Régional Hospitalier (Saint-Louis)', 'address' => 'Boulevard Abdoulaye Mar Diop', 'phone' => '+221 77 289 02 44'],
                    ['title' => 'Hôpital régional de Ziguinchor', 'address' => 'Quartier Néma', 'phone' => '+221 33 991 11 54'],
                ]
            ],
            [
                'name' => 'Transport',
                'order' => 3,
                'items' => [
                    ['title' => 'Aéroport Blaise Diagne', 'phone' => '+221 33 939 59 00', 'description' => 'Départ ou arrivée des vols en temps réel : sur le site internet de l’aéroport'],
                    ['title' => 'Taxi urbain', 'description' => 'Très présent dans les grandes villes'],
                    ['title' => 'Applications de transport', 'description' => 'Yango, Heetch'],
                    ['title' => 'Location de véhicule', 'description' => 'Disponible dans les hôtels et agences spécialisées'],
                    ['title' => 'Navettes et bus', 'description' => 'Dans certaines zones'],
                ]
            ],
            [
                'name' => 'Pratique & Internet',
                'order' => 4,
                'items' => [
                    ['title' => 'Culture & respect', 'description' => "Le Sénégal est un pays reconnu pour la Teranga (hospitalité).\n\nQuelques conseils :\n- Respecter les lieux religieux\n- Adopter une tenue correcte dans les lieux publics et religieux\n- Demander l’autorisation avant de photographier certaines personnes"],
                    ['title' => 'Santé Pratique', 'description' => "Hôpitaux et cliniques modernes disponibles à Dakar et dans les grandes villes.\nPharmacies accessibles dans les zones urbaines.\nIl est conseillé de boire de l’eau en bouteille."],
                    ['title' => 'Internet & communication', 'description' => "Couverture mobile très bonne avec :\n- Orange\n- Free\n- Expresso\n\nWi-Fi disponible dans les hôtels, restaurants et certains lieux publics."],
                ]
            ],
            [
                'name' => 'Découvrir le Sénégal',
                'order' => 5,
                'items' => [
                    ['title' => 'Ville de Dakar', 'latitude' => 14.7110139, 'longitude' => -17.5360373, 'description' => 'La capitale sénégalaise séduit d’abord par son patrimoine architectural, vestiges de la colonisation française. Dakar est une ville cosmopolite aux trésors multiples. De la pointe des Almadies au Cap Manuel, elle vibre au rythme des vagues, dévoilant ses lieux festifs, ses espaces culturels et ses marchés colorés.'],
                    ['title' => 'Gorée', 'latitude' => 14.6672883, 'longitude' => -17.4030526, 'description' => 'Trait d’union entre le passé et le présent, Gorée exerce une fascination extraordinaire sur ses visiteurs, célèbres et anonymes, qui, en déambulant dans les ruelles de l’île, marchent sur les empreintes laissées par les fantômes du passé. Un lieu unique chargé d’émotion où les maisons en vieux rose laissent deviner, à travers leurs fenêtres, l’histoire de tout un peuple.'],
                    ['title' => 'Monument de la renaissance', 'latitude' => 14.7222092, 'longitude' => -17.497148, 'description' => 'Classé parmi les monuments les plus hauts du monde, le Monument est composé d’une imposante statue de 52 m en bronze et cuivre représentant un couple et un enfant reposant sur une colline d’environ 100m. Ode à l’Afrique, il offre une vue spectaculaire sur Dakar et ses plages.'],
                    ['title' => 'Lac Rose', 'latitude' => 14.8417276, 'longitude' => -17.2453921, 'description' => 'Curiosité naturelle, le lac Rose est un lagon entouré de dunes et de filaos. Il doit sa renommée à sa couleur qui vire du rose au mauve en fonction de l’intensité des rayons du soleil. Les environs du lac sont un excellent terrain de jeu pour des excursions sur les dunes en 4x4, en moto ou en quad ou pour des promenades plus paisibles à cheval, à dos de chameaux.'],
                    ['title' => 'Saly', 'latitude' => 14.445287, 'longitude' => -17.0277572, 'description' => 'Plus grand centre touristique d’Afrique de l’Ouest, la station balnéaire de Saly Portudal abrite de nombreux hôtels et restaurants aux charmes variés et aux programmes d’animation éclectiques. Plonger dans l’Océan, se prélasser au bord des piscines et admirer le coucher de soleil sont des grands classiques de cette destination. On peut également y pratiquer la pêche, la planche à voile, le ski nautique, le tennis, l’équitation, et le Golf de Saly est un parcours sur lequel les amateurs trouveront leur bonheur.'],
                    ['title' => 'La reserve de Bandia', 'latitude' => 14.5873657, 'longitude' => -17.0190829, 'description' => 'Réserve animalière à proximité directe des hôtels de Saly Portudal, elle invite à des safaris à la portée de tous. Les 3500 hectares de cette forêt abritent une faune variée (rhinocéros, singes, gazelles, girafes, reptiles...) dans un décor de baobabs géants et de lianes. Le visiteur peut également découvrir des vestiges de la civilisation sérère parsemés dans la réserve.'],
                    ['title' => 'Joal Fadiouth', 'latitude' => 14.1793041, 'longitude' => -16.8635195, 'description' => 'Joal est un petit village de pêcheurs qui s’est agrandi pour devenir aujourd’hui un des plus grands ports de pêche artisanal du Sénégal. Une visite guidée de la maison natale du Président poète Léopold Sedar Senghor et de l’île de Fadiouth, entièrement bâtie sur un amas de coquillages et reliée à la terre par deux ponts en bois vous plonge dans un univers enchanteur. Le cimetière mixte, que se partagent chrétiens et musulmans est une belle preuve de la cohabitation harmonieuse de ces deux religions.'],
                    ['title' => 'Les iles du Sine Saloum', 'latitude' => 13.9156764, 'longitude' => -16.5962177, 'description' => 'Classé réserve mondiale de la biosphère et au Patrimoine mondial de l’UNESCO, le Delta du Saloum est une invitation à la contemplation, à la sérénité et à la douceur. Les pirogues qui sillonnent le dédale de bolongs (bras de mer étroits) mènent à la découverte de la multitude d’îles, des milliers d’oiseaux (pélicans, calaos, flamants roses...), et des mammifères (Hyènes chacals, phacochères, dauphins). Parmi les zones les plus poissonneuses au monde, le Delta du saloum est un site idéal pour les pêcheurs de loisir.'],
                    ['title' => 'Sites Mégalitiques de Kaolack', 'latitude' => 13.6911115, 'longitude' => -15.5246957, 'description' => 'Kaolack, chef lieu de la région du Saloum et capitale de l’arachide et du sel, est une région carrefour dont le marché est l’un des plus grands du pays. La région abrite un nombre considérable de sites mégalithiques ou « menhirs bretons ». A Nioro du Rip, sont érigés plus de 1 000 cercles concentriques avec plus 30 000 pierres. Il s’agit des vestiges funéraires d’une civilisation qui eut cours de l’an 200 avant J.C. jusqu’au XVIe siècle.'],
                    ['title' => 'Parc de Niokolo-Koba', 'latitude' => 13.0020502, 'longitude' => -13.5813666, 'description' => 'Classé Patrimoine mondial et Réserve de la biosphère internationale, le parc du Niokolo-Koba s’étend sur une superficie de plus de 950 000 hectares et offre un paysage riche et très varié, où se concentrent presque toutes les espèces végétales et animales des savanes de l’Ouest africain. Il renferme également de petites collines dont l’Assirik (311 m), qui surplombent les cours d’eau où les animaux viennent s’abreuver.'],
                    ['title' => 'Pays Bassari et Bédik', 'latitude' => 12.5509486, 'longitude' => -12.8448607, 'description' => 'Aux pieds du Fouta Djallon, un territoire sauvage et montagneux abrite les peuples Bassari et Bédick. Des communautés atypiques dont la découverte des cultures est un véritable voyage au cœur des rites africains originels. L’osmose avec la nature se traduit dans les rites, les cérémonies d’initiation, l’architecture des habitations, les récits des mythes et légendes. Une richesse classée depuis 2012 au Patrimoine mondial de l’Unesco.'],
                    ['title' => 'Chutes de Dindefelo', 'latitude' => 12.3772271, 'longitude' => -12.3354685, 'description' => 'Un bruissement d’eau et puis la découverte d’une cascade majestueuse : c’est la magie des Chutes de Dindefello. Du sommet de la montagne, l’eau s’écrase sur les différents paliers jusqu’à atterrir sur le bassin, dans une chorégraphie fabuleuse. Une invitation à une baignade rafraichissante dans une source aux vertus thérapeutiques et mystérieuses. Aussi, les populations locales jouent le rôle de guides pour aller à la découverte des autres merveilles qui composent le catalogue touristique de Dindefelo.'],
                    ['title' => 'Kafountine', 'latitude' => 12.9330338, 'longitude' => -16.7523952, 'description' => 'Dans un décor de bolongs et de lagunes, Kafountine est un magnifique spot pour les ornithologues et les passionnés de pêche grâce à ses magnifiques plages. Non loin de là, Abéné attire beaucoup de monde de par ses activités culturelles telles que ses festivals et cérémonies traditionnelles organisées au mois de décembre de chaque année.'],
                    ['title' => 'L’ile de Karabane', 'latitude' => 12.5377992, 'longitude' => -16.7183526, 'description' => 'Ancien comptoir colonial, l’île située à l’embouchure du fleuve, conserve les traces de son passé de ville administrative de premier plan. Accessible par pirogue, le village est un refuge paradisiaque recouvert d’une végétation luxuriante : palmiers, cocotiers, baobabs, fromagers, manguiers, flamboyants, bougainvillées. L’île est au centre d’un immense domaine halieutique avec d’innombrables espèces de poissons tropicaux ; un lieu de rêve pour pêcher et nager avec les dauphins.'],
                    ['title' => 'Oussouye', 'latitude' => 12.4902554, 'longitude' => -16.5529418, 'description' => 'Carrefour géographique de la Basse Casamance, Oussouye est le centre d’une région imprégnée par l’animisme et organisée en minuscules royaumes. Il est également le berceau de culture de la basse Casamance où sont célébrées chaque année la récolte du vin de palme et les cérémonies de lutte traditionnelle. On peut y découvrir les cases à impluvium d’Enampore et de Séléki qui se transforment souvent en campements pour accueillir les visiteurs.'],
                    ['title' => 'Le Cap Skirring', 'latitude' => 12.3585663, 'longitude' => -16.7320308, 'description' => 'Très prisé et doté d’un aérodrome, le Cap Skirring est l’une des plus belles stations balnéaires de l’Afrique de l’Ouest. Le sable est d’une finesse rare et la côte est très arborée (cocotiers, palmiers, etc...). De grands complexes hôteliers touristiques s’y déploient où tous les goûts seront servis et tous les luxes permis. La station a su se faire un nom chez les amateurs de belles pêches et on peut y pratiquer aussi l’équitation et le ski nautique.'],
                    ['title' => 'La haute Casamance', 'latitude' => 13.1307556, 'longitude' => -14.9723407, 'description' => 'C’est la transition entre la zone forestière du sud et la savane arborée du Sénégal Oriental mais également une grande zone de chasse notamment du gibier à plume ou à quatre pattes. Les paysages y sont très beaux. Sédhiou et ses contreforts, sont le fief des « kankouran » et du « Djambadong », la danse des feuilles.'],
                    ['title' => 'Saint Louis', 'latitude' => 16.0199527, 'longitude' => -16.4934046, 'description' => 'Ile située dans le delta du fleuve Sénégal, Saint Louis, ou la majestueuse «Venise africaine», fut l’ancienne capitale de l’Afrique de l’Ouest francophone. Son passé colonial s’admire à travers ses maisons colorées aux balcons en fer forgé qui font partie intégrante du charme de la ville. A pied ou en calèche, des excursions dans la ville dévoilent toute l’élégance de sa société et la richesse de son patrimoine culturel. Le Festival International de Jazz, le Fanal et les régates de Saint Louis sont des moments vibrants de l’agenda festif de la ville.'],
                    ['title' => 'Fort de Podor', 'latitude' => 16.6565509, 'longitude' => -14.9585993, 'description' => 'Plus au nord, le Fort de Podor plus connue sous le nom de Fort de Faidherbe, édifié entre 1818 et 1819 est classé patrimoine historique mondial. Vieux de 190 ans, ce site repose sur une colline qui surplombe la ville et offre un superbe panorama et une très belle vue sur le fleuve Sénégal.'],
                    ['title' => 'Parc de Djoudj', 'latitude' => 16.399321, 'longitude' => -16.2426408, 'description' => 'Situé à 70 Km de Saint Louis, le parc du Djoudj est la troisième réserve ornithologique du monde. Inscrit au Patrimoine Mondial de l’Unesco, le parc s’étend sur 16 000 hectares et compte près de trois millions d’oiseaux migrateurs dont environ 400 espèces on été répertoriées (flamant rose, pélican blanc, aigrettes, oie de Gambie, héron cendré, canards, grands cormorans, martins-pêcheurs ...)'],
                    ['title' => 'Le désert de Lompoul', 'latitude' => 15.4635636, 'longitude' => -16.69482, 'description' => 'Propice à la rencontre de tribus nomades, ce petit désert de couleur ocre s’étend sur une vingtaine de kilomètres jusqu’à la mer, et fascine par son paysage de dunes. Une nuit en bivouac dans ce petit désert peut constituer un moment original et inoubliable.'],
                ]
            ],
        ];

        foreach ($data as $catData) {
            $category = \App\Models\GuideCategory::firstOrCreate([
                'name' => $catData['name'],
            ], [
                'order' => $catData['order'],
                'is_active' => true,
            ]);

            $order = 1;
            foreach ($catData['items'] as $itemData) {
                \App\Models\GuideItem::firstOrCreate([
                    'guide_category_id' => $category->id,
                    'title' => $itemData['title'],
                ], array_merge($itemData, [
                        'order' => $order++,
                        'is_active' => true,
                    ]));
            }
        }
    }
}
