class ChatMessage {
  final int id;
  final String senderType;
  final String messageType;
  final String? content;
  final Map<String, dynamic>? metadata;
  final DateTime createdAt;

  ChatMessage({
    required this.id,
    required this.senderType,
    required this.messageType,
    required this.content,
    required this.metadata,
    required this.createdAt,
  });

  factory ChatMessage.fromJson(Map<String, dynamic> json) {
    return ChatMessage(
      id: json['id'] as int,
      senderType: json['sender_type'] as String? ?? 'guest',
      messageType: json['message_type'] as String? ?? 'text',
      content: json['content'] as String?,
      metadata: json['metadata'] as Map<String, dynamic>?,
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }
}

