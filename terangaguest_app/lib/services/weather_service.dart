import 'package:flutter/foundation.dart';
import 'package:geolocator/geolocator.dart';
import 'package:weather/weather.dart';

class WeatherService {
  // Clé API OpenWeatherMap - Obtenir une clé gratuite sur https://openweathermap.org/api
  static const String _apiKey = 'YOUR_API_KEY_HERE';
  static const String _placeholder = 'YOUR_API_KEY_HERE';
  late WeatherFactory _weatherFactory;

  WeatherService() {
    _weatherFactory = WeatherFactory(_apiKey);
  }

  bool get _hasValidKey =>
      _apiKey.isNotEmpty && _apiKey != _placeholder;

  // Obtenir la position actuelle
  Future<Position> _getCurrentPosition() async {
    bool serviceEnabled;
    LocationPermission permission;

    // Vérifier si les services de localisation sont activés
    serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      throw Exception('Les services de localisation sont désactivés.');
    }

    permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) {
        throw Exception('Permissions de localisation refusées');
      }
    }

    if (permission == LocationPermission.deniedForever) {
      throw Exception('Les permissions de localisation sont définitivement refusées');
    }

    return await Geolocator.getCurrentPosition();
  }

  // Obtenir la météo actuelle
  Future<Weather?> getCurrentWeather() async {
    if (!_hasValidKey) {
      if (kDebugMode) {
        debugPrint('Météo: clé API OpenWeather non configurée (weather_service.dart)');
      }
      return null;
    }
    try {
      Position position = await _getCurrentPosition();
      Weather weather = await _weatherFactory.currentWeatherByLocation(
        position.latitude,
        position.longitude,
      );
      return weather;
    } catch (e) {
      final msg = e.toString();
      if (kDebugMode) {
        if (msg.contains('401') || msg.contains('Invalid API key')) {
          debugPrint('Météo: clé API OpenWeather invalide ou expirée (openweathermap.org)');
        } else {
          debugPrint('Erreur météo: $e');
        }
      }
      return null;
    }
  }

  // Obtenir l'icône météo Flutter correspondante
  static String getWeatherIcon(String? weatherMain) {
    if (weatherMain == null) return '☁️';
    
    switch (weatherMain.toLowerCase()) {
      case 'clear':
        return '☀️';
      case 'clouds':
        return '☁️';
      case 'rain':
      case 'drizzle':
        return '🌧️';
      case 'thunderstorm':
        return '⛈️';
      case 'snow':
        return '❄️';
      case 'mist':
      case 'fog':
        return '🌫️';
      default:
        return '☁️';
    }
  }
}
