import 'dart:async';

import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:file_picker/file_picker.dart';
import 'package:path_provider/path_provider.dart';
import 'package:record/record.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/chat_message.dart';
import '../../services/chat_api.dart';
import '../../utils/haptic_helper.dart';

class ChatbotScreen extends StatefulWidget {
  const ChatbotScreen({super.key});

  @override
  State<ChatbotScreen> createState() => _ChatbotScreenState();
}

class _ChatbotScreenState extends State<ChatbotScreen> {
  final ChatApi _api = ChatApi();
  final TextEditingController _controller = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  final AudioRecorder _audioRecorder = AudioRecorder();
  bool _loading = true;
  bool _sending = false;
  bool _sendingMedia = false;
  bool _recording = false;
  DateTime? _recordStartAt;
  Timer? _recordTimer;
  int _recordSeconds = 0;
  String? _error;
  List<ChatMessage> _messages = [];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadMessages();
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    _scrollController.dispose();
    _audioRecorder.dispose();
    _recordTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadMessages() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final items = await _api.getMessages();
      if (!mounted) return;
      setState(() {
        _messages = items;
        _loading = false;
      });
      _scrollToBottom();
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString().replaceAll('Exception: ', '');
        _loading = false;
      });
    }
  }

  Future<void> _refresh() async {
    if (_loading) return;
    try {
      final items = await _api.getMessages();
      if (!mounted) return;
      setState(() {
        _messages = items;
      });
      _scrollToBottom();
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString().replaceAll('Exception: ', '');
      });
    }
  }

  Future<void> _sendMessage() async {
    final raw = _controller.text.trim();
    if (raw.isEmpty || _sending || _sendingMedia) return;

    setState(() {
      _sending = true;
    });
    try {
      final msg = await _api.sendMessage(raw);
      if (!mounted) return;
      setState(() {
        _controller.clear();
        _messages = [..._messages, msg];
        _sending = false;
      });
      HapticHelper.lightImpact();
      _scrollToBottom();
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _sending = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString().replaceAll('Exception: ', '')),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }

  Future<void> _pickAndSendMedia(String messageType) async {
    if (_sending || _sendingMedia) return;

    try {
      final result = await FilePicker.platform.pickFiles(
        allowMultiple: false,
        withData: false,
      );
      if (result == null || result.files.isEmpty) return;
      final file = result.files.single;
      final path = file.path;
      if (path == null || path.isEmpty) return;

      setState(() {
        _sendingMedia = true;
      });

      final msg = await _api.sendMediaMessage(
        filePath: path,
        fileName: file.name,
        messageType: messageType,
      );
      if (!mounted) return;
      setState(() {
        _messages = [..._messages, msg];
        _sendingMedia = false;
      });
      HapticHelper.lightImpact();
      _scrollToBottom();
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _sendingMedia = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString().replaceAll('Exception: ', '')),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }

  Future<void> _toggleRecord() async {
    if (_recording) {
      await _stopRecordingAndSend();
    } else {
      await _startRecording();
    }
  }

  Future<void> _startRecording() async {
    if (_sending || _sendingMedia) return;

    try {
      final hasPermission = await _audioRecorder.hasPermission();
      if (!hasPermission) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text(
              'Autorisez l’accès au micro pour envoyer une note vocale.',
            ),
            backgroundColor: Colors.redAccent,
          ),
        );
        return;
      }

      _recordStartAt = DateTime.now();
      final dir = await getTemporaryDirectory();
      final fileName =
          'note_vocale_guest_${DateTime.now().millisecondsSinceEpoch}.aac';
      final fullPath = '${dir.path}/$fileName';
      await _audioRecorder.start(const RecordConfig(), path: fullPath);

      if (!mounted) return;
      setState(() {
        _recording = true;
        _recordSeconds = 0;
      });

      _recordTimer?.cancel();
      _recordTimer = Timer.periodic(const Duration(seconds: 1), (_) {
        if (!mounted) return;
        setState(() {
          _recordSeconds++;
        });
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString().replaceAll('Exception: ', '')),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }

  Future<void> _stopRecordingAndSend() async {
    try {
      final startedAt = _recordStartAt;
      final path = await _audioRecorder.stop();

      if (!mounted) return;
      setState(() {
        _recording = false;
        _recordStartAt = null;
        _recordTimer?.cancel();
      });

      if (path == null || path.isEmpty) return;

      final durationSeconds = startedAt != null
          ? DateTime.now().difference(startedAt).inSeconds
          : null;

      setState(() {
        _sendingMedia = true;
      });

      final fileName = path.split('/').isNotEmpty
          ? path.split('/').last
          : 'note_vocale_guest.m4a';

      final msg = await _api.sendMediaMessage(
        filePath: path,
        fileName: fileName,
        messageType: 'audio',
        durationSeconds: durationSeconds,
      );
      if (!mounted) return;
      setState(() {
        _messages = [..._messages, msg];
        _sendingMedia = false;
      });
      HapticHelper.lightImpact();
      _scrollToBottom();
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _sendingMedia = false;
        _recording = false;
        _recordStartAt = null;
        _recordTimer?.cancel();
        _recordSeconds = 0;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString().replaceAll('Exception: ', '')),
          backgroundColor: Colors.redAccent,
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

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
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
                                textAlign: TextAlign.center,
                                style: const TextStyle(
                                  color: AppTheme.textGray,
                                  fontSize: 14,
                                ),
                              ),
                            ),
                          ),
                        )
                      else
                        Expanded(
                          child: RefreshIndicator(
                            color: AppTheme.accentGold,
                            onRefresh: _refresh,
                            child: _messages.isEmpty
                                ? ListView(
                                    padding: const EdgeInsets.all(24),
                                    children: const [
                                      SizedBox(height: 40),
                                      Center(
                                        child: Text(
                                          'Commencez la conversation avec la réception.',
                                          textAlign: TextAlign.center,
                                          style: TextStyle(
                                            color: AppTheme.textGray,
                                            fontSize: 15,
                                          ),
                                        ),
                                      ),
                                    ],
                                  )
                                : ListView.builder(
                                    controller: _scrollController,
                                    padding: const EdgeInsets.fromLTRB(
                                      16,
                                      12,
                                      16,
                                      12,
                                    ),
                                    itemCount: _messages.length,
                                    itemBuilder: (context, index) {
                                      final message = _messages[index];
                                      final isMe =
                                          message.senderType == 'guest';
                                      return _buildMessageBubble(
                                        context,
                                        message,
                                        isMe,
                                      );
                                    },
                                  ),
                          ),
                        ),
                      _buildInputBar(context, l10n),
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
  ) {
    final time = DateFormat.Hm().format(message.createdAt.toLocal());
    final bgColor = isMe
        ? AppTheme.accentGold
        : AppTheme.primaryBlue.withValues(alpha: 0.9);
    final textColor = isMe ? AppTheme.primaryDark : Colors.white;

    return Align(
      alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 4),
        padding: const EdgeInsets.all(12),
        constraints: const BoxConstraints(maxWidth: 480),
        decoration: BoxDecoration(
          color: bgColor,
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
            Text(
              message.messageType == 'text'
                  ? (message.content ?? '')
                  : '[Message média]',
              style: TextStyle(color: textColor, fontSize: 16, height: 1.3),
            ),
            const SizedBox(height: 4),
            Text(
              time,
              style: TextStyle(
                color: textColor.withValues(alpha: 0.8),
                fontSize: 12,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInputBar(BuildContext context, AppLocalizations l10n) {
    return SafeArea(
      top: false,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (_recording) _buildRecordingIndicator(),
          Padding(
            padding: const EdgeInsets.fromLTRB(12, 8, 12, 12),
            child: Row(
              children: [
                IconButton(
                  onPressed: (_sending || _sendingMedia || _recording)
                      ? null
                      : () => _pickAndSendMedia('image'),
                  icon: Icon(
                    Icons.attach_file,
                    color: (_sending || _sendingMedia)
                        ? AppTheme.textGray
                        : AppTheme.accentGold,
                  ),
                ),
                IconButton(
                  onPressed: (_sending || _sendingMedia) ? null : _toggleRecord,
                  icon: Icon(
                    _recording
                        ? Icons.stop_circle_rounded
                        : Icons.mic_none_rounded,
                    color: (_sending || _sendingMedia)
                        ? AppTheme.textGray
                        : (_recording ? Colors.redAccent : AppTheme.accentGold),
                  ),
                ),
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
                  onPressed: (_sending || _sendingMedia) ? null : _sendMessage,
                  icon: Icon(
                    Icons.send_rounded,
                    color: (_sending || _sendingMedia)
                        ? AppTheme.textGray
                        : AppTheme.accentGold,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRecordingIndicator() {
    final pattern = [6.0, 18.0, 26.0, 14.0, 22.0, 10.0, 20.0];
    final shift = _recordSeconds % pattern.length;
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 0),
      child: Row(
        children: [
          Text(
            _formatRecordDuration(),
            style: const TextStyle(color: Colors.white, fontSize: 13),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: SizedBox(
              height: 28,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(pattern.length, (index) {
                  final h = pattern[(index + shift) % pattern.length];
                  return AnimatedContainer(
                    duration: const Duration(milliseconds: 160),
                    margin: const EdgeInsets.symmetric(horizontal: 2),
                    width: 3,
                    height: h,
                    decoration: BoxDecoration(
                      color: AppTheme.accentGold,
                      borderRadius: BorderRadius.circular(8),
                    ),
                  );
                }),
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _formatRecordDuration() {
    final minutes = _recordSeconds ~/ 60;
    final seconds = _recordSeconds % 60;
    return '$minutes:${seconds.toString().padLeft(2, '0')}';
  }
}
