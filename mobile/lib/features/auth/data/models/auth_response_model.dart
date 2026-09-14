import 'user_model.dart';

class AuthDataModel {
  final UserModel user;
  final String token;

  const AuthDataModel({
    required this.user,
    required this.token,
  });

  factory AuthDataModel.fromJson(Map<String, dynamic> json) {
    return AuthDataModel(
      user: UserModel.fromJson(json['user'] as Map<String, dynamic>),
      token: json['token'] as String? ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'user': user.toJson(),
      'token': token,
    };
  }
}

class AuthResponseModel {
  final bool success;
  final String message;
  final AuthDataModel? data;

  const AuthResponseModel({
    required this.success,
    required this.message,
    this.data,
  });

  factory AuthResponseModel.fromJson(Map<String, dynamic> json) {
    return AuthResponseModel(
      success: json['success'] is bool ? json['success'] : true,
      message: json['message'] as String? ?? '',
      data: json['data'] is Map<String, dynamic>
          ? AuthDataModel.fromJson(json['data'] as Map<String, dynamic>)
          : null,
    );
  }
}
