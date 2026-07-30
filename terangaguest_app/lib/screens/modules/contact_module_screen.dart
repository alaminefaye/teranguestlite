import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../config/theme.dart';
import '../../models/guide.dart';
import '../../models/user.dart';
import '../../services/guides_api.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../utils/navigation_helper.dart';
import '../../widgets/service_card.dart';
import '../hotel_infos/guide_items_screen.dart';

class ContactModuleScreen extends StatefulWidget {
  final Enterprise? enterprise;

  const ContactModuleScreen({super.key, this.enterprise});

  @override
  State<ContactModuleScreen> createState() => _ContactModuleScreenState();
}

class _ContactModuleScreenState extends State<ContactModuleScreen> {
  List<GuideCategory>? _categories;
  bool _loadingGuides = false;

  static final _defaultUsefulNumbersCategory = GuideCategory(
    id: -1,
    name: 'Numéros utiles',
    categoryType: 'useful_numbers',
    order: 1,
    isActive: true,
    items: [
      GuideItem(
        id: 1,
        categoryId: -1,
        title: 'BAR PISCINE',
        phone: '2010',
        order: 1,
        isActive: true,
      ),
      GuideItem(
        id: 2,
        categoryId: -1,
        title: 'BAR SAINT-LOUIS',
        phone: '2009',
        order: 2,
        isActive: true,
      ),
      GuideItem(
        id: 3,
        categoryId: -1,
        title: 'BASE NAUTIQ',
        phone: '2702',
        order: 3,
        isActive: true,
      ),
      GuideItem(
        id: 4,
        categoryId: -1,
        title: 'BOUTIQUE',
        phone: '2013',
        order: 4,
        isActive: true,
      ),
      GuideItem(
        id: 5,
        categoryId: -1,
        title: 'INFIRMERIE',
        phone: '2016',
        order: 5,
        isActive: true,
      ),
      GuideItem(
        id: 6,
        categoryId: -1,
        title: 'SALLE DE SPORT',
        phone: '2015',
        order: 6,
        isActive: true,
      ),
      GuideItem(
        id: 7,
        categoryId: -1,
        title: 'SDT EXCURSION',
        phone: '2008',
        order: 7,
        isActive: true,
      ),
      GuideItem(
        id: 8,
        categoryId: -1,
        title: 'RECEPTION',
        phone: '2500',
        order: 8,
        isActive: true,
      ),
      GuideItem(
        id: 9,
        categoryId: -1,
        title: 'SECURITE',
        phone: '2501',
        order: 9,
        isActive: true,
      ),
      GuideItem(
        id: 10,
        categoryId: -1,
        title: 'RELATION CLIENTELE FRAM',
        phone: '2017',
        order: 10,
        isActive: true,
      ),
      GuideItem(
        id: 11,
        categoryId: -1,
        title: 'ROOM SERVICE',
        phone: '2408',
        order: 11,
        isActive: true,
      ),
      GuideItem(
        id: 12,
        categoryId: -1,
        title: 'SPA',
        phone: '2012',
        order: 12,
        isActive: true,
      ),
    ],
  );

  static final _defaultEmergencyNumbersCategory = GuideCategory(
    id: -2,
    name: 'Numéros urgence',
    categoryType: 'emergency_numbers',
    order: 2,
    isActive: true,
    items: [
      GuideItem(
        id: 1,
        categoryId: -2,
        title: 'RECEPTION',
        phone: '2500',
        order: 1,
        isActive: true,
      ),
      GuideItem(
        id: 2,
        categoryId: -2,
        title: 'INFIRMERIE',
        phone: '2016',
        order: 2,
        isActive: true,
      ),
      GuideItem(
        id: 3,
        categoryId: -2,
        title: 'SECURITE',
        phone: '2501',
        order: 3,
        isActive: true,
      ),
    ],
  );

  static final _defaultPracticalInternetCategory = GuideCategory(
    id: -3,
    name: 'Pratique & Internet',
    categoryType: 'practical_internet',
    order: 3,
    isActive: true,
    items: [
      GuideItem(
        id: 1,
        categoryId: -3,
        title: 'Culture & respect',
        description:
            'Le Senegal est un pays reconnu pour la Teranga (hospitalite).\n\n'
            'Quelques conseils :\n'
            '- Respecter les lieux religieux\n'
            '- Adopter une tenue correcte dans les lieux publics et religieux\n'
            '- Demander l’autorisation avant de photographier certaines personnes',
        order: 1,
        isActive: true,
      ),
      GuideItem(
        id: 2,
        categoryId: -3,
        title: 'Sante Pratique',
        description:
            'Hopitaux et cliniques modernes disponibles a Dakar et dans les grandes villes.\n'
            'Pharmacies accessibles dans les zones urbaines.\n'
            'Il est conseille de boire de l’eau en bouteille.',
        order: 2,
        isActive: true,
      ),
      GuideItem(
        id: 3,
        categoryId: -3,
        title: 'Internet & communication',
        description:
            'Couverture mobile tres bonne avec :\n'
            '- Orange\n'
            '- Free\n'
            '- Expresso\n\n'
            'Wi-Fi disponible dans les hotels, restaurants et certains lieux publics.',
        order: 3,
        isActive: true,
      ),
    ],
  );

