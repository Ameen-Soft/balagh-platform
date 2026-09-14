import '../../domain/entities/user_entity.dart';

class MinistryModel {
  final int id;
  final String name;

  const MinistryModel({
    required this.id,
    required this.name,
  });

  factory MinistryModel.fromJson(Map<String, dynamic> json) {
    return MinistryModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      name: json['name'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
    };
  }
}

class DepartmentModel {
  final int id;
  final String name;
  final int? ministryId;
  final MinistryModel? ministry;

  const DepartmentModel({
    required this.id,
    required this.name,
    this.ministryId,
    this.ministry,
  });

  factory DepartmentModel.fromJson(Map<String, dynamic> json) {
    return DepartmentModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      name: json['name'] ?? '',
      ministryId: json['ministry_id'] is int
          ? json['ministry_id']
          : (json['ministry_id'] != null
              ? int.tryParse(json['ministry_id'].toString())
              : null),
      ministry: json['ministry'] is Map<String, dynamic>
          ? MinistryModel.fromJson(json['ministry'] as Map<String, dynamic>)
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'ministry_id': ministryId,
      'ministry': ministry?.toJson(),
    };
  }

  DepartmentEntity toEntity() {
    return DepartmentEntity(
      id: id,
      name: name,
      ministryId: ministryId,
      ministryName: ministry?.name,
    );
  }
}

class RoleModel {
  final int id;
  final String name;
  final List<String> permissions;

  const RoleModel({
    required this.id,
    required this.name,
    this.permissions = const [],
  });

  factory RoleModel.fromJson(Map<String, dynamic> json) {
    List<String> parsedPermissions = [];
    if (json['permissions'] is List) {
      parsedPermissions = (json['permissions'] as List)
          .map((item) => item.toString())
          .toList();
    }

    return RoleModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      name: json['name'] ?? '',
      permissions: parsedPermissions,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'permissions': permissions,
    };
  }

  RoleEntity toEntity() {
    return RoleEntity(
      id: id,
      name: name,
      permissions: permissions,
    );
  }
}

class UserModel {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? nationalId;
  final bool isActive;
  final DepartmentModel? department;
  final List<RoleModel> roles;
  final DateTime? createdAt;

  const UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.nationalId,
    this.isActive = true,
    this.department,
    this.roles = const [],
    this.createdAt,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    DepartmentModel? parsedDepartment;
    if (json['department'] is Map<String, dynamic>) {
      parsedDepartment =
          DepartmentModel.fromJson(json['department'] as Map<String, dynamic>);
    }

    List<RoleModel> parsedRoles = [];
    if (json['roles'] is List) {
      parsedRoles = (json['roles'] as List)
          .whereType<Map<String, dynamic>>()
          .map((roleJson) => RoleModel.fromJson(roleJson))
          .toList();
    }

    DateTime? parsedCreatedAt;
    if (json['created_at'] != null) {
      parsedCreatedAt = DateTime.tryParse(json['created_at'].toString());
    }

    return UserModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      phone: json['phone']?.toString(),
      nationalId: json['national_id']?.toString(),
      isActive: json['is_active'] is bool
          ? json['is_active']
          : (json['is_active'] == 1 || json['is_active'] == '1'),
      department: parsedDepartment,
      roles: parsedRoles,
      createdAt: parsedCreatedAt,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
      'national_id': nationalId,
      'is_active': isActive,
      'department': department?.toJson(),
      'roles': roles.map((r) => r.toJson()).toList(),
      'created_at': createdAt?.toIso8601String(),
    };
  }

  UserEntity toEntity() {
    return UserEntity(
      id: id,
      name: name,
      email: email,
      phone: phone,
      nationalId: nationalId,
      isActive: isActive,
      department: department?.toEntity(),
      roles: roles.map((r) => r.toEntity()).toList(),
      createdAt: createdAt,
    );
  }
}
