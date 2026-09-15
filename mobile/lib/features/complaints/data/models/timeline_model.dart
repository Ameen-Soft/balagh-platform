import '../../domain/entities/timeline_entity.dart';

class TimelineModel {
  final int id;
  final String eventType;
  final String description;
  final String? oldValue;
  final String? newValue;
  final String? performerName;
  final DateTime? createdAt;

  const TimelineModel({
    required this.id,
    required this.eventType,
    required this.description,
    this.oldValue,
    this.newValue,
    this.performerName,
    this.createdAt,
  });

  factory TimelineModel.fromJson(Map<String, dynamic> json) {
    String? parsedPerformerName;
    if (json['performer'] is Map<String, dynamic>) {
      parsedPerformerName = json['performer']['name']?.toString();
    }

    DateTime? parsedCreatedAt;
    if (json['created_at'] != null) {
      parsedCreatedAt = DateTime.tryParse(json['created_at'].toString());
    }

    return TimelineModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      eventType: json['event_type'] ?? '',
      description: json['description'] ?? '',
      oldValue: json['old_value']?.toString(),
      newValue: json['new_value']?.toString(),
      performerName: parsedPerformerName,
      createdAt: parsedCreatedAt,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'event_type': eventType,
      'description': description,
      'old_value': oldValue,
      'new_value': newValue,
      if (performerName != null) 'performer': {'name': performerName},
      'created_at': createdAt?.toIso8601String(),
    };
  }

  TimelineEntity toEntity() {
    return TimelineEntity(
      id: id,
      eventType: eventType,
      description: description,
      oldValue: oldValue,
      newValue: newValue,
      performerName: performerName,
      createdAt: createdAt,
    );
  }
}
