import 'dart:io';

bool get isAndroid => Platform.isAndroid;

bool get isFlutterTest => Platform.environment.containsKey('FLUTTER_TEST');
