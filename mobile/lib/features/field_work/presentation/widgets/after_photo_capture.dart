import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/services/camera_service.dart';
import '../../../complaints/application/complaints_providers.dart';

class AfterPhotoCapture extends ConsumerStatefulWidget {
  final List<String> initialPhotos;
  final ValueChanged<List<String>> onPhotosChanged;
  final void Function(double latitude, double longitude)? onLocationCaptured;
  final int maxPhotos;

  const AfterPhotoCapture({
    super.key,
    this.initialPhotos = const [],
    required this.onPhotosChanged,
    this.onLocationCaptured,
    this.maxPhotos = 5,
  });

  @override
  ConsumerState<AfterPhotoCapture> createState() => _AfterPhotoCaptureState();
}

class _AfterPhotoCaptureState extends ConsumerState<AfterPhotoCapture> {
  final List<String> _photos = [];
  bool _isCapturing = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _photos.addAll(widget.initialPhotos);
  }

  Future<void> _capturePhoto() async {
    if (_photos.length >= widget.maxPhotos) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('الحد الأقصى لصور إثبات الإنجاز هو ${widget.maxPhotos} صور.'),
          backgroundColor: AppColors.yemenRed,
        ),
      );
      return;
    }

    setState(() {
      _isCapturing = true;
      _errorMessage = null;
    });

    try {
      final cameraService = ref.read(cameraServiceProvider);
      final photoPath = await cameraService.capturePhoto();

      if (photoPath != null) {
        setState(() {
          _photos.add(photoPath);
        });
        widget.onPhotosChanged(List.unmodifiable(_photos));

        // Attempt to capture GPS coordinates at the moment of taking the photo
        try {
          final locationService = ref.read(locationServiceProvider);
          final loc = await locationService.getCurrentLocation();
          widget.onLocationCaptured?.call(loc.latitude, loc.longitude);
        } catch (_) {
          // Location acquisition is handled additionally at execution page level
        }
      }
    } on CameraException catch (e) {
      setState(() {
        _errorMessage = e.message;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'حدث خطأ غير متوقع أثناء فتح الكاميرا: ${e.toString()}';
      });
    } finally {
      if (mounted) {
        setState(() {
          _isCapturing = false;
        });
      }
    }
  }

  void _removePhoto(int index) {
    setState(() {
      _photos.removeAt(index);
    });
    widget.onPhotosChanged(List.unmodifiable(_photos));
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Header
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Row(
              children: [
                Icon(Icons.camera_alt_rounded, size: 18, color: AppColors.deepBlack),
                SizedBox(width: 8),
                Text(
                  'توثيق ما بعد الإصلاح (صور الإنجاز)',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimary,
                  ),
                ),
              ],
            ),
            Text(
              '${_photos.length}/${widget.maxPhotos}',
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
              ),
            ),
          ],
        ),

        const SizedBox(height: 6),
        const Text(
          'التقط صورة واضحة للأعمال المنجزة لتوثيق الحل وإرفاقها مع التقرير.',
          style: TextStyle(
            fontSize: 12,
            color: AppColors.textSecondary,
          ),
        ),

        if (_errorMessage != null) ...[
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: Colors.red.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.red.withValues(alpha: 0.2)),
            ),
            child: Row(
              children: [
                const Icon(Icons.error_outline, size: 16, color: Colors.red),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    _errorMessage!,
                    style: const TextStyle(fontSize: 12, color: Colors.red),
                  ),
                ),
              ],
            ),
          ),
        ],

        const SizedBox(height: 12),

        // Photo Grid / Action Row
        if (_photos.isEmpty)
          // Empty State Prompt
          InkWell(
            onTap: _isCapturing ? null : _capturePhoto,
            borderRadius: BorderRadius.circular(12),
            child: Container(
              height: 130,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: AppColors.borderSubtle,
                  style: BorderStyle.solid,
                ),
              ),
              child: Center(
                child: _isCapturing
                    ? const CircularProgressIndicator(strokeWidth: 2)
                    : Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: AppColors.deepBlack.withValues(alpha: 0.05),
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(
                              Icons.add_a_photo_rounded,
                              size: 28,
                              color: AppColors.deepBlack,
                            ),
                          ),
                          const SizedBox(height: 8),
                          const Text(
                            'اضغط لالتقاط صورة ما بعد الإصلاح',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              color: AppColors.deepBlack,
                            ),
                          ),
                          const SizedBox(height: 2),
                          const Text(
                            'الكاميرا المباشرة فقط لضمان المصداقية',
                            style: TextStyle(
                              fontSize: 11,
                              color: AppColors.textMuted,
                            ),
                          ),
                        ],
                      ),
              ),
            ),
          )
        else
          // Thumbnails + Add button
          Wrap(
            spacing: 10,
            runSpacing: 10,
            children: [
              ..._photos.asMap().entries.map((entry) {
                final index = entry.key;
                final path = entry.value;

                return Stack(
                  clipBehavior: Clip.none,
                  children: [
                    Container(
                      width: 95,
                      height: 95,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.borderSubtle),
                        image: DecorationImage(
                          image: FileImage(File(path)),
                          fit: BoxFit.cover,
                        ),
                      ),
                    ),
                    // After badge
                    Positioned(
                      bottom: 4,
                      right: 4,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppColors.yemenEmerald,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: const Text(
                          'بعد الإصلاح',
                          style: TextStyle(
                            fontSize: 9,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ),
                    // Delete button
                    Positioned(
                      top: -6,
                      left: -6,
                      child: InkWell(
                        onTap: () => _removePhoto(index),
                        child: Container(
                          padding: const EdgeInsets.all(4),
                          decoration: const BoxDecoration(
                            color: Colors.red,
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(
                            Icons.close_rounded,
                            size: 14,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ),
                  ],
                );
              }),

              // Add more button if limit not reached
              if (_photos.length < widget.maxPhotos)
                InkWell(
                  onTap: _isCapturing ? null : _capturePhoto,
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    width: 95,
                    height: 95,
                    decoration: BoxDecoration(
                      color: Colors.black.withValues(alpha: 0.03),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: AppColors.borderSubtle,
                        style: BorderStyle.solid,
                      ),
                    ),
                    child: Center(
                      child: _isCapturing
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  Icons.add_a_photo_outlined,
                                  size: 24,
                                  color: AppColors.textSecondary,
                                ),
                                SizedBox(height: 4),
                                Text(
                                  'إضافة صورة',
                                  style: TextStyle(
                                    fontSize: 10,
                                    color: AppColors.textSecondary,
                                  ),
                                ),
                              ],
                            ),
                    ),
                  ),
                ),
            ],
          ),
      ],
    );
  }
}
