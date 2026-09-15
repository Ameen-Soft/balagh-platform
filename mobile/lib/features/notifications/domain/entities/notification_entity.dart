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

  int? get complaintId {
    if (data != null && data!['complaint_id'] != null) {
      final val = data!['complaint_id'];
      if (val is int) return val;
      return int.tryParse(val.toString());
    }
    return null;
  }
}
