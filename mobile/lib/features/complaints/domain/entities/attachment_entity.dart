class AttachmentEntity {
  final int id;
  final String filePath;
  final String? fileUrl;
  final String? fileType;
  final double? capturedLatitude;
  final double? capturedLongitude;
  final String type; // 'before' or 'after'
  final String? uploaderName;
  final DateTime? createdAt;

  const AttachmentEntity({
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

  bool get isBefore => type == 'before';
  bool get isAfter => type == 'after';
}
