/// Result from the backend's verify-location endpoint.
class LocationVerificationResult {
  final int assignmentId;
  final int complaintId;
  final double workerLatitude;
  final double workerLongitude;
  final double complaintLatitude;
  final double complaintLongitude;
  final double distanceMeters;
  final double allowedRadiusMeters;
  final bool isWithinRange;
  final String message;

  const LocationVerificationResult({
    required this.assignmentId,
    required this.complaintId,
    required this.workerLatitude,
    required this.workerLongitude,
    required this.complaintLatitude,
    required this.complaintLongitude,
    required this.distanceMeters,
    required this.allowedRadiusMeters,
    required this.isWithinRange,
    required this.message,
  });

  factory LocationVerificationResult.fromJson(Map<String, dynamic> json) {
    final workerLoc = json['worker_location'] as Map<String, dynamic>? ?? {};
    final complaintLoc =
        json['complaint_location'] as Map<String, dynamic>? ?? {};

    return LocationVerificationResult(
      assignmentId: json['assignment_id'] is int
          ? json['assignment_id']
          : int.parse(json['assignment_id'].toString()),
      complaintId: json['complaint_id'] is int
          ? json['complaint_id']
          : int.parse(json['complaint_id'].toString()),
      workerLatitude: (workerLoc['latitude'] as num?)?.toDouble() ?? 0.0,
      workerLongitude: (workerLoc['longitude'] as num?)?.toDouble() ?? 0.0,
      complaintLatitude:
          (complaintLoc['latitude'] as num?)?.toDouble() ?? 0.0,
      complaintLongitude:
          (complaintLoc['longitude'] as num?)?.toDouble() ?? 0.0,
      distanceMeters: (json['distance_meters'] as num?)?.toDouble() ?? 0.0,
      allowedRadiusMeters:
          (json['allowed_radius_meters'] as num?)?.toDouble() ?? 500.0,
      isWithinRange: json['is_within_range'] == true,
      message: json['message']?.toString() ?? '',
    );
  }
}
