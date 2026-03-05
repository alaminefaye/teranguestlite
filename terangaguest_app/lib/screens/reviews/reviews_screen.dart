import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../services/reviews_api.dart';
import '../../utils/haptic_helper.dart';
import '../../widgets/animated_button.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';

class ReviewsScreen extends StatefulWidget {
  const ReviewsScreen({super.key});

  @override
  State<ReviewsScreen> createState() => _ReviewsScreenState();
}

class _ReviewsScreenState extends State<ReviewsScreen> {
  final ReviewsApi _api = ReviewsApi();
  List<dynamic> _pending = [];
  List<dynamic> _myReviews = [];
  bool _loading = true;
  bool _loadingMy = false;
  String? _error;
  int _selectedTab = 0; // 0 = À noter, 1 = Mes avis

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final pending = await _api.getPending();
      if (mounted) {
        setState(() {
          _pending = pending;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString();
          _loading = false;
        });
      }
    }
  }

  Future<void> _loadMyReviews() async {
    setState(() => _loadingMy = true);
    try {
      final result = await _api.getMyReviews();
      if (mounted) {
        setState(() {
          _myReviews = result['items'] as List<dynamic>? ?? [];
          _loadingMy = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loadingMy = false);
      }
    }
  }

  Future<void> _submitReview(PendingReviewItem item, int rating, String? comment) async {
    try {
      await _api.submitReview(
        reviewableType: item.reviewableType,
        reviewableId: item.reviewableId,
        rating: rating,
        comment: comment,
      );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(AppLocalizations.of(context).thankYouForReview),
            backgroundColor: Colors.green,
          ),
        );
        _load();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(e.toString()),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  void _openReviewDialog(PendingReviewItem item) {
    HapticHelper.lightImpact();
    int selectedRating = 0;
    final commentController = TextEditingController();

    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) {
          return Container(
            padding: EdgeInsets.only(
              left: 24,
              right: 24,
              top: 24,
              bottom: MediaQuery.of(context).viewInsets.bottom + 24,
            ),
            decoration: BoxDecoration(
              color: AppTheme.primaryBlue,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
              border: Border.all(color: AppTheme.accentGold, width: 1),
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Text(
                  item.label,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.accentGold,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 16),
                Text(
                  AppLocalizations.of(context).rateYourExperience,
                  style: const TextStyle(color: AppTheme.textGray, fontSize: 14),
                ),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: List.generate(5, (i) {
                    final star = i + 1;
                    return IconButton(
                      icon: Icon(
                        star <= selectedRating ? Icons.star : Icons.star_border,
                        color: AppTheme.accentGold,
                        size: 36,
                      ),
                      onPressed: () => setModalState(() => selectedRating = star),
                    );
                  }),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: commentController,
                  maxLines: 3,
                  maxLength: 500,
                  style: const TextStyle(color: Colors.white),
                  decoration: InputDecoration(
                    hintText: AppLocalizations.of(context).optionalComment,
                    hintStyle: TextStyle(color: AppTheme.textGray.withValues(alpha: 0.8)),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: AppTheme.accentGold),
                    ),
                  ),
                ),
                const SizedBox(height: 20),
                AnimatedButton(
                  text: AppLocalizations.of(context).submit,
                  onPressed: selectedRating == 0
                      ? null
                      : () async {
                          Navigator.pop(context);
                          await _submitReview(
                            item,
                            selectedRating,
                            commentController.text.trim().isEmpty
                                ? null
                                : commentController.text.trim(),
                          );
                        },
                  width: double.infinity,
                  height: 48,
                  backgroundColor: AppTheme.accentGold,
                  textColor: AppTheme.primaryDark,
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
                      onPressed: () {
                        HapticHelper.lightImpact();
                        Navigator.pop(context);
                      },
                    ),
                    const SizedBox(width: 8),
                    Text(
                      l10n.reviewsTitle,
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.accentGold,
                      ),
                    ),
                  ],
                ),
              ),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Row(
                  children: [
                    _tab(l10n.reviewsPending, 0),
                    const SizedBox(width: 12),
                    _tab(l10n.reviewsMyReviews, 1),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              Expanded(
                child: _selectedTab == 0 ? _buildPendingList(l10n) : _buildMyReviewsList(l10n),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _tab(String label, int index) {
    final selected = _selectedTab == index;
    return GestureDetector(
      onTap: () {
        HapticHelper.lightImpact();
        setState(() {
          _selectedTab = index;
          if (index == 1 && _myReviews.isEmpty) _loadMyReviews();
        });
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          color: selected ? AppTheme.accentGold : Colors.transparent,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: AppTheme.accentGold, width: 1),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: selected ? AppTheme.primaryDark : AppTheme.accentGold,
          ),
        ),
      ),
    );
  }

  Widget _buildPendingList(AppLocalizations l10n) {
    if (_loading) {
      return const Center(
        child: CircularProgressIndicator(
          valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
        ),
      );
    }
    if (_error != null) {
      return ErrorStateWidget(
        message: _error!,
        onRetry: _load,
      );
    }
    if (_pending.isEmpty) {
      return EmptyStateWidget(
        icon: Icons.rate_review_outlined,
        title: l10n.reviewsNoPending,
        subtitle: l10n.reviewsNoPendingHint,
      );
    }
    return RefreshIndicator(
      onRefresh: _load,
      color: AppTheme.accentGold,
      child: ListView.builder(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        itemCount: _pending.length,
        itemBuilder: (context, i) {
          final item = _pending[i] as PendingReviewItem;
          return Card(
            margin: const EdgeInsets.only(bottom: 12),
            color: AppTheme.primaryBlue.withValues(alpha: 0.6),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
              side: const BorderSide(color: AppTheme.accentGold, width: 1),
            ),
            child: ListTile(
              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              leading: const CircleAvatar(
                backgroundColor: AppTheme.accentGold,
                child: Icon(Icons.rate_review, color: AppTheme.primaryDark),
              ),
              title: Text(
                item.label,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                  fontSize: 15,
                ),
              ),
              trailing: const Icon(Icons.chevron_right, color: AppTheme.accentGold),
              onTap: () => _openReviewDialog(item),
            ),
          );
        },
      ),
    );
  }

  Widget _buildMyReviewsList(AppLocalizations l10n) {
    if (_loadingMy && _myReviews.isEmpty) {
      return const Center(
        child: CircularProgressIndicator(
          valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
        ),
      );
    }
    if (_myReviews.isEmpty) {
      return EmptyStateWidget(
        icon: Icons.rate_review_outlined,
        title: l10n.reviewsNoReviewsYet,
        subtitle: l10n.reviewsNoReviewsYetHint,
      );
    }
    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      itemCount: _myReviews.length,
      itemBuilder: (context, i) {
        final r = _myReviews[i] as GuestReviewItem;
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          color: AppTheme.primaryBlue.withValues(alpha: 0.6),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
            side: const BorderSide(color: AppTheme.accentGold, width: 1),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        r.label,
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w600,
                          fontSize: 15,
                        ),
                      ),
                    ),
                    ...List.generate(5, (j) => Icon(
                          j < r.rating ? Icons.star : Icons.star_border,
                          color: AppTheme.accentGold,
                          size: 18,
                        )),
                  ],
                ),
                if (r.comment != null && r.comment!.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  Text(
                    r.comment!,
                    style: const TextStyle(color: AppTheme.textGray, fontSize: 13),
                    maxLines: 3,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
                const SizedBox(height: 6),
                Text(
                  _formatDate(r.createdAt),
                  style: TextStyle(
                    color: AppTheme.textGray.withValues(alpha: 0.8),
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  String _formatDate(DateTime d) {
    return '${d.day.toString().padLeft(2, '0')}/${d.month.toString().padLeft(2, '0')}/${d.year}';
  }
}
