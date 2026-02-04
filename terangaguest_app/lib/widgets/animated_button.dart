import 'package:flutter/material.dart';
import '../config/theme.dart';
import '../utils/haptic_helper.dart';

/// Bouton avec animations et feedback haptique
class AnimatedButton extends StatefulWidget {
  final String text;
  final VoidCallback? onPressed;
  final IconData? icon;
  final Color? backgroundColor;
  final Color? textColor;
  final double? width;
  final double height;
  final bool isLoading;
  final bool enableHaptic;

  const AnimatedButton({
    super.key,
    required this.text,
    this.onPressed,
    this.icon,
    this.backgroundColor,
    this.textColor,
    this.width,
    this.height = 56,
    this.isLoading = false,
    this.enableHaptic = true,
  });

  @override
  State<AnimatedButton> createState() => _AnimatedButtonState();
}

class _AnimatedButtonState extends State<AnimatedButton>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _scaleAnimation;
  bool _isPressed = false;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      duration: const Duration(milliseconds: 100),
      vsync: this,
    );

    _scaleAnimation = Tween<double>(begin: 1.0, end: 0.95).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _handleTapDown(TapDownDetails details) {
    if (widget.onPressed != null && !widget.isLoading) {
      setState(() => _isPressed = true);
      _controller.forward();
      if (widget.enableHaptic) {
        HapticHelper.lightImpact();
      }
    }
  }

  void _handleTapUp(TapUpDetails details) {
    if (_isPressed) {
      setState(() => _isPressed = false);
      _controller.reverse();
    }
  }

  void _handleTapCancel() {
    if (_isPressed) {
      setState(() => _isPressed = false);
      _controller.reverse();
    }
  }

  @override
  Widget build(BuildContext context) {
    final backgroundColor = widget.backgroundColor ?? AppTheme.accentGold;
    final textColor = widget.textColor ?? AppTheme.primaryDark;
    final isDisabled = widget.onPressed == null || widget.isLoading;

    return Semantics(
      button: true,
      label: widget.text,
      enabled: widget.onPressed != null && !widget.isLoading,
      child: GestureDetector(
        onTapDown: _handleTapDown,
        onTapUp: _handleTapUp,
        onTapCancel: _handleTapCancel,
        onTap: widget.onPressed != null && !widget.isLoading
          ? () {
              if (widget.enableHaptic) {
                HapticHelper.lightImpact();
              }
              widget.onPressed!();
            }
          : null,
      child: AnimatedBuilder(
        animation: _scaleAnimation,
        builder: (context, child) {
          return Transform.scale(
            scale: _scaleAnimation.value,
            child: Container(
              width: widget.width,
              height: widget.height,
              decoration: BoxDecoration(
                gradient: isDisabled
                    ? LinearGradient(
                        colors: [
                          AppTheme.textGray.withValues(alpha: 0.3),
                          AppTheme.textGray.withValues(alpha: 0.2),
                        ],
                      )
                    : LinearGradient(
                        colors: [
                          backgroundColor,
                          backgroundColor.withValues(alpha: 0.8),
                        ],
                      ),
                borderRadius: BorderRadius.circular(16),
                boxShadow: isDisabled
                    ? []
                    : [
                        BoxShadow(
                          color: backgroundColor.withValues(alpha: 0.3),
                          blurRadius: 8,
                          offset: const Offset(0, 4),
                        ),
                      ],
              ),
              child: Center(
                child: widget.isLoading
                    ? SizedBox(
                        width: 24,
                        height: 24,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(textColor),
                        ),
                      )
                    : Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          if (widget.icon != null) ...[
                            Icon(
                              widget.icon,
                              color: textColor,
                              size: 20,
                            ),
                            const SizedBox(width: 12),
                          ],
                          Text(
                            widget.text,
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: textColor,
                            ),
                          ),
                        ],
                      ),
              ),
            ),
          );
        },
      ),
      ),
    );
  }
}

/// Bouton outline avec animations
class AnimatedOutlineButton extends StatefulWidget {
  final String text;
  final VoidCallback? onPressed;
  final IconData? icon;
  final Color? borderColor;
  final Color? textColor;
  final double? width;
  final double height;
  final bool enableHaptic;

  const AnimatedOutlineButton({
    super.key,
    required this.text,
    this.onPressed,
    this.icon,
    this.borderColor,
    this.textColor,
    this.width,
    this.height = 56,
    this.enableHaptic = true,
  });

  @override
  State<AnimatedOutlineButton> createState() => _AnimatedOutlineButtonState();
}

class _AnimatedOutlineButtonState extends State<AnimatedOutlineButton>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _scaleAnimation;
  bool _isPressed = false;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      duration: const Duration(milliseconds: 100),
      vsync: this,
    );

    _scaleAnimation = Tween<double>(begin: 1.0, end: 0.95).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _handleTapDown(TapDownDetails details) {
    if (widget.onPressed != null) {
      setState(() => _isPressed = true);
      _controller.forward();
      if (widget.enableHaptic) {
        HapticHelper.lightImpact();
      }
    }
  }

  void _handleTapUp(TapUpDetails details) {
    if (_isPressed) {
      setState(() => _isPressed = false);
      _controller.reverse();
    }
  }

  void _handleTapCancel() {
    if (_isPressed) {
      setState(() => _isPressed = false);
      _controller.reverse();
    }
  }

  @override
  Widget build(BuildContext context) {
    final borderColor = widget.borderColor ?? AppTheme.accentGold;
    final textColor = widget.textColor ?? AppTheme.accentGold;
    final isDisabled = widget.onPressed == null;

    return Semantics(
      button: true,
      label: widget.text,
      enabled: widget.onPressed != null,
      child: GestureDetector(
        onTapDown: _handleTapDown,
        onTapUp: _handleTapUp,
        onTapCancel: _handleTapCancel,
        onTap: widget.onPressed != null
            ? () {
                if (widget.enableHaptic) {
                  HapticHelper.lightImpact();
                }
                widget.onPressed!();
              }
            : null,
        child: AnimatedBuilder(
          animation: _scaleAnimation,
          builder: (context, child) {
            return Transform.scale(
              scale: _scaleAnimation.value,
              child: Container(
                width: widget.width,
                height: widget.height,
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: isDisabled
                        ? AppTheme.textGray.withValues(alpha: 0.3)
                        : borderColor,
                    width: 1.5,
                  ),
                ),
                child: Center(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      if (widget.icon != null) ...[
                        Icon(
                          widget.icon,
                          color: isDisabled
                              ? AppTheme.textGray.withValues(alpha: 0.3)
                              : textColor,
                          size: 20,
                        ),
                        const SizedBox(width: 12),
                      ],
                      Text(
                        widget.text,
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: isDisabled
                              ? AppTheme.textGray.withValues(alpha: 0.3)
                              : textColor,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}
