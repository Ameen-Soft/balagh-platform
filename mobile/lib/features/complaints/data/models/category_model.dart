import '../../domain/entities/category_entity.dart';

class CategoryModel {
  final int id;
  final String name;
  final String? description;
  final int level;
  final int? parentId;
  final int? departmentId;
  final String? departmentName;
  final List<CategoryModel> children;

  const CategoryModel({
    required this.id,
    required this.name,
    this.description,
    this.level = 1,
    this.parentId,
    this.departmentId,
    this.departmentName,
    this.children = const [],
  });

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    String? parsedDepartmentName;
    if (json['department'] is Map<String, dynamic>) {
      parsedDepartmentName = json['department']['name']?.toString();
    }

    List<CategoryModel> parsedChildren = [];
    if (json['children'] is List) {
      parsedChildren = (json['children'] as List)
          .whereType<Map<String, dynamic>>()
          .map((childJson) => CategoryModel.fromJson(childJson))
          .toList();
    }

    return CategoryModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      name: json['name'] ?? '',
      description: json['description']?.toString(),
      level: json['level'] is int
          ? json['level']
          : (json['level'] != null ? int.tryParse(json['level'].toString()) ?? 1 : 1),
      parentId: json['parent_id'] is int
          ? json['parent_id']
          : (json['parent_id'] != null ? int.tryParse(json['parent_id'].toString()) : null),
      departmentId: json['department_id'] is int
          ? json['department_id']
          : (json['department_id'] != null ? int.tryParse(json['department_id'].toString()) : null),
      departmentName: parsedDepartmentName,
      children: parsedChildren,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'level': level,
      'parent_id': parentId,
      'department_id': departmentId,
      if (departmentName != null) 'department': {'name': departmentName},
      'children': children.map((c) => c.toJson()).toList(),
    };
  }

  CategoryEntity toEntity() {
    return CategoryEntity(
      id: id,
      name: name,
      description: description,
      level: level,
      parentId: parentId,
      departmentId: departmentId,
      departmentName: departmentName,
      children: children.map((c) => c.toEntity()).toList(),
    );
  }
}
