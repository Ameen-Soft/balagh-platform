class ComplaintLikeResult {
  final bool isLiked;
  final int likesCount;

  const ComplaintLikeResult({
    required this.isLiked,
    required this.likesCount,
  });

  factory ComplaintLikeResult.fromJson(Map<String, dynamic> json) {
    return ComplaintLikeResult(
      isLiked: json['liked'] == true,
      likesCount: json['likes_count'] is int
          ? json['likes_count'] as int
          : (int.tryParse(json['likes_count']?.toString() ?? '0') ?? 0),
    );
  }
}
