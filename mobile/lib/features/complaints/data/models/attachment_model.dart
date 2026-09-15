import '../../../../core/network/media_url_resolver.dart';
import '../../domain/entities/attachment_entity.dart';

class AttachmentModel {
  final int id;
  final String filePath;
  final String? fileUrl;
  final String? fileType;
  final double? capturedLatitude;
  final double? capturedLongitude;
  final String type;
  final String? uploaderName;
  final DateTime? createdAt;

  const AttachmentModel({
    required this.id,
    required this.filePath,
    this.fileUrl,
    this.fileType,
    this.capturedLatitude,
    this.capturedLongitude,
    required this.type,
    this.uploaderName,
    this.createdAt,
  });

  factory AttachmentModel.fromJson(Map<String, dynamic> json) {
    double? parseCoord(dynamic value) {
      if (value == null) return null;
      if (value is num) return value.toDouble();
      return double.tryParse(value.toString());
    }

    String? parsedUploaderName;
    if (json['uploader'] is Map<String, dynamic>) {
      parsedUploaderName = json['uploader']['name']?.toString();
    }

    DateTime? parsedCreatedAt;
    if (json['created_at'] != null) {
      parsedCreatedAt = DateTime.tryParse(json['created_at'].toString());
    }

    return AttachmentModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      filePath: json['file_path'] ?? '',
      fileUrl: json['file_url']?.toString(),
      fileType: json['file_type']?.toString(),
      capturedLatitude: parseCoord(json['captured_latitude']),
      capturedLongitude: parseCoord(json['captured_longitude']),
      type: json['type']?.toString() ?? 'before',
      uploaderName: parsedUploaderName,
      createdAt: parsedCreatedAt,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'file_path': filePath,
      'file_url': fileUrl,
      'file_type': fileType,
      'captured_latitude': capturedLatitude,
      'captured_longitude': capturedLongitude,
      'type': type,
      if (uploaderName != null) 'uploader': {'name': uploaderName},
      'created_at': createdAt?.toIso8601String(),
    };
  }

  AttachmentEntity toEntity() {
    return AttachmentEntity(
      id: id,
      filePath: filePath,
      fileUrl: MediaUrlResolver.resolve(fileUrl),
      fileType: fileType,
      capturedLatitude: capturedLatitude,
      capturedLongitude: capturedLongitude,
      type: type,
      uploaderName: uploaderName,
      createdAt: createdAt,
    );
  }
}
