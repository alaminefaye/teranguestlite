import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import '../models/favorite_item.dart';

const String _keyFavorites = 'favorites_list';

class FavoritesProvider with ChangeNotifier {
  List<FavoriteItem> _items = [];
  bool _loaded = false;

  List<FavoriteItem> get items => List.unmodifiable(_items);
  bool get isLoaded => _loaded;

  Future<void> load() async {
    if (_loaded) return;
    try {
      final prefs = await SharedPreferences.getInstance();
      final raw = prefs.getString(_keyFavorites);
      if (raw == null || raw.isEmpty) {
        _items = [];
      } else {
        final list = jsonDecode(raw) as List<dynamic>?;
        _items = list
                ?.map((e) => FavoriteItem.fromJson(e as Map<String, dynamic>))
                .toList() ??
            [];
      }
      _loaded = true;
      notifyListeners();
    } catch (e) {
      debugPrint('FavoritesProvider.load error: $e');
      _items = [];
      _loaded = true;
      notifyListeners();
    }
  }

  Future<void> _save() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final list = _items.map((e) => e.toJson()).toList();
      await prefs.setString(_keyFavorites, jsonEncode(list));
    } catch (e) {
      debugPrint('FavoritesProvider._save error: $e');
    }
  }

  bool isFavorite(FavoriteType type, int id) {
    return _items.any((e) => e.type == type && e.id == id);
  }

  Future<void> add(FavoriteItem item) async {
    if (isFavorite(item.type, item.id)) return;
    _items.add(item);
    await _save();
    notifyListeners();
  }

  Future<void> remove(FavoriteType type, int id) async {
    _items.removeWhere((e) => e.type == type && e.id == id);
    await _save();
    notifyListeners();
  }

  Future<void> toggle(FavoriteItem item) async {
    if (isFavorite(item.type, item.id)) {
      await remove(item.type, item.id);
    } else {
      await add(item);
    }
  }
}
