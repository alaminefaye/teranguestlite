import 'package:flutter/material.dart';

class ServiceItem {
  final String id;
  final String title;
  final IconData icon;
  final String route;

  ServiceItem({
    required this.id,
    required this.title,
    required this.icon,
    required this.route,
  });
}
