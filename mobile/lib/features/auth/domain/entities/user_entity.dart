class DepartmentEntity {
  final int id;
  final String name;
  final int? ministryId;
  final String? ministryName;

  const DepartmentEntity({
    required this.id,
    required this.name,
    this.ministryId,
    this.ministryName,
  });
}

class RoleEntity {
  final int id;
  final String name;
  final List<String> permissions;

  const RoleEntity({
    required this.id,
    required this.name,
    this.permissions = const [],
  });
}

class UserEntity {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? nationalId;
  final bool isActive;
  final DepartmentEntity? department;
  final List<RoleEntity> roles;
  final DateTime? createdAt;

  const UserEntity({
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

  UserEntity copyWith({
    int? id,
    String? name,
    String? email,
    String? phone,
    String? nationalId,
    bool? isActive,
    DepartmentEntity? department,
    List<RoleEntity>? roles,
    DateTime? createdAt,
  }) {
    return UserEntity(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      nationalId: nationalId ?? this.nationalId,
      isActive: isActive ?? this.isActive,
      department: department ?? this.department,
      roles: roles ?? this.roles,
      createdAt: createdAt ?? this.createdAt,
    );
  }

  String get primaryRoleName {
    if (roles.isNotEmpty) {
      return roles.first.name;
    }
    return 'مواطن';
  }

  bool get isCitizen => roles.any((r) => r.name.toLowerCase() == 'citizen');
  bool get isAdmin => roles.any((r) => r.name.toLowerCase() == 'admin');
  bool get isFieldWorker =>
      roles.any((r) =>
          r.name.toLowerCase() == 'field worker' ||
          r.name.toLowerCase() == 'field_worker');

  List<String> get allPermissions {
    final permissions = <String>{};
    for (final role in roles) {
      permissions.addAll(role.permissions);
    }
    return permissions.toList();
  }
}
