import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/services/camera_service.dart';
import '../../../core/services/location_service.dart';
import '../domain/entities/category_entity.dart';
import '../domain/entities/ministry_entity.dart';
import '../domain/repositories/complaint_repository.dart';
import 'complaints_providers.dart';
import 'create_complaint_state.dart';

class CreateComplaintNotifier extends Notifier<CreateComplaintState> {
  late ComplaintRepository _repository;
  late CameraService _cameraService;
  late LocationService _locationService;

  @override
  CreateComplaintState build() {
    _repository = ref.read(complaintRepositoryProvider);
    _cameraService = ref.read(cameraServiceProvider);
    _locationService = ref.read(locationServiceProvider);
    return const CreateComplaintState();
  }

  void selectMinistry(MinistryEntity ministry) {
    state = state.copyWith(
      selectedMinistry: ministry,
      clearParentCategory: true,
      clearCategory: true,
      clearErrorMessage: true,
    );
  }

  void selectParentCategory(CategoryEntity parentCategory) {
    // If the selected category has no children, it acts as the leaf category directly
    state = state.copyWith(
      selectedParentCategory: parentCategory,
      selectedCategory: parentCategory.hasChildren ? null : parentCategory,
      clearCategory: parentCategory.hasChildren,
      clearErrorMessage: true,
    );
  }

  void selectCategory(CategoryEntity category) {
    state = state.copyWith(
      selectedCategory: category,
      clearErrorMessage: true,
    );
  }

  void setTitle(String value) {
    state = state.copyWith(
      title: value,
      clearErrorMessage: true,
    );
  }

  void setDescription(String value) {
    state = state.copyWith(
      description: value,
      clearErrorMessage: true,
    );
  }

  void setPriority(String value) {
    state = state.copyWith(priority: value);
  }

  void setStep(int step) {
    if (step >= 0 && step <= 3) {
      state = state.copyWith(
        currentStep: step,
        clearErrorMessage: true,
      );
    }
  }

  void nextStep() {
    if (state.currentStep < 3) {
      state = state.copyWith(
        currentStep: state.currentStep + 1,
        clearErrorMessage: true,
      );
    }
  }

  void prevStep() {
    if (state.currentStep > 0) {
      state = state.copyWith(
        currentStep: state.currentStep - 1,
        clearErrorMessage: true,
      );
    }
  }

  Future<void> capturePhoto() async {
    state = state.copyWith(
      isCameraLoading: true,
      clearCameraError: true,
      clearErrorMessage: true,
    );

    try {
      final path = await _cameraService.capturePhoto();
      if (path != null) {
        state = state.copyWith(
          imagePath: path,
          isCameraLoading: false,
          clearCameraError: true,
        );
      } else {
        // User closed the camera without snapping
        state = state.copyWith(isCameraLoading: false);
      }
    } on CameraException catch (e) {
      state = state.copyWith(
        isCameraLoading: false,
        cameraError: e.message,
        isCameraPermanentlyDenied: e.message.contains('بشكل دائم'),
      );
    } catch (e) {
      state = state.copyWith(
        isCameraLoading: false,
        cameraError: 'تعذر التقاط الصورة من الكاميرا: ${e.toString()}',
      );
    }
  }

  void removePhoto() {
    state = state.copyWith(
      clearImage: true,
      clearCameraError: true,
    );
  }

  Future<void> captureLocation() async {
    state = state.copyWith(
      isLocationLoading: true,
      clearLocationError: true,
      clearErrorMessage: true,
    );

    try {
      final result = await _locationService.getCurrentLocation();
      state = state.copyWith(
        latitude: result.latitude,
        longitude: result.longitude,
        locationAccuracy: result.accuracy,
        isLocationLoading: false,
        clearLocationError: true,
        isGpsDisabled: false,
        isLocationPermanentlyDenied: false,
      );
    } on LocationException catch (e) {
      state = state.copyWith(
        isLocationLoading: false,
        locationError: e.message,
        isGpsDisabled: e.isGpsDisabled,
        isLocationPermanentlyDenied: e.isPermanentlyDenied,
      );
    } catch (e) {
      state = state.copyWith(
        isLocationLoading: false,
        locationError: 'تعذر تحديد الموقع الجغرافي: ${e.toString()}',
      );
    }
  }

  Future<void> openLocationSettings() async {
    await _locationService.openLocationSettings();
  }

  Future<void> openAppSettings() async {
    await _locationService.openAppSettings();
  }

  Future<bool> submitComplaint() async {
    if (!state.isReadyToSubmit) {
      if (!state.canProceedFromCategoryStep) {
        state = state.copyWith(errorMessage: 'يرجى اختيار تصنيف البلاغ أولاً.');
      } else if (!state.canProceedFromDetailsStep) {
        state = state.copyWith(
          errorMessage: 'يرجى إدخال عنوان واضح ووصف لا يقل عن 10 أحرف.',
        );
      } else if (state.imagePath == null) {
        state = state.copyWith(errorMessage: 'التقاط صورة توثيقية بالكاميرا إلزامي.');
      } else if (state.latitude == null || state.longitude == null) {
        state = state.copyWith(errorMessage: 'تحديد الموقع الجغرافي (GPS) إلزامي.');
      }
      return false;
    }

    state = state.copyWith(
      isSubmitting: true,
      clearErrorMessage: true,
    );

    try {
      final List<String> attachments = [];
      if (state.imagePath != null) {
        attachments.add(state.imagePath!);
      }

      final complaint = await _repository.createComplaint(
        title: state.title.trim(),
        description: state.description.trim(),
        categoryId: state.selectedCategory!.id,
        latitude: state.latitude!,
        longitude: state.longitude!,
        priority: state.priority,
        attachmentPaths: attachments.isNotEmpty ? attachments : null,
      );

      state = state.copyWith(
        isSubmitting: false,
        createdComplaint: complaint,
        clearErrorMessage: true,
      );

      return true;
    } catch (e) {
      String readableError = 'فشل في إرسال البلاغ. يرجى التحقق من اتصالك والمحاولة مجدداً.';
      final errStr = e.toString();
      if (errStr.contains('Exception:')) {
        readableError = errStr.replaceAll('Exception:', '').trim();
      }

      state = state.copyWith(
        isSubmitting: false,
        errorMessage: readableError,
      );

      return false;
    }
  }

  void reset() {
    state = const CreateComplaintState();
  }
}
