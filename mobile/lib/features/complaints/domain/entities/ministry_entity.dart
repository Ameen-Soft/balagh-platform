class DepartmentSummaryEntity {
  final int id;
  final int ministryId;
  final String name;
  final String? description;
  final bool isActive;

  const DepartmentSummaryEntity({
    required this.id,
    required this.ministryId,
    required this.name,
    this.description,
    this.isActive = true,
  });
}

class MinistryEntity {
  final int id;
  final String name;
  final String code;
  final String? logo;
  final String? contactEmail;
  final bool isActive;
  final List<DepartmentSummaryEntity> departments;

  const MinistryEntity({
    required this.id,
    required this.name,
    required this.code,
    this.logo,
    this.contactEmail,
    this.isActive = true,
    this.departments = const [],
  });
}
