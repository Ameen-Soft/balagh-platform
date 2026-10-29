plugins {
    id("com.android.application")
    // The Flutter Gradle Plugin must be applied after the Android and Kotlin Gradle plugins.
    id("dev.flutter.flutter-gradle-plugin")
}

android {
    namespace = "com.example.mobile"
    compileSdk = 37
    ndkVersion = flutter.ndkVersion

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    defaultConfig {
        // TODO: Specify your own unique Application ID (https://developer.android.com/studio/build/application-id.html).
        applicationId = "com.example.mobile"
        // You can update the following values to match your application needs.
        // For more information, see: https://flutter.dev/to/review-gradle-config.
        minSdk = flutter.minSdkVersion
        targetSdk = flutter.targetSdkVersion
        // Uses the version code from pubspec.yaml. When using split APKs, 1000 * ABI_VERSION
        // is added automatically by Flutter. (https://developer.android.com/studio/build/configure-apk-splits#configure-APK-versions)
        // You can force using the value of versionCode by specifying the `-P force-version-code-ignoring-abi=true`
        // flag during build.
        versionCode = flutter.versionCode
        versionName = flutter.versionName
    }

    buildTypes {
        release {
            // TODO: Add your own signing config for the release build.
            // Signing with the debug keys for now, so `flutter run --release` works.
            signingConfig = signingConfigs.getByName("debug")
        }
    }
}

kotlin {
    compilerOptions {
        jvmTarget = org.jetbrains.kotlin.gradle.dsl.JvmTarget.JVM_17
    }
}

flutter {
    source = "../.."
}

tasks.register("copyBaderReleaseApk") {
    description = "Copies the release APK to بادر.apk in build/app/outputs/flutter-apk/ only"
    doLast {
        val buildDir = project.layout.buildDirectory.get().asFile
        val targetDir = File(buildDir, "outputs/flutter-apk")
        if (!targetDir.exists()) {
            targetDir.mkdirs()
        }

        // المسار المصدري الحصري الناتج عن عملية البناء الحالية
        val releaseApk = File(buildDir, "outputs/apk/release/app-release.apk")
        if (!releaseApk.exists()) {
            throw GradleException(
                "[بادر] خطأ: تعذر العثور على ملف APK الناتج عن عملية البناء الحالية في المسار:\n" +
                "${releaseApk.absolutePath}\n" +
                "يرجى التأكد من اكتمال مهمة تجميع إصدار الإنتاج (packageRelease) بنجاح."
            )
        }

        val baderApk = File(targetDir, "بادر.apk")
        releaseApk.copyTo(baderApk, overwrite = true)

        if (!baderApk.exists() || baderApk.length() == 0L) {
            throw GradleException(
                "[بادر] خطأ: فشل إنشاء الملف النهائي في المسار: ${baderApk.absolutePath}"
            )
        }

        println("[بادر] تم بنجاح إنشاء ملف APK النهائي: ${baderApk.absolutePath} (الحجم: ${baderApk.length()} بايت)")
    }
}

tasks.matching { it.name == "assembleRelease" }.configureEach {
    doFirst {
        // حذف أي ملف قديم متبقي من بناء سابق لضمان أن الملف النهائي ناتج حصرياً عن عملية البناء الحالية
        val buildDir = project.layout.buildDirectory.get().asFile
        val staleBaderApk = File(buildDir, "outputs/flutter-apk/بادر.apk")
        if (staleBaderApk.exists()) {
            staleBaderApk.delete()
        }
        val extraApk = File(buildDir, "outputs/apk/release/بادر.apk")
        if (extraApk.exists()) {
            extraApk.delete()
        }
    }
    finalizedBy("copyBaderReleaseApk")
}