  static final _defaultDiscoverSenegalCategory = GuideCategory(
    id: -4,
    name: 'Découvrir le Sénégal',
    categoryType: 'discover_senegal',
    order: 4,
    isActive: true,
    items: [
      GuideItem(
        id: 1,
        categoryId: -4,
        title: 'Ville de Dakar',
        description:
            'La capitale senegalaise seduit d’abord par son patrimoine architectural, vestiges de la colonisation francaise. Dakar est une ville cosmopolite aux tresors multiples. De la pointe des Almadies au Cap Manuel, elle vibre au rythme des vagues, devoilant ses lieux festifs, ses espaces culturels et ses marches colores.',
        order: 1,
        isActive: true,
      ),
      GuideItem(
        id: 2,
        categoryId: -4,
        title: 'Gorée',
        description:
            'Trait d’union entre le passe et le present, Goree exerce une fascination extraordinaire sur ses visiteurs, celebres et anonymes, qui, en deambulant dans les ruelles de l’ile, marchent sur les empreintes laissees par les fantomes du passe. Un lieu unique charge d’emotion ou les maisons en vieux rose laissent deviner, a travers leurs fenetres, l’histoire de tout un peuple.',
        order: 2,
        isActive: true,
      ),
      GuideItem(
        id: 3,
        categoryId: -4,
        title: 'Monument de la renaissance',
        description:
            'Classe parmi les monuments les plus hauts du monde, le Monument est compose d’une imposante statue de 52 m en bronze et cuivre representant un couple et un enfant reposant sur une colline d’environ 100 m. Ode a l’Afrique, il offre une vue spectaculaire sur Dakar et ses plages.',
        order: 3,
        isActive: true,
      ),
    ],
  );

  @override
  void initState() {
    super.initState();
    _loadGuides();
  }

  Future<void> _loadGuides() async {
    if (_loadingGuides) return;
    setState(() => _loadingGuides = true);
    try {
      final categories = await GuidesApi().getGuides();
      if (!mounted) return;
      setState(() => _categories = categories);
    } catch (_) {
      if (!mounted) return;
      setState(() => _categories = const []);
    } finally {
      if (mounted) {
        setState(() => _loadingGuides = false);
      }
    }
  }

  GuideCategory? _findCategory({
    required List<String> types,
    required List<String> keywords,
  }) {
    final categories = _categories ?? const <GuideCategory>[];

    for (final type in types) {
      for (final category in categories) {
        final categoryType = (category.categoryType ?? '').toLowerCase();
        if (categoryType == type.toLowerCase()) {
          return category;
        }
      }
    }

    for (final keyword in keywords) {
      final key = keyword.toLowerCase();
      for (final category in categories) {
        final name = category.name.toLowerCase();
        if (name.contains(key)) {
          return category;
        }
      }
    }

    return null;
  }

  Future<void> _launchPhone(String phone) async {
    await launchUrl(
      Uri.parse('tel:$phone'),
      mode: LaunchMode.externalApplication,
    );
  }

  Future<void> _launchEmail(String email) async {
    await launchUrl(
      Uri.parse('mailto:$email'),
      mode: LaunchMode.externalApplication,
    );
  }

