class ChatMessage {
  final int id;
  final String senderType;
  final String? senderName;
  final String messageType;
  final String? content;
  final Map<String, dynamic>? metadata;
  final DateTime createdAt;
  final bool isDeleted;
  final int? replyToId;
  final ReplyTo? replyTo;

  ChatMessage({
    required this.id,
    required this.senderType,
    required this.senderName,
    required this.messageType,
    required this.content,
    required this.metadata,
    required this.createdAt,
    this.isDeleted = false,
    this.replyToId,
    this.replyTo,
  });

  ChatMessage copyWith({
    bool? isDeleted,
    String? content,
  }) {
    return ChatMessage(
      id: id,
      senderType: senderType,
      senderName: senderName,
      messageType: messageType,
      content: content ?? this.content,
      metadata: metadata,
      createdAt: createdAt,
      isDeleted: isDeleted ?? this.isDeleted,
      replyToId: replyToId,
      replyTo: replyTo,
    );
  }

  factory ChatMessage.fromJson(Map<String, dynamic> json) {
    final replyToRaw = json['reply_to'] as Map<String, dynamic>?;
    ReplyTo? replyTo;
    if (replyToRaw != null) {
      replyTo = ReplyTo(
        id: replyToRaw['id'] as int? ?? 0,
        senderName: replyToRaw['sender_name'] as String?,
        content: replyToRaw['content'] as String?,
        isDeleted: replyToRaw['deleted'] as bool? ?? false,
      );
    }
    return ChatMessage(
      id: json['id'] as int,
      senderType: json['sender_type'] as String? ?? 'guest',
      senderName: json['sender_name'] as String?,
      messageType: json['message_type'] as String? ?? 'text',
      content: json['content'] as String?,
      metadata: json['metadata'] as Map<String, dynamic>?,
      createdAt: DateTime.parse(json['created_at'] as String),
      isDeleted: json['deleted_at'] != null,
      replyToId: json['reply_to_id'] as int?,
      replyTo: replyTo,
    );
  }
}

class ReplyTo {
  final int id;
  final String? senderName;
  final String? content;
  final bool isDeleted;

  ReplyTo({
    required this.id,
    this.senderName,
    this.content,
    this.isDeleted = false,
  });
}
