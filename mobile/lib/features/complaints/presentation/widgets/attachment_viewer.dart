import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import '../../../../core/constants/app_colors.dart';
import '../../domain/entities/attachment_entity.dart';

class AttachmentViewer extends StatelessWidget {
  final List<AttachmentEntity> attachments;

  const AttachmentViewer({super.key, required this.attachments});

  void _openFullScreen(BuildContext context, String imageUrl, String tag) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (ctx) => Scaffold(
          backgroundColor: Colors.black,
          appBar: AppBar(
            backgroundColor: Colors.black,
            elevation: 0,
            centerTitle: true,
            leading: IconButton(
              icon: const Icon(
                Icons.close_rounded,
                color: Colors.white,
                size: 20,
              ),
              onPressed: () => Navigator.of(ctx).pop(),
            ),
            title: Text(
              tag,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 15,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
          body: Center(
            child: InteractiveViewer(
              minScale: 0.5,
              maxScale: 4.0,
              child: CachedNetworkImage(
                imageUrl: imageUrl,
                fit: BoxFit.contain,
                placeholder: (context, url) => const Center(
                  child: SizedBox(
                    width: 24,
                    height: 24,
                    child: CircularProgressIndicator(
                      color: AppColors.yemenRed,
                      strokeWidth: 2,
                    ),
                  ),
                ),
                errorWidget: (context, url, error) => const Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.broken_image_outlined,
                        size: 40,
                        color: Colors.white54,
                      ),
                      SizedBox(height: 10),
                      Text(
                        'تعذر تحميل الصورة الأصلية',
                        style: TextStyle(color: Colors.white70, fontSize: 13),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (attachments.isEmpty) {
      return Container(
        padding: const EdgeInsets.symmetric(vertical: 18, horizontal: 16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.borderSubtle, width: 0.8),
        ),
        child: const Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.image_not_supported_outlined,
              size: 18,
              color: AppColors.textMuted,
            ),
            SizedBox(width: 8),
            Text(
              'لا توجد مرفقات مصورة لهذا البلاغ',
              style: TextStyle(fontSize: 12.5, color: AppColors.textSecondary),
            ),
          ],
        ),
      );
    }

    // If only 1 attachment, display a sleek full-width card
    if (attachments.length == 1) {
      return SizedBox(
        height: 175,
        width: double.infinity,
        child: _buildAttachmentCard(context, attachments.first),
      );
    }

    // If multiple attachments, display a wide horizontal gallery
    return SizedBox(
      height: 155,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: attachments.length,
        separatorBuilder: (context, index) => const SizedBox(width: 10),
        itemBuilder: (context, index) {
          return SizedBox(
            width: 210,
            child: _buildAttachmentCard(context, attachments[index]),
          );
        },
      ),
    );
  }

  Widget _buildAttachmentCard(
    BuildContext context,
    AttachmentEntity attachment,
  ) {
    final url = attachment.fileUrl ?? '';
    final tag = attachment.isBefore ? 'صورة البلاغ' : 'بعد المعالجة';
    final badgeColor = attachment.isBefore
        ? AppColors.yemenBlack
        : AppColors.yemenEmerald;

    return GestureDetector(
      onTap: () {
        if (url.isNotEmpty) {
          _openFullScreen(context, url, tag);
        }
      },
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.borderSubtle, width: 0.8),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.015),
              blurRadius: 10,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(13.2),
          child: Stack(
            fit: StackFit.expand,
            children: [
              if (url.isNotEmpty)
                CachedNetworkImage(
                  imageUrl: url,
                  fit: BoxFit.cover,
                  placeholder: (context, url) => Container(
                    color: AppColors.backgroundSubtle,
                    child: const Center(
                      child: SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 1.8,
                          color: AppColors.yemenRed,
                        ),
                      ),
                    ),
                  ),
                  errorWidget: (context, url, error) => Container(
                    color: AppColors.backgroundSubtle,
                    child: const Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.broken_image_outlined,
                          color: AppColors.textMuted,
                          size: 24,
                        ),
                        SizedBox(height: 4),
                        Text(
                          'تعذر العرض',
                          style: TextStyle(
                            fontSize: 11,
                            color: AppColors.textSecondary,
                          ),
                        ),
                      ],
                    ),
                  ),
                )
              else
                Container(
                  color: AppColors.backgroundSubtle,
                  child: const Center(
                    child: Icon(
                      Icons.image_not_supported_outlined,
                      color: AppColors.textMuted,
                    ),
                  ),
                ),

              // Subtle bottom gradient for contrast
              Positioned(
                bottom: 0,
                left: 0,
                right: 0,
                height: 48,
                child: DecoratedBox(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.bottomCenter,
                      end: Alignment.topCenter,
                      colors: [
                        Colors.black.withValues(alpha: 0.45),
                        Colors.transparent,
                      ],
                    ),
                  ),
                ),
              ),

              // Stage Label Pill
              Positioned(
                bottom: 10,
                right: 10,
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 9,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: badgeColor.withValues(alpha: 0.85),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    tag,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 10.5,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ),

              // Minimal Zoom Icon
              Positioned(
                bottom: 10,
                left: 10,
                child: Container(
                  padding: const EdgeInsets.all(5),
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.45),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: const Icon(
                    Icons.open_in_full_rounded,
                    size: 12,
                    color: Colors.white,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
