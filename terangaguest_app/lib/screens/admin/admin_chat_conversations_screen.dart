import 'dart:async';

import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:file_picker/file_picker.dart';
import 'package:path_provider/path_provider.dart';
import 'package:record/record.dart';
import 'package:audioplayers/audioplayers.dart';

import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/chat_message.dart';
import '../../providers/auth_provider.dart';
import '../../services/chat_api.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';

class AdminChatConversationsScreen extends StatefulWidget {
  const AdminChatConversationsScreen({super.key});

  @override
  State<AdminChatConversationsScreen> createState() =>
      _AdminChatConversationsScreenState();
}

class _AdminChatConversationsScreenState
    extends State<AdminChatConversationsScreen> {
  final ChatApi _api = ChatApi();
  bool _loading = true;
  bool _refreshing = false;
  bool _loadingMore = false;
  bool _hasMorePages = true;
  String? _error;
  List<StaffConversationSummary> _conversations = [];
  int _currentPage = 1;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadConversations();
    });
  }

  Future<void> _loadConversations() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final result = await _api.getStaffConversations(page: 1);
      final items =
          result['conversations'] as List<StaffConversationSummary>? ?? [];
      final meta = result['meta'] as Map<String, dynamic>? ?? {};
      final currentPage =
          meta['current_page'] is int ? meta['current_page'] as int : 1;
      final lastPage =
          meta['last_page'] is int ? meta['last_page'] as int : currentPage;
      if (!mounted) return;
      setState(() {
        _conversations = items;
        _loading = false;
        _currentPage = currentPage + 1;
        _hasMorePages = currentPage < lastPage;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString().replaceAll('Exception: ', '');
        _loading = false;
      });
    }
  }

  Future<void> _refresh() async {
    if (_refreshing) return;
    setState(() {
      _refreshing = true;
    });
    try {
      final result = await _api.getStaffConversations(page: 1);
      final items =
          result['conversations'] as List<StaffConversationSummary>? ?? [];
      final meta = result['meta'] as Map<String, dynamic>? ?? {};
      final currentPage =
          meta['current_page'] is int ? meta['current_page'] as int : 1;
      final lastPage =
          meta['last_page'] is int ? meta['last_page'] as int : currentPage;
      if (!mounted) return;
      setState(() {
        _conversations = items;
        _currentPage = currentPage + 1;
        _hasMorePages = currentPage < lastPage;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString().replaceAll('Exception: ', '');
      });
    } finally {
      if (mounted) {
        setState(() {
          _refreshing = false;
        });
      }
    }
  }

  Future<void> _loadMore() async {
    if (_loadingMore || !_hasMorePages) return;
    setState(() {
      _loadingMore = true;
    });
    try {
      final result = await _api.getStaffConversations(page: _currentPage);
      final items =
          result['conversations'] as List<StaffConversationSummary>? ?? [];
      final meta = result['meta'] as Map<String, dynamic>? ?? {};
      final currentPage =
          meta['current_page'] is int ? meta['current_page'] as int : _currentPage;
      final lastPage =
          meta['last_page'] is int ? meta['last_page'] as int : currentPage;
      if (!mounted) return;
      setState(() {
        _conversations.addAll(items);
        _currentPage = currentPage + 1;
        _hasMorePages = currentPage < lastPage;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString().replaceAll('Exception: ', '');
      });
    } finally {
      if (mounted) {
        setState(() {
          _loadingMore = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final enterpriseName = user?.enterprise?.name ?? 'Votre établissement';

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              _buildHeader(context, enterpriseName),
              Expanded(child: _buildBody(context)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context, String enterpriseName) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      child: Row(
        children: [
          IconButton(
            onPressed: () {
              HapticHelper.lightImpact();
              Navigator.of(context).pop();
            },
            icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text(
                  'Messages invités',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  enterpriseName,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: AppTheme.textGray,
                    fontSize: 13,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBody(BuildContext context) {
    if (_loading) {
      return const Center(
        child: CircularProgressIndicator(
          valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
        ),
      );
    }

    if (_error != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Text(
            _error!,
            textAlign: TextAlign.center,
            style: const TextStyle(color: AppTheme.textGray, fontSize: 14),
          ),
        ),
      );
    }

    if (_conversations.isEmpty) {
      return RefreshIndicator(
        color: AppTheme.accentGold,
        onRefresh: _refresh,
        child: ListView(
          padding: LayoutHelper.horizontalPadding(
            context,
          ).copyWith(top: 24, bottom: 24),
          children: const [
            SizedBox(height: 40),
            Center(
              child: Text(
                'Aucun message invité pour le moment.',
                textAlign: TextAlign.center,
                style: TextStyle(color: AppTheme.textGray, fontSize: 15),
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      color: AppTheme.accentGold,
      onRefresh: _refresh,
      child: NotificationListener<ScrollNotification>(
        onNotification: (ScrollNotification scrollInfo) {
          if (scrollInfo.metrics.pixels ==
              scrollInfo.metrics.maxScrollExtent) {
            _loadMore();
          }
          return false;
        },
        child: ListView.builder(
          padding: LayoutHelper.horizontalPadding(
            context,
          ).copyWith(top: 8, bottom: 24),
          itemCount: _conversations.length + (_hasMorePages ? 1 : 0),
          itemBuilder: (context, index) {
            if (index == _conversations.length && _hasMorePages) {
              return const Padding(
                padding: EdgeInsets.symmetric(vertical: 16),
                child: Center(
                  child: CircularProgressIndicator(
                    valueColor: AlwaysStoppedAnimation<Color>(
                      AppTheme.accentGold,
                    ),
                  ),
                ),
              );
            }
            final conv = _conversations[index];
            return _buildConversationTile(context, conv);
          },
        ),
      ),
    );
  }

  Widget _buildConversationTile(
    BuildContext context,
    StaffConversationSummary conv,
  ) {
    final date = conv.lastMessageAt;
    final subtitleParts = <String>[];
    if (conv.roomLabel != null && conv.roomLabel!.isNotEmpty) {
      subtitleParts.add(conv.roomLabel!);
    }
    if (date != null) {
      subtitleParts.add(DateFormat('dd/MM HH:mm').format(date.toLocal()));
    }
    final subtitle = subtitleParts.join(' · ');

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: () {
          HapticHelper.selectionClick();
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => AdminChatConversationScreen(
                conversationId: conv.id,
                guestName: conv.guestName,
                roomLabel: conv.roomLabel,
              ),
            ),
          );
        },
        child: Container(
          decoration: BoxDecoration(
            color: AppTheme.primaryDark.withValues(alpha: 0.7),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: AppTheme.accentGold.withValues(alpha: 0.7),
              width: 1,
            ),
          ),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          child: Row(
            children: [
              const Icon(Icons.support_agent, color: AppTheme.accentGold),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      conv.guestName,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 15,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    if (subtitle.isNotEmpty) ...[
                      const SizedBox(height: 2),
                      Text(
                        subtitle,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: AppTheme.textGray,
                          fontSize: 12,
                        ),
                      ),
                    ],
                    if (conv.lastMessagePreview != null &&
                        conv.lastMessagePreview!.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(
                        conv.lastMessagePreview!,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: AppTheme.textGray,
                          fontSize: 13,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              if (conv.unreadCount > 0) ...[
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: AppTheme.accentGold,
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: Text(
                    conv.unreadCount.toString(),
                    style: const TextStyle(
                      color: Colors.black,
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class AdminChatConversationScreen extends StatefulWidget {
  final int conversationId;
  final String guestName;
  final String? roomLabel;

  const AdminChatConversationScreen({
    super.key,
    required this.conversationId,
    required this.guestName,
    required this.roomLabel,
  });

  @override
  State<AdminChatConversationScreen> createState() =>
      _AdminChatConversationScreenState();
}

class _AdminChatConversationScreenState
    extends State<AdminChatConversationScreen> {
  final ChatApi _api = ChatApi();
  final TextEditingController _controller = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  final AudioRecorder _audioRecorder = AudioRecorder();
  final AudioPlayer _audioPlayer = AudioPlayer();
  bool _loading = true;
  bool _sending = false;
  bool _sendingMedia = false;
  bool _recording = false;
  DateTime? _recordStartAt;
  Timer? _recordTimer;
  int _recordSeconds = 0;
  bool _isPlayingAudio = false;
  int? _playingAudioMessageId;
  Timer? _audioAnimationTimer;
  int _audioAnimationTick = 0;
  String? _error;
  List<ChatMessage> _messages = [];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadConversation();
    });

    _audioPlayer.onPlayerComplete.listen((_) {
      if (!mounted) return;
      _stopAudioPlaybackState();
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    _scrollController.dispose();
    _audioRecorder.dispose();
    _recordTimer?.cancel();
    _audioAnimationTimer?.cancel();
    _audioPlayer.dispose();
    super.dispose();
  }

  Future<void> _loadConversation() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final detail = await _api.getStaffConversationDetail(
        widget.conversationId,
      );
      if (!mounted) return;
      setState(() {
        _messages = detail.messages;
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

  Future<void> _sendMessage() async {
    final raw = _controller.text.trim();
    if (raw.isEmpty || _sending || _sendingMedia) return;

    setState(() {
      _sending = true;
    });
    try {
      final msg = await _api.sendStaffTextMessage(widget.conversationId, raw);
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
              'Autorisez l’accès au micro pour envoyer un message vocal.',
            ),
            backgroundColor: Colors.redAccent,
          ),
        );
        return;
      }

      _recordStartAt = DateTime.now();
      final dir = await getTemporaryDirectory();
      final fileName =
          'note_vocale_${DateTime.now().millisecondsSinceEpoch}.aac';
      final fullPath = '${dir.path}/$fileName';
      const config = RecordConfig(encoder: AudioEncoder.aacLc);
      await _audioRecorder.start(config, path: fullPath);

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
          : 'note_vocale.aac';

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
              _buildHeader(context),
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
                              final isMe = msg.senderType != 'guest';
                              return _buildMessageBubble(context, msg, isMe);
                            },
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

  Widget _buildHeader(BuildContext context) {
    final details = <String>[];
    if (widget.roomLabel != null && widget.roomLabel!.isNotEmpty) {
      details.add(widget.roomLabel!);
    }

    return Padding(
      padding: const EdgeInsets.all(20.0),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
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
                  widget.guestName,
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                if (details.isNotEmpty)
                  Text(
                    details.join(' · '),
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
    );
  }

  Widget _buildMessageBubble(
    BuildContext context,
    ChatMessage message,
    bool isMe,
  ) {
    final senderLabel = message.senderName?.trim().isNotEmpty == true
        ? message.senderName!.trim()
        : null;
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
            if (senderLabel != null)
              Padding(
                padding: const EdgeInsets.only(bottom: 4),
                child: Text(
                  senderLabel,
                  style: TextStyle(
                    color: textColor.withValues(alpha: 0.8),
                    fontSize: 12,
                  ),
                ),
              ),
            _buildMessageContent(message, textColor),
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

  Widget _buildMessageContent(ChatMessage message, Color textColor) {
    if (message.messageType == 'image') {
      final meta = message.metadata;
      final url = meta != null ? meta['url'] as String? : null;
      if (url == null || url.isEmpty) {
        return Text(
          message.content?.isNotEmpty == true
              ? message.content!
              : '[Image indisponible]',
          style: TextStyle(color: textColor, fontSize: 16, height: 1.3),
        );
      }
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          if (message.content != null && message.content!.trim().isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(bottom: 6),
              child: Text(
                message.content!,
                style: TextStyle(color: textColor, fontSize: 16, height: 1.3),
              ),
            ),
          ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: Image.network(
              url,
              fit: BoxFit.cover,
              width: 220,
              height: 220,
            ),
          ),
        ],
      );
    }

    if (message.messageType == 'audio') {
      final meta = message.metadata;
      final durationSeconds = meta != null ? meta['duration'] as int? : null;
      final isPlaying = _isPlayingAudio && _playingAudioMessageId == message.id;
      final label = durationSeconds != null && durationSeconds > 0
          ? 'Message vocal ${_formatAudioDuration(durationSeconds)}'
          : 'Message vocal';
      final pattern = [6.0, 16.0, 10.0, 18.0, 12.0];
      final shift = isPlaying ? _audioAnimationTick % pattern.length : 0;

      return InkWell(
        onTap: () => _togglePlayAudio(message),
        borderRadius: BorderRadius.circular(999),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
          decoration: BoxDecoration(
            color: textColor.withValues(alpha: 0.12),
            borderRadius: BorderRadius.circular(999),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                isPlaying ? Icons.pause_circle_filled : Icons.play_circle_fill,
                color: textColor,
                size: 26,
              ),
              const SizedBox(width: 8),
              Text(label, style: TextStyle(color: textColor, fontSize: 15)),
              const SizedBox(width: 10),
              SizedBox(
                height: 18,
                child: Row(
                  children: List.generate(pattern.length, (index) {
                    final h = pattern[(index + shift) % pattern.length];
                    return AnimatedContainer(
                      duration: const Duration(milliseconds: 140),
                      margin: const EdgeInsets.symmetric(horizontal: 1),
                      width: 3,
                      height: h,
                      decoration: BoxDecoration(
                        color: textColor.withValues(alpha: 0.7),
                        borderRadius: BorderRadius.circular(999),
                      ),
                    );
                  }),
                ),
              ),
            ],
          ),
        ),
      );
    }

    return Text(
      message.content ?? '',
      style: TextStyle(color: textColor, fontSize: 16, height: 1.3),
    );
  }

  Future<void> _togglePlayAudio(ChatMessage message) async {
    final meta = message.metadata;
    final url = meta != null ? meta['url'] as String? : null;
    if (url == null || url.isEmpty) return;

    try {
      if (_isPlayingAudio && _playingAudioMessageId == message.id) {
        await _audioPlayer.pause();
        if (!mounted) return;
        setState(() {
          _isPlayingAudio = false;
        });
        _audioAnimationTimer?.cancel();
        return;
      }

      await _audioPlayer.stop();
      await _audioPlayer.play(UrlSource(url));
      if (!mounted) return;
      setState(() {
        _isPlayingAudio = true;
        _playingAudioMessageId = message.id;
      });
      _audioAnimationTimer?.cancel();
      _audioAnimationTimer = Timer.periodic(const Duration(milliseconds: 160), (
        _,
      ) {
        if (!mounted) return;
        setState(() {
          _audioAnimationTick++;
        });
      });
    } catch (_) {}
  }

  void _stopAudioPlaybackState() {
    _audioAnimationTimer?.cancel();
    _audioAnimationTimer = null;
    _audioAnimationTick = 0;
    setState(() {
      _isPlayingAudio = false;
      _playingAudioMessageId = null;
    });
  }

  String _formatAudioDuration(int seconds) {
    final m = seconds ~/ 60;
    final s = seconds % 60;
    return '$m:${s.toString().padLeft(2, '0')}';
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
