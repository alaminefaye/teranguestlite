import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../widgets/service_card.dart';
import 'hotel_infos_screen.dart';
import 'assistance_emergency_screen.dart';
import 'chatbot_screen.dart';

/// Hub « HOTEL INFOS & SÉCURITÉ » : Hôtel Infos, Assistance & Urgence, Chatbot.
class HotelInfosSecurityScreen extends StatefulWidget {
  const HotelInfosSecurityScreen({super.key});

  @override
  State<HotelInfosSecurityScreen> createState() => _HotelInfosSecurityScreenState();
}

class _HotelInfosSecurityScreenState extends State<HotelInfosSecurityScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AuthProvider>().loadUser();
    });
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);

    final subServices = [
      (l10n.hotelInfos, Icons.info_outline, () {
        HapticHelper.lightImpact();
        context.navigateTo(const HotelInfosScreen());
      }),
      (l10n.assistanceEmergency, Icons.emergency_outlined, () {
        HapticHelper.lightImpact();
        context.navigateTo(const AssistanceEmergencyScreen());
      }),
      (l10n.chatbotMultilingual, Icons.smart_toy_outlined, () {
        HapticHelper.lightImpact();
        context.navigateTo(const ChatbotScreen());
      }),
    ];

    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final spacing = LayoutHelper.gridSpacing(context);

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: AppTheme.backgroundGradient,
        ),
        child: SafeArea(
          child: Column(
            children: [
              _buildAppBar(context, l10n.hotelInfosSecurity, l10n.hotelInfosSecuritySubtitle),
              Expanded(
                child: Padding(
                  padding: LayoutHelper.horizontalPadding(context),
                  child: GridView.builder(
                    padding: EdgeInsets.symmetric(vertical: spacing),
                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: crossAxisCount,
                      crossAxisSpacing: spacing,
                      mainAxisSpacing: spacing,
                      childAspectRatio: aspectRatio,
                    ),
                    itemCount: subServices.length,
                    itemBuilder: (context, index) {
                      final (title, icon, onTap) = subServices[index];
                      return ServiceCard(
                        title: title,
                        icon: icon,
                        onTap: onTap,
                      );
                    },
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAppBar(BuildContext context, String title, String subtitle) {
    return Padding(
      padding: const EdgeInsets.all(20.0),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
            onPressed: () {
              HapticHelper.lightImpact();
              Navigator.of(context).pop();
            },
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontSize: MediaQuery.of(context).size.width < 600 ? 18 : 28,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentGold,
                  ),
                ),
                if (subtitle.isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Text(
                    subtitle,
                    style: const TextStyle(
                      fontSize: 14,
                      color: AppTheme.textGray,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
