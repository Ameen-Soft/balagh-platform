class TimelineEntity {
  final int id;
  final String eventType;
  final String description;
  final String? oldValue;
  final String? newValue;
  final String? performerName;
  final DateTime? createdAt;

  const TimelineEntity({
    required this.id,
    required this.eventType,
    required this.description,
    this.oldValue,
    this.newValue,
    this.performerName,
    this.createdAt,
  });
}
