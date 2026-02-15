import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:file_picker/file_picker.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:flutter/services.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../utils/haptic_helper.dart';
import '../../models/chat_message.dart';
import '../../services/chat_api.dart';

/// Discussion avec l'équipe de l'hôtel (type messagerie interne).
class ChatbotScreen extends StatefulWidget {
  const ChatbotScreen({super.key});

  @override
  State<ChatbotScreen> createState() => _ChatbotScreenState();
}

class _ChatbotScreenState extends State<ChatbotScreen> {
  final ChatApi _api = ChatApi();
  final TextEditingController _controller = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  final List<ChatMessage> _messages = [];
  bool _loading = true;
  bool _sending = false;
  bool _pickingMedia = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadMessages();
  }

  Future<void> _loadMessages() async {
    try {
      final messages = await _api.getMessages();
      if (!mounted) return;
      setState(() {
        _messages
          ..clear()
          ..addAll(messages);
        _loading = false;
        _error = null;
      });
      await Future.delayed(const Duration(milliseconds: 100));
      _scrollToBottom();
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _loading = false;
        _error =
            'Impossible de charger la discussion pour le moment.\n'
            'Merci de vérifier la connexion internet ou de réessayer plus tard.';
      });
    }
  }

  Future<void> _sendMessage() async {
    final text = _controller.text.trim();
    if (text.isEmpty || _sending) return;
    HapticHelper.lightImpact();
    setState(() {
      _sending = true;
    });
    try {
      final msg = await _api.sendMessage(text);
      if (!mounted) return;
      setState(() {
        _messages.add(msg);
        _controller.clear();
        _sending = false;
      });
      _scrollToBottom();
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _sending = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Impossible d’envoyer le message.\n'
            'Merci de vérifier la connexion internet ou de réessayer plus tard.',
          ),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _pickAndSendImage() async {
    if (_sending || _pickingMedia) return;
    _pickingMedia = true;
    FilePickerResult? result;
    try {
      result = await FilePicker.platform.pickFiles(
        type: FileType.image,
        allowMultiple: false,
      );
    } on PlatformException catch (e) {
      if (e.code == 'multiple_request') {
        _pickingMedia = false;
        return;
      }
      _pickingMedia = false;
      rethrow;
    }
    _pickingMedia = false;
    if (result == null || result.files.isEmpty) return;
    final file = result.files.single;
    final path = file.path;
    if (path == null) return;

    HapticHelper.lightImpact();
    setState(() {
      _sending = true;
    });

    try {
      final msg = await _api.sendMediaMessage(
        filePath: path,
        fileName: file.name,
        messageType: 'image',
      );
      if (!mounted) return;
      setState(() {
        _messages.add(msg);
        _sending = false;
      });
      _scrollToBottom();
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _sending = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Impossible d’envoyer le média.\n'
            'Merci de vérifier la connexion internet ou de réessayer plus tard.',
          ),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _pickAndSendAudio() async {
    if (_sending || _pickingMedia) return;
    _pickingMedia = true;
    FilePickerResult? result;
    try {
      result = await FilePicker.platform.pickFiles(
        type: FileType.audio,
        allowMultiple: false,
      );
    } on PlatformException catch (e) {
      if (e.code == 'multiple_request') {
        _pickingMedia = false;
        return;
      }
      _pickingMedia = false;
      rethrow;
    }
    _pickingMedia = false;
    if (result == null || result.files.isEmpty) return;
    final file = result.files.single;
    final path = file.path;
    if (path == null) return;

    HapticHelper.lightImpact();
    setState(() {
      _sending = true;
    });

    try {
      final msg = await _api.sendMediaMessage(
        filePath: path,
        fileName: file.name,
        messageType: 'audio',
      );
      if (!mounted) return;
      setState(() {
        _messages.add(msg);
        _sending = false;
      });
      _scrollToBottom();
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _sending = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Impossible d’envoyer le message vocal.\n'
            'Merci de vérifier la connexion internet ou de réessayer plus tard.',
          ),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  void _scrollToBottom() {
    if (_scrollController.hasClients) {
      _scrollController.animateTo(
        _scrollController.position.maxScrollExtent,
        duration: const Duration(milliseconds: 250),
        curve: Curves.easeOut,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final user = context.watch<AuthProvider>().user;

    return Scaffold(
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [AppTheme.primaryDark, AppTheme.primaryBlue],
          ),
        ),
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
                        Navigator.pop(context);
                      },
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            l10n.chatbotMultilingual,
                            style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          Text(
                            l10n.chatbotDesc,
                            style: const TextStyle(
                              fontSize: 13,
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
                child: Container(
                  decoration: BoxDecoration(
                    color: AppTheme.primaryBlue.withValues(alpha: 0.4),
                    borderRadius: const BorderRadius.vertical(
                      top: Radius.circular(24),
                    ),
                  ),
                  child: Column(
                    children: [
                      if (_loading)
                        const Expanded(
                          child: Center(
                            child: CircularProgressIndicator(
                              valueColor: AlwaysStoppedAnimation<Color>(
                                AppTheme.accentGold,
                              ),
                            ),
                          ),
                        )
                      else if (_error != null)
                        Expanded(
                          child: Center(
                            child: Padding(
                              padding: const EdgeInsets.all(24),
                              child: Text(
                                _error!,
                                style: const TextStyle(
                                  color: AppTheme.textGray,
                                ),
                                textAlign: TextAlign.center,
                              ),
                            ),
                          ),
                        )
                      else
                        Expanded(
                          child: ListView.builder(
                            controller: _scrollController,
                            padding: const EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 12,
                            ),
                            itemCount: _messages.length,
                            itemBuilder: (context, index) {
                              final msg = _messages[index];
                              final isMe = msg.senderType == 'guest';
                              final guestName = user?.name.trim();
                              return _buildMessageBubble(
                                context,
                                msg,
                                isMe,
                                guestName?.isNotEmpty == true
                                    ? guestName!
                                    : 'Vous',
                              );
                            },
                          ),
                        ),
                      _buildInputBar(context, user?.name ?? 'Vous'),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMessageBubble(
    BuildContext context,
    ChatMessage message,
    bool isMe,
    String guestDisplayName,
  ) {
    final authorLabel = isMe ? guestDisplayName : 'Assistant de l’hôtel';
    final time = DateFormat.Hm().format(message.createdAt.toLocal());
    return Align(
      alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 4),
        padding: const EdgeInsets.all(12),
        constraints: const BoxConstraints(maxWidth: 480),
        decoration: BoxDecoration(
          color: isMe
              ? AppTheme.accentGold
              : AppTheme.primaryBlue.withValues(alpha: 0.8),
          borderRadius: BorderRadius.circular(16).copyWith(
            bottomLeft: Radius.circular(isMe ? 16 : 4),
            bottomRight: Radius.circular(isMe ? 4 : 16),
          ),
        ),
        child: Column(
          crossAxisAlignment: isMe
              ? CrossAxisAlignment.end
              : CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(
                color: isMe
                    ? Colors.white.withValues(alpha: 0.9)
                    : Colors.white.withValues(alpha: 0.18),
                borderRadius: BorderRadius.circular(999),
              ),
              child: Text(
                authorLabel,
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: isMe ? AppTheme.primaryDark : Colors.white,
                ),
              ),
            ),
            const SizedBox(height: 4),
            _buildMessageContent(message, isMe),
            const SizedBox(height: 4),
            Align(
              alignment: Alignment.bottomRight,
              child: Text(
                time,
                style: TextStyle(
                  color: isMe
                      ? AppTheme.primaryDark.withValues(alpha: 0.8)
                      : Colors.white.withValues(alpha: 0.8),
                  fontSize: 13,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMessageContent(ChatMessage message, bool isMe) {
    if (message.messageType == 'image') {
      final imageUrl = message.metadata?['url'] as String?;
      if (imageUrl == null || imageUrl.isEmpty) {
        if (message.content != null && message.content!.isNotEmpty) {
          return Text(
            message.content!,
            style: TextStyle(
              color: isMe ? AppTheme.primaryDark : Colors.white,
              fontSize: 18,
              height: 1.3,
            ),
          );
        }
        return const SizedBox.shrink();
      }
      return Column(
        crossAxisAlignment: isMe
            ? CrossAxisAlignment.end
            : CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          GestureDetector(
            onTap: () => _openMediaUrl(imageUrl),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Image.network(imageUrl, width: 260, fit: BoxFit.cover),
            ),
          ),
          if (message.content != null && message.content!.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 6),
              child: Text(
                message.content!,
                style: TextStyle(
                  color: isMe ? AppTheme.primaryDark : Colors.white,
                  fontSize: 16,
                  height: 1.3,
                ),
              ),
            ),
        ],
      );
    }

    if (message.messageType == 'audio') {
      final audioUrl = message.metadata?['url'] as String?;
      final durationSeconds = message.metadata?['duration'] as int?;
      final label = durationSeconds != null && durationSeconds > 0
          ? 'Message vocal (${_formatDuration(durationSeconds)})'
          : 'Message vocal';

      if (audioUrl == null || audioUrl.isEmpty) {
        return Text(
          label,
          style: TextStyle(
            color: isMe ? AppTheme.primaryDark : Colors.white,
            fontSize: 16,
          ),
        );
      }

      return InkWell(
        onTap: () => _openMediaUrl(audioUrl),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
          decoration: BoxDecoration(
            color: isMe
                ? AppTheme.primaryDark.withValues(alpha: 0.08)
                : Colors.black.withValues(alpha: 0.18),
            borderRadius: BorderRadius.circular(999),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                Icons.play_arrow_rounded,
                color: isMe ? AppTheme.primaryDark : Colors.white,
              ),
              const SizedBox(width: 6),
              Text(
                label,
                style: TextStyle(
                  color: isMe ? AppTheme.primaryDark : Colors.white,
                  fontSize: 15,
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (message.content != null && message.content!.isNotEmpty) {
      return Text(
        message.content!,
        style: TextStyle(
          color: isMe ? AppTheme.primaryDark : Colors.white,
          fontSize: 18,
          height: 1.3,
        ),
      );
    }

    return const SizedBox.shrink();
  }

  Future<void> _openMediaUrl(String url) async {
    final uri = Uri.tryParse(url);
    if (uri == null) {
      return;
    }
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  String _formatDuration(int seconds) {
    final clamped = seconds < 0 ? 0 : seconds;
    final minutes = clamped ~/ 60;
    final remainingSeconds = clamped % 60;
    final twoDigits = remainingSeconds.toString().padLeft(2, '0');
    return '$minutes:$twoDigits';
  }

  Widget _buildInputBar(BuildContext context, String displayName) {
    final l10n = AppLocalizations.of(context);
    return SafeArea(
      top: false,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(12, 8, 12, 12),
        child: Row(
          children: [
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                IconButton(
                  onPressed: _sending ? null : _pickAndSendImage,
                  icon: Icon(
                    Icons.photo_outlined,
                    color: _sending ? AppTheme.textGray : AppTheme.accentGold,
                  ),
                ),
                IconButton(
                  onPressed: _sending ? null : _pickAndSendAudio,
                  icon: Icon(
                    Icons.mic_none_rounded,
                    color: _sending ? AppTheme.textGray : AppTheme.accentGold,
                  ),
                ),
              ],
            ),
            const SizedBox(width: 4),
            Expanded(
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12),
                decoration: BoxDecoration(
                  color: AppTheme.primaryDark.withValues(alpha: 0.7),
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(
                    color: AppTheme.accentGold.withValues(alpha: 0.4),
                  ),
                ),
                child: TextField(
                  controller: _controller,
                  maxLines: 4,
                  minLines: 1,
                  style: const TextStyle(color: Colors.white),
                  decoration: InputDecoration(
                    hintText: l10n.chatbotDesc,
                    hintStyle: const TextStyle(
                      color: AppTheme.textGray,
                      fontSize: 14,
                    ),
                    border: InputBorder.none,
                  ),
                  onSubmitted: (_) => _sendMessage(),
                ),
              ),
            ),
            const SizedBox(width: 8),
            IconButton(
              onPressed: _sending ? null : _sendMessage,
              icon: Icon(
                Icons.send_rounded,
                color: _sending ? AppTheme.textGray : AppTheme.accentGold,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
