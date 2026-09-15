import '../domain/entities/category_entity.dart';
import '../domain/entities/complaint_entity.dart';
import '../domain/entities/ministry_entity.dart';

class CreateComplaintState {
  final int currentStep;
  final MinistryEntity? selectedMinistry;
  final CategoryEntity? selectedParentCategory;
  final CategoryEntity? selectedCategory;
  final String title;
  final String description;
  final String priority;
  final String? imagePath;
  final double? latitude;
  final double? longitude;
  final double? locationAccuracy;
  final bool isLocationLoading;
  final bool isCameraLoading;
  final bool isSubmitting;
  final String? errorMessage;
  final String? locationError;
  final String? cameraError;
  final bool isGpsDisabled;
  final bool isLocationPermanentlyDenied;
  final bool isCameraPermanentlyDenied;
  final ComplaintEntity? createdComplaint;

  const CreateComplaintState({
    this.currentStep = 0,
    this.selectedMinistry,
    this.selectedParentCategory,
    this.selectedCategory,
    this.title = '',
    this.description = '',
    this.priority = 'medium',
    this.imagePath,
    this.latitude,
    this.longitude,
    this.locationAccuracy,
    this.isLocationLoading = false,
    this.isCameraLoading = false,
    this.isSubmitting = false,
    this.errorMessage,
    this.locationError,
    this.cameraError,
    this.isGpsDisabled = false,
    this.isLocationPermanentlyDenied = false,
    this.isCameraPermanentlyDenied = false,
    this.createdComplaint,
  });

  bool get canProceedFromCategoryStep => selectedCategory != null;

  bool get canProceedFromDetailsStep =>
      title.trim().length >= 3 && description.trim().length >= 10;

  bool get canProceedFromEvidenceStep =>
      imagePath != null && latitude != null && longitude != null;

  bool get isReadyToSubmit =>
      canProceedFromCategoryStep &&
      canProceedFromDetailsStep &&
      canProceedFromEvidenceStep &&
      !isSubmitting;

  bool get isSuccess => createdComplaint != null;

  CreateComplaintState copyWith({
    int? currentStep,
    MinistryEntity? selectedMinistry,
    bool clearMinistry = false,
    CategoryEntity? selectedParentCategory,
    bool clearParentCategory = false,
    CategoryEntity? selectedCategory,
    bool clearCategory = false,
    String? title,
    String? description,
    String? priority,
    String? imagePath,
    bool clearImage = false,
    double? latitude,
    double? longitude,
    double? locationAccuracy,
    bool clearLocation = false,
    bool? isLocationLoading,
    bool? isCameraLoading,
    bool? isSubmitting,
    String? errorMessage,
    bool clearErrorMessage = false,
    String? locationError,
    bool clearLocationError = false,
    String? cameraError,
    bool clearCameraError = false,
    bool? isGpsDisabled,
    bool? isLocationPermanentlyDenied,
    bool? isCameraPermanentlyDenied,
    ComplaintEntity? createdComplaint,
    bool clearCreatedComplaint = false,
  }) {
    return CreateComplaintState(
      currentStep: currentStep ?? this.currentStep,
      selectedMinistry:
          clearMinistry ? null : (selectedMinistry ?? this.selectedMinistry),
      selectedParentCategory: clearParentCategory
          ? null
          : (selectedParentCategory ?? this.selectedParentCategory),
      selectedCategory:
          clearCategory ? null : (selectedCategory ?? this.selectedCategory),
      title: title ?? this.title,
      description: description ?? this.description,
      priority: priority ?? this.priority,
      imagePath: clearImage ? null : (imagePath ?? this.imagePath),
      latitude: clearLocation ? null : (latitude ?? this.latitude),
      longitude: clearLocation ? null : (longitude ?? this.longitude),
      locationAccuracy:
          clearLocation ? null : (locationAccuracy ?? this.locationAccuracy),
      isLocationLoading: isLocationLoading ?? this.isLocationLoading,
      isCameraLoading: isCameraLoading ?? this.isCameraLoading,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      errorMessage:
          clearErrorMessage ? null : (errorMessage ?? this.errorMessage),
      locationError:
          clearLocationError ? null : (locationError ?? this.locationError),
      cameraError: clearCameraError ? null : (cameraError ?? this.cameraError),
      isGpsDisabled: isGpsDisabled ?? this.isGpsDisabled,
      isLocationPermanentlyDenied:
          isLocationPermanentlyDenied ?? this.isLocationPermanentlyDenied,
      isCameraPermanentlyDenied:
          isCameraPermanentlyDenied ?? this.isCameraPermanentlyDenied,
      createdComplaint: clearCreatedComplaint
          ? null
          : (createdComplaint ?? this.createdComplaint),
    );
  }
}