  @override
  Widget build(BuildContext context) {
    final e = widget.enterprise;
    final phone = e?.phone?.trim() ?? '';
    final email = e?.email?.trim() ?? '';
    final address = e?.address?.trim() ?? '';
    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final spacing = LayoutHelper.gridSpacing(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final usefulNumbersCategory =
        _findCategory(
          types: const ['useful_numbers', 'numbers'],
          keywords: const ['numéros utiles', 'numeros utiles', 'utiles'],
        ) ??
        _defaultUsefulNumbersCategory;
    final emergencyNumbersCategory =
        _findCategory(
          types: const ['emergency_numbers', 'emergency', 'urgent_numbers'],
          keywords: const ['numéros urgence', 'numeros urgence', 'urgence'],
        ) ??
        _defaultEmergencyNumbersCategory;
    final practicalInternetCategory =
        _findCategory(
          types: const ['practical_internet', 'practical_info', 'internet'],
          keywords: const [
            'pratique & internet',
            'pratique et internet',
            'pratique',
            'internet',
            'culture',
          ],
        ) ??
        _defaultPracticalInternetCategory;
    final discoverSenegalCategory =
        _findCategory(
          types: const ['discover_senegal', 'discover', 'culture'],
          keywords: const [
            'découvrir le sénégal',
            'decouvrir le senegal',
            'sénégal',
            'senegal',
            'découvr',
            'decouvr',
          ],
        ) ??
        _defaultDiscoverSenegalCategory;

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.all(20.0),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(
                        Icons.arrow_back,
                        color: AppTheme.accentGold,
                      ),
                      onPressed: () {
                        HapticHelper.lightImpact();
                        Navigator.of(context).pop();
                      },
                    ),
                    const SizedBox(width: 12),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            'Contact',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          SizedBox(height: 4),
                          Text(
                            'Coordonnées et accès rapides',
                            style: TextStyle(
                              fontSize: 14,
                              color: AppTheme.textGray,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: ListView(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  children: [
                    if (phone.isNotEmpty)
                      _ContactTile(
                        icon: Icons.call_outlined,
                        title: 'Téléphone',
                        value: phone,
                        onTap: () async {
                          HapticHelper.lightImpact();
                          await _launchPhone(phone);
                        },
                      ),
                    if (email.isNotEmpty)
                      _ContactTile(
                        icon: Icons.email_outlined,
                        title: 'Email',
                        value: email,
                        onTap: () async {
                          HapticHelper.lightImpact();
                          await _launchEmail(email);
                        },
                      ),
                    if (address.isNotEmpty)
                      _ContactTile(
                        icon: Icons.location_on_outlined,
                        title: 'Adresse',
                        value: address,
                        onTap: null,
                      ),
                    if (phone.isEmpty && email.isEmpty && address.isEmpty)
                      const Padding(
                        padding: EdgeInsets.all(24.0),
                        child: Text(
                          'Aucune information de contact disponible.',
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            color: AppTheme.textGray,
                            height: 1.4,
                          ),
                        ),
                      ),
                    const SizedBox(height: 20),
                    const Text(
                      'Services',
                      style: TextStyle(
                        color: AppTheme.accentGold,
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    if (_loadingGuides)
                      const Padding(
                        padding: EdgeInsets.symmetric(vertical: 24),
                        child: Center(
                          child: CircularProgressIndicator(
                            valueColor: AlwaysStoppedAnimation<Color>(
                              AppTheme.accentGold,
                            ),
                          ),
                        ),
                      )
                    else
                      GridView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: crossAxisCount,
                          crossAxisSpacing: spacing,
                          mainAxisSpacing: spacing,
                          childAspectRatio: aspectRatio,
                        ),
                        itemCount: 4,
                        itemBuilder: (context, index) {
                          final items = [
                            (
                              'Numéros utiles',
                              Icons.phone_in_talk_outlined,
                              'assets/images/info_urgence.png',
                              () => context.navigateTo(
                                GuideItemsScreen(
                                  category: usefulNumbersCategory,
                                ),
                              ),
                            ),
                            (
                              'Numéros urgence',
                              Icons.local_hospital_outlined,
                              'assets/images/info_urgence.png',
                              () => context.navigateTo(
                                GuideItemsScreen(
                                  category: emergencyNumbersCategory,
                                ),
                              ),
                            ),
                            (
                              'Pratique & Internet',
                              Icons.wifi_rounded,
                              'assets/images/info_pratique.png',
                              () => context.navigateTo(
                                GuideItemsScreen(
                                  category: practicalInternetCategory,
                                ),
                              ),
                            ),
                            (
                              'Découvrir le Sénégal',
                              Icons.travel_explore_outlined,
                              'assets/images/info_decouvrir.png',
                              () => context.navigateTo(
                                GuideItemsScreen(
                                  category: discoverSenegalCategory,
                                ),
                              ),
                            ),
                          ];
                          final (title, icon, imagePath, onTap) = items[index];

                          return ServiceCard(
                            title: title,
                            icon: icon,
                            imagePath: imagePath,
                            isLoading: _loadingGuides,
                            onTap: () {
                              HapticHelper.lightImpact();
                              onTap();
                            },
                          );
                        },
                      ),
                    const SizedBox(height: 12),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ContactTile extends StatelessWidget {
  final IconData icon;
  final String title;
  final String value;
  final VoidCallback? onTap;

  const _ContactTile({
    required this.icon,
    required this.title,
    required this.value,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Material(
        color: AppTheme.primaryDark.withValues(alpha: 0.35),
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(14),
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: AppTheme.primaryBlue.withValues(alpha: 0.35),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: AppTheme.accentGold.withValues(alpha: 0.25),
                    ),
                  ),
                  child: Icon(icon, color: AppTheme.accentGold),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        value,
                        style: const TextStyle(color: AppTheme.textGray),
                      ),
                    ],
                  ),
                ),
                if (onTap != null)
                  const Icon(Icons.chevron_right, color: AppTheme.textGray),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
