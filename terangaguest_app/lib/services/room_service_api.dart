import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/menu_category.dart';
import '../models/menu_item.dart';
import 'api_service.dart';

class RoomServiceApi {
  final ApiService _apiService = ApiService();

  // Récupérer toutes les catégories
  Future<List<MenuCategory>> getCategories({
    bool? available,
    String? search,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (available != null) queryParams['available'] = available ? 1 : 0;
      if (search != null && search.isNotEmpty) queryParams['search'] = search;

      final response = await _apiService.get(
        ApiConfig.roomServiceCategories,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200) {
        final data = response.data;
        if (data['success'] == true) {
          final List categoriesJson = data['data'] as List;
          return categoriesJson
              .map((json) => MenuCategory.fromJson(json))
              .toList();
        } else {
          throw Exception(
            data['message'] ?? 'Erreur lors de la récupération des catégories',
          );
        }
      } else {
        throw Exception('Erreur serveur: ${response.statusCode}');
      }
    } on DioException catch (e) {
      if (e.response != null) {
        throw Exception(e.response?.data['message'] ?? 'Erreur réseau');
      } else {
        throw Exception('Impossible de se connecter au serveur');
      }
    }
  }

  // Récupérer les articles de menu
  Future<Map<String, dynamic>> getItems({
    int? categoryId,
    bool? available,
    String? search,
    int page = 1,
    int perPage = 15,
  }) async {
    try {
      final queryParams = <String, dynamic>{'page': page, 'per_page': perPage};

      if (categoryId != null) queryParams['category_id'] = categoryId;
      if (available != null) queryParams['available'] = available ? 1 : 0;
      if (search != null && search.isNotEmpty) queryParams['search'] = search;

      final response = await _apiService.get(
        ApiConfig.roomServiceItems,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200) {
        final data = response.data;
        if (data['success'] == true) {
          final List itemsJson = data['data'] as List;
          final items = itemsJson
              .map((json) => MenuItem.fromJson(json))
              .toList();

          return {'items': items, 'meta': data['meta'] ?? {}};
        } else {
          throw Exception(
            data['message'] ?? 'Erreur lors de la récupération des articles',
          );
        }
      } else {
        throw Exception('Erreur serveur: ${response.statusCode}');
      }
    } on DioException catch (e) {
      if (e.response != null) {
        throw Exception(e.response?.data['message'] ?? 'Erreur réseau');
      } else {
        throw Exception('Impossible de se connecter au serveur');
      }
    }
  }

  // Récupérer le détail d'un article
  Future<MenuItem> getItemDetails(int itemId) async {
    try {
      final response = await _apiService.get(
        '${ApiConfig.roomServiceItems}/$itemId',
      );

      if (response.statusCode == 200) {
        final data = response.data;
        if (data['success'] == true) {
          return MenuItem.fromJson(data['data']);
        } else {
          throw Exception(
            data['message'] ?? 'Erreur lors de la récupération du détail',
          );
        }
      } else {
        throw Exception('Erreur serveur: ${response.statusCode}');
      }
    } on DioException catch (e) {
      if (e.response != null) {
        throw Exception(e.response?.data['message'] ?? 'Erreur réseau');
      } else {
        throw Exception('Impossible de se connecter au serveur');
      }
    }
  }

  // Passer une commande (checkout)
  Future<Map<String, dynamic>> checkout({
    required List<Map<String, dynamic>> items,
    String? specialInstructions,
    String? deliveryTime,
  }) async {
    try {
      final requestData = {
        'items': items,
        if (specialInstructions != null && specialInstructions.isNotEmpty)
          'special_instructions': specialInstructions,
        ...?(deliveryTime != null ? {'delivery_time': deliveryTime} : null),
      };

      final response = await _apiService.post(
        ApiConfig.roomServiceCheckout,
        data: requestData,
      );

      if (response.statusCode == 201 || response.statusCode == 200) {
        final data = response.data;
        if (data['success'] == true) {
          return data['data'];
        } else {
          throw Exception(data['message'] ?? 'Erreur lors de la commande');
        }
      } else {
        throw Exception('Erreur serveur: ${response.statusCode}');
      }
    } on DioException catch (e) {
      if (e.response != null) {
        final errorData = e.response?.data;
        final statusCode = e.response?.statusCode;
        if (errorData is Map && errorData['message'] != null) {
          final msg = errorData['message'];
          if (msg is String && msg.trim().isNotEmpty) {
            throw Exception(msg);
          }
        }
        if (statusCode == 403) {
          throw Exception(
            'Accès refusé. Utilisez le code client de la chambre pour valider la commande depuis cette tablette.',
          );
        }
        if (statusCode == 401) {
          throw Exception(
            'Session expirée. Reconnectez-vous ou entrez votre code client.',
          );
        }
        if (errorData is Map && errorData.containsKey('errors')) {
          final errors = errorData['errors'] as Map<String, dynamic>;
          final firstError = errors.values.first;
          if (firstError is List && firstError.isNotEmpty) {
            throw Exception(firstError.first.toString());
          }
        }
        throw Exception(
          'Erreur lors de la commande. Réessayez ou contactez la réception.',
        );
      } else {
        throw Exception(
          'Impossible de se connecter au serveur. Vérifiez votre connexion.',
        );
      }
    }
  }
}
