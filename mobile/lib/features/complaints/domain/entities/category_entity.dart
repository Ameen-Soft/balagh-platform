class CategoryEntity {
  final int id;
  final String name;
  final String? description;
  final int level;
  final int? parentId;
  final int? departmentId;
  final String? departmentName;
  final List<CategoryEntity> children;

  const CategoryEntity({
    required this.id,
    required this.name,
    this.description,
    this.level = 1,
    this.parentId,
    this.departmentId,
    this.departmentName,
    this.children = const [],
  });

  bool get isRoot => parentId == null;
  bool get hasChildren => children.isNotEmpty;
}
