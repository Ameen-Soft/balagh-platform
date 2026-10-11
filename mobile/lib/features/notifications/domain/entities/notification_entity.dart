class NotificationEntity {
  final int id;
  final String title;
  final String body;
  final String type;
  final Map<String, dynamic>? data;
  final bool isRead;
  final DateTime? createdAt;

  const NotificationEntity({
    required this.id,
    required this.title,
    required this.body,
    required this.type,
    this.data,
    this.isRead = false,
    this.createdAt,
  });

  NotificationEntity copyWith({
    int? id,
    String? title,
    String? body,
    String? type,
    Map<String, dynamic>? data,
    bool? isRead,
    DateTime? createdAt,
  }) {
    return NotificationEntity(
      id: id ?? this.id,
      title: title ?? this.title,
      body: body ?? this.body,
      type: type ?? this.type,
      data: data ?? this.data,
      isRead: isRead ?? this.isRead,
      createdAt: createdAt ?? this.createdAt,
    );
  }

  int? get complaintId {
    if (data != null && data!['complaint_id'] != null) {
      final val = data!['complaint_id'];
      if (val is int) return val;
      return int.tryParse(val.toString());
    }
    return null;
  }

  int? get projectId {
    if (data != null && data!['project_id'] != null) {
      final val = data!['project_id'];
      if (val is int) return val;
      return int.tryParse(val.toString());
    }
    return null;
  }

  int? get assignmentId {
    if (data != null && data!['assignment_id'] != null) {
      final val = data!['assignment_id'];
      if (val is int) return val;
      return int.tryParse(val.toString());
    }
    return null;
  }
}
