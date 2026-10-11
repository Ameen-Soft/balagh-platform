import 'package:flutter/material.dart';
import '../../../../core/constants/app_colors.dart';

/// Clean, responsive authentication text field matching the Figma/mockup design:
/// - Rounded rectangular border (12px)
/// - Left-aligned leading category icon (Person, Email, Lock)
/// - Right-aligned Arabic hint/text (RTL)
/// - Optional password visibility toggle placed on the right side
class AuthTextField extends StatefulWidget {
  final TextEditingController? controller;
  final String hintText;
  final IconData leadingIcon;
  final bool isPassword;
  final TextInputType keyboardType;
  final TextInputAction textInputAction;
  final String? Function(String?)? validator;
  final void Function(String)? onChanged;
  final void Function(String)? onFieldSubmitted;
  final bool enabled;
  final Iterable<String>? autofillHints;
  final FocusNode? focusNode;

  const AuthTextField({
    super.key,
    this.controller,
    required this.hintText,
    required this.leadingIcon,
    this.isPassword = false,
    this.keyboardType = TextInputType.text,
    this.textInputAction = TextInputAction.next,
    this.validator,
    this.onChanged,
    this.onFieldSubmitted,
    this.enabled = true,
    this.autofillHints,
    this.focusNode,
  });

  @override
  State<AuthTextField> createState() => _AuthTextFieldState();
}

class _AuthTextFieldState extends State<AuthTextField> {
  bool _obscureText = true;

  @override
  void initState() {
    super.initState();
    _obscureText = widget.isPassword;
  }

  @override
  Widget build(BuildContext context) {
    // Force RTL directionality inside the field to guarantee identical layout
    // (leading icon on the left via suffixIcon, hint/text on the right)
    return Directionality(
      textDirection: TextDirection.rtl,
      child: TextFormField(
        controller: widget.controller,
        focusNode: widget.focusNode,
        obscureText: widget.isPassword ? _obscureText : false,
        keyboardType: widget.keyboardType,
        textInputAction: widget.textInputAction,
        enabled: widget.enabled,
        autofillHints: widget.autofillHints,
        validator: widget.validator,
        onChanged: widget.onChanged,
        onFieldSubmitted: widget.onFieldSubmitted,
        textAlign: TextAlign.right,
        style: const TextStyle(
          fontSize: 14.5,
          fontWeight: FontWeight.w500,
          color: AppColors.textPrimary,
        ),
        decoration: InputDecoration(
          hintText: widget.hintText,
          hintTextDirection: TextDirection.rtl,
          hintStyle: const TextStyle(
            fontSize: 13.5,
            fontWeight: FontWeight.normal,
            color: AppColors.textMuted,
          ),
          filled: true,
          fillColor: widget.enabled ? const Color(0xFFFCFCFD) : const Color(0xFFF3F4F6),
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 16,
            vertical: 15,
          ),
          // In RTL, suffixIcon appears at the FAR LEFT edge of the text field
          suffixIcon: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14),
            child: Icon(
              widget.leadingIcon,
              color: AppColors.textMuted,
              size: 20,
            ),
          ),
          suffixIconConstraints: const BoxConstraints(
            minWidth: 44,
            minHeight: 44,
          ),
          // In RTL, prefixIcon appears on the RIGHT edge of the text field
          prefixIcon: widget.isPassword
              ? IconButton(
                  icon: Icon(
                    _obscureText
                        ? Icons.visibility_off_outlined
                        : Icons.visibility_outlined,
                    color: AppColors.textMuted,
                    size: 19,
                  ),
                  tooltip: _obscureText ? 'إظهار كلمة المرور' : 'إخفاء كلمة المرور',
                  onPressed: widget.enabled
                      ? () {
                          setState(() {
                            _obscureText = !_obscureText;
                          });
                        }
                      : null,
                )
              : null,
          prefixIconConstraints: widget.isPassword
              ? const BoxConstraints(minWidth: 44, minHeight: 44)
              : null,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: AppColors.borderSubtle, width: 1.0),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: AppColors.borderSubtle, width: 1.0),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: AppColors.yemenRed, width: 1.5),
          ),
          errorBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: AppColors.yemenRed, width: 1.2),
          ),
          focusedErrorBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: AppColors.yemenRed, width: 1.5),
          ),
          errorStyle: const TextStyle(
            fontSize: 11.5,
            fontWeight: FontWeight.w500,
            color: AppColors.yemenRed,
          ),
        ),
      ),
    );
  }
}
