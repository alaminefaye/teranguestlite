import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:file_picker/file_picker.dart';

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
  String? _error;
  List<StaffConversationSummary> _conversations = [];

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
      final items = await _api.getStaffConversations();
      if (!mounted) return;
      setState(() {
        _conversations = items;
        _loading = false;
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
      final items = await _api.getStaffConversations();
      if (!mounted) return;
      setState(() {
        _conversations = items;
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
      child: ListView.builder(
        padding: LayoutHelper.horizontalPadding(
          context,
        ).copyWith(top: 8, bottom: 24),
        itemCount: _conversations.length,
        itemBuilder: (context, index) {
          final conv = _conversations[index];
          return _buildConversationTile(context, conv);
        },
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
  bool _loading = true;
  bool _sending = false;
  bool _sendingMedia = false;
  String? _error;
  List<ChatMessage> _messages = [];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadConversation();
    });
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
      child: Padding(
        padding: const EdgeInsets.fromLTRB(12, 8, 12, 12),
        child: Row(
          children: [
            IconButton(
              onPressed:
                  (_sending || _sendingMedia) ? null : () => _pickAndSendMedia('image'),
              icon: Icon(
                Icons.attach_file,
                color: (_sending || _sendingMedia)
                    ? AppTheme.textGray
                    : AppTheme.accentGold,
              ),
            ),
            IconButton(
              onPressed:
                  (_sending || _sendingMedia) ? null : () => _pickAndSendMedia('audio'),
              icon: Icon(
                Icons.mic_none_rounded,
                color: (_sending || _sendingMedia)
                    ? AppTheme.textGray
                    : AppTheme.accentGold,
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
    );
  }
}
