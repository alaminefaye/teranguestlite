import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import 'api_service.dart';

class PendingReviewItem {
  final String reviewableType;
  final int reviewableId;
  final String label;
  final String? completedAt;

  PendingReviewItem({
    required this.reviewableType,
    required this.reviewableId,
    required this.label,
    this.completedAt,
  });

  factory PendingReviewItem.fromJson(Map<String, dynamic> json) {
    return PendingReviewItem(
      reviewableType: json['reviewable_type'] as String? ?? '',
      reviewableId: (json['reviewable_id'] as num?)?.toInt() ?? 0,
      label: json['label'] as String? ?? '',
      completedAt: json['completed_at'] as String?,
    );
  }
}

class GuestReviewItem {
  final int id;
  final String reviewableType;
  final int reviewableId;
  final String label;
  final int rating;
  final String? comment;
  final DateTime createdAt;

  GuestReviewItem({
    required this.id,
    required this.reviewableType,
    required this.reviewableId,
    required this.label,
    required this.rating,
    this.comment,
    required this.createdAt,
  });

  factory GuestReviewItem.fromJson(Map<String, dynamic> json) {
    final createdAtStr = json['created_at'] as String?;
    return GuestReviewItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      reviewableType: json['reviewable_type'] as String? ?? '',
      reviewableId: (json['reviewable_id'] as num?)?.toInt() ?? 0,
      label: json['label'] as String? ?? '',
      rating: (json['rating'] as num?)?.toInt() ?? 0,
      comment: json['comment'] as String?,
      createdAt: createdAtStr != null
          ? (DateTime.tryParse(createdAtStr) ?? DateTime.now())
          : DateTime.now(),
    );
  }
}

class ReviewsApi {
  final ApiService _apiService = ApiService();

  Future<List<PendingReviewItem>> getPending() async {
    try {
      final response = await _apiService.get(ApiConfig.reviewsPending);
      final data = response.data as Map<String, dynamic>?;
      final list = data?['data'] as List? ?? [];
      return list
          .map((e) => PendingReviewItem.fromJson(e as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ Reviews API Error: $e');
      rethrow;
    }
  }

  Future<void> submitReview({
    required String reviewableType,
    required int reviewableId,
    required int rating,
    String? comment,
  }) async {
    try {
      await _apiService.post(
        ApiConfig.reviews,
        data: <String, dynamic>{
          'reviewable_type': reviewableType,
          'reviewable_id': reviewableId,
          'rating': rating,
          if (comment != null && comment.trim().isNotEmpty) 'comment': comment.trim(),
        },
      );
    } on DioException catch (e) {
      debugPrint('❌ Reviews API Error: $e');
      rethrow;
    }
  }

  Future<Map<String, dynamic>> getMyReviews({int page = 1, int perPage = 15}) async {
    try {
      final response = await _apiService.get(
        ApiConfig.reviews,
        queryParameters: <String, dynamic>{'page': page, 'per_page': perPage},
      );
      final data = response.data as Map<String, dynamic>? ?? {};
      final list = data['data'] as List? ?? [];
      final meta = data['meta'] as Map<String, dynamic>? ?? {};
      final items = list
          .map((e) => GuestReviewItem.fromJson(e as Map<String, dynamic>))
          .toList();
      return {'items': items, 'meta': meta};
    } on DioException catch (e) {
      debugPrint('❌ Reviews API Error: $e');
      rethrow;
    }
  }
}
