import '../../domain/entities/ministry_entity.dart';

class DepartmentSummaryModel {
  final int id;
  final int ministryId;
  final String name;
  final String? description;
  final bool isActive;

  const DepartmentSummaryModel({
    required this.id,
    required this.ministryId,
    required this.name,
    this.description,
    this.isActive = true,
  });

  factory DepartmentSummaryModel.fromJson(Map<String, dynamic> json) {
    return DepartmentSummaryModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      ministryId: json['ministry_id'] is int
          ? json['ministry_id']
          : int.parse(json['ministry_id'].toString()),
      name: json['name'] ?? '',
      description: json['description']?.toString(),
      isActive: json['is_active'] is bool
          ? json['is_active']
          : (json['is_active'] == 1 || json['is_active'] == '1'),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'ministry_id': ministryId,
      'name': name,
      'description': description,
      'is_active': isActive,
    };
  }

  DepartmentSummaryEntity toEntity() {
    return DepartmentSummaryEntity(
      id: id,
      ministryId: ministryId,
      name: name,
      description: description,
      isActive: isActive,
    );
  }
}

class MinistryModel {
  final int id;
  final String name;
  final String code;
  final String? logo;
  final String? contactEmail;
  final bool isActive;
  final List<DepartmentSummaryModel> departments;

  const MinistryModel({
    required this.id,
    required this.name,
    required this.code,
    this.logo,
    this.contactEmail,
    this.isActive = true,
    this.departments = const [],
  });

  factory MinistryModel.fromJson(Map<String, dynamic> json) {
    List<DepartmentSummaryModel> parsedDepartments = [];
    if (json['departments'] is List) {
      parsedDepartments = (json['departments'] as List)
          .whereType<Map<String, dynamic>>()
          .map((deptJson) => DepartmentSummaryModel.fromJson(deptJson))
          .toList();
    }

    return MinistryModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      name: json['name'] ?? '',
      code: json['code'] ?? '',
      logo: json['logo']?.toString(),
      contactEmail: json['contact_email']?.toString(),
      isActive: json['is_active'] is bool
          ? json['is_active']
          : (json['is_active'] == 1 || json['is_active'] == '1'),
      departments: parsedDepartments,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'code': code,
      'logo': logo,
      'contact_email': contactEmail,
      'is_active': isActive,
      'departments': departments.map((d) => d.toJson()).toList(),
    };
  }

  MinistryEntity toEntity() {
    return MinistryEntity(
      id: id,
      name: name,
      code: code,
      logo: logo,
      contactEmail: contactEmail,
      isActive: isActive,
      departments: departments.map((d) => d.toEntity()).toList(),
    );
  }
}
