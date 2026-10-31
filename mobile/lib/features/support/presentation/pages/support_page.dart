import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../../core/constants/app_colors.dart';

class SupportPage extends StatefulWidget {
  const SupportPage({super.key});

  @override
  State<SupportPage> createState() => _SupportPageState();
}

class _SupportPageState extends State<SupportPage> {
  final TextEditingController _subjectController = TextEditingController();
  final TextEditingController _messageController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  static const String _supportPhone = '777235077';
  static const String _supportEmail = 'support@bader.ameen-dev.com';

  String _selectedCategory = 'all';
  String _selectedTicketType = 'general';
  bool _isSubmitting = false;

  final List<Map<String, String>> _categories = [
    {'id': 'all', 'label': 'الكل'},
    {'id': 'complaints', 'label': 'البلاغات والشكاوى'},
    {'id': 'projects', 'label': 'المشاريع المجتمعية'},
    {'id': 'account', 'label': 'الحساب والأمان'},
  ];

  final List<Map<String, String>> _faqs = [
    {
      'category': 'complaints',
      'question': 'كيف يمكنني تقديم بلاغ جديد؟',
      'answer': 'يمكنك تقديم بلاغ جديد بالضغط على زر (+) "بلاغ جديد" في الشريط السفلي. قم بتحديد نوع المشكلة، وإرفاق صورة توضيحية، ثم تأكيد موقع البلاغ على الخريطة والضغط على "إرسال البلاغ".',
    },
    {
      'category': 'complaints',
      'question': 'كم يستغرق الرد ومراجعة البلاغات؟',
      'answer': 'تتم مراجعة وتدقيق البلاغات خلال 24 ساعة من تاريخ الإرسال من قبل فريق العمليات، ثم يتم توجيهها فوراً إلى الجهة والموظف الميداني المختص لمباشرة الحل.',
    },
    {
      'category': 'complaints',
      'question': 'كيف أتابع حالة البلاغ بعد إرساله؟',
      'answer': 'من خلال الذهاب إلى قسم "متابعة البلاغات" أو عبر تبويب "البلاغات" في الشريط السفلي، حيث يمكنك مشاهدة التحديثات وصور ما قبل وبعد الإصلاحات مباشرة.',
    },
    {
      'category': 'projects',
      'question': 'ما هي المشاريع المجتمعية وكيف أساهم فيها؟',
      'answer': 'المشاريع المجتمعية هي مبادرات تنموية ومحلية لتحسين البنية التحتية والخدمات. يمكنك تصفح المشاريع في قسم "المشاريع" والاطلاع على نسب الإنجاز والمساهمة في دعمها.',
    },
    {
      'category': 'account',
      'question': 'هل بياناتي الشخصية ورقم هاتفي محمية؟',
      'answer': 'نعم بالتأكيد، يتم التعامل مع جميع البيانات الشخصية بسرية وأمان تام وتشفير متقدم وفقاً لمعايير الخصوصية، ولا تظهر للعامة إلا معلومات البلاغ وموقعه الجغرافي لتحسين الخدمات العامة.',
    },
    {
      'category': 'account',
      'question': 'كيف أقوم بتعديل بيانات حسابي أو كلمة المرور؟',
      'answer': 'يمكنك الدخول إلى "الملف الشخصي" من القائمة أو الشريط السفلي، ثم الضغط على "تعديل الملف الشخصي" لتحديث الاسم، رقم الهاتف أو تغيير كلمة المرور.',
    },
  ];

  @override
  void dispose() {
    _subjectController.dispose();
    _messageController.dispose();
    super.dispose();
  }

  Future<void> _makeCall(String phoneNumber) async {
    final uri = Uri.parse('tel:$phoneNumber');
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri);
      } else {
        _copyToClipboard(phoneNumber, 'تم نسخ رقم التواصل: $phoneNumber');
      }
    } catch (_) {
      _copyToClipboard(phoneNumber, 'تم نسخ رقم التواصل: $phoneNumber');
    }
  }

  Future<void> _sendEmail(String email) async {
    final uri = Uri.parse('mailto:$email?subject=استفسار بخصوص منصة بادر');
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri);
      } else {
        _copyToClipboard(email, 'تم نسخ البريد الإلكتروني: $email');
      }
    } catch (_) {
      _copyToClipboard(email, 'تم نسخ البريد الإلكتروني: $email');
    }
  }

  void _copyToClipboard(String text, String message) {
    Clipboard.setData(ClipboardData(text: text));
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          message,
          textAlign: TextAlign.right,
          style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600),
        ),
        backgroundColor: AppColors.yemenBlack,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
    );
  }

  void _submitTicket() {
    if (_formKey.currentState?.validate() != true) return;

    setState(() => _isSubmitting = true);

    Future.delayed(const Duration(milliseconds: 900), () {
      if (!mounted) return;
      setState(() => _isSubmitting = false);
      _subjectController.clear();
      _messageController.clear();

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Row(
            children: [
              Icon(Icons.check_circle_rounded, color: Colors.white, size: 18),
              SizedBox(width: 10),
              Expanded(
                child: Text(
                  'تم إرسال رسالتك بنجاح! سيتواصل معك فريق الدعم قريباً.',
                  textAlign: TextAlign.right,
                  style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5),
                ),
              ),
            ],
          ),
          backgroundColor: AppColors.yemenEmerald,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          duration: const Duration(seconds: 4),
        ),
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    final filteredFaqs = _selectedCategory == 'all'
        ? _faqs
        : _faqs.where((f) => f['category'] == _selectedCategory).toList();

    return Scaffold(
      backgroundColor: AppColors.backgroundSubtle,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0.5,
        surfaceTintColor: Colors.transparent,
        centerTitle: true,
        shape: Border(
          bottom: BorderSide(
            color: AppColors.borderSubtle.withValues(alpha: 0.8),
            width: 0.8,
          ),
        ),
        leading: IconButton(
          icon: const Icon(
            Icons.arrow_back_ios_new_rounded,
            color: AppColors.yemenBlack,
            size: 18,
          ),
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/home');
            }
          },
        ),
        title: const Text(
          'الدعم والمساعدة',
          style: TextStyle(
            fontSize: 16.5,
            fontWeight: FontWeight.w800,
            color: AppColors.yemenBlack,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // 1. Executive Support Header Banner
            _buildHeaderBanner(),

            const SizedBox(height: 22),

            // 2. Direct Contact Channels
            _buildSectionHeader(
              title: 'قنوات التواصل المباشر',
              subtitle: 'اضغط للاتصال أو المراسلة الفورية',
            ),
            const SizedBox(height: 10),
            _buildContactCards(),

            const SizedBox(height: 24),

            // 3. FAQs Section
            _buildSectionHeader(
              title: 'الأسئلة الشائعة',
              subtitle: 'إجابات سريعة حول أبرز الاستفسارات',
            ),
            const SizedBox(height: 10),
            _buildCategoryTabs(),
            const SizedBox(height: 10),
            _buildFaqContainer(filteredFaqs),

            const SizedBox(height: 24),

            // 4. Direct Support Ticket Form
            _buildSectionHeader(
              title: 'إرسال تذكرة دعم',
              subtitle: 'تواصل مع الفريق الفني أوشاركنا مقترحاتك',
            ),
            const SizedBox(height: 10),
            _buildContactForm(),

            const SizedBox(height: 22),

            // 5. Minimalist Footer
            _buildSupportFooter(),
            const SizedBox(height: 12),
          ],
        ),
      ),
    );
  }

  Widget _buildHeaderBanner() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.yemenBlack,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(
                    color: Colors.white.withValues(alpha: 0.12),
                    width: 0.8,
                  ),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CircleAvatar(
                      radius: 3.5,
                      backgroundColor: AppColors.yemenEmerald,
                    ),
                    SizedBox(width: 6),
                    Text(
                      'متاح على مدار الساعة 24/7',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
              Icon(
                Icons.support_agent_rounded,
                color: Colors.white.withValues(alpha: 0.85),
                size: 22,
              ),
            ],
          ),
          const SizedBox(height: 14),
          const Text(
            'مركز خدمة ودعم المستفيدين',
            style: TextStyle(
              fontSize: 16.5,
              fontWeight: FontWeight.w800,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'فريق منصة بادر جاهز للرد على استفساراتكم ومتابعة البلاغات والملاحظات الفنية.',
            style: TextStyle(
              fontSize: 12.5,
              color: Colors.white.withValues(alpha: 0.72),
              height: 1.45,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionHeader({
    required String title,
    required String subtitle,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 2),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          Text(
            title,
            style: const TextStyle(
              fontSize: 14.5,
              fontWeight: FontWeight.w800,
              color: AppColors.yemenBlack,
            ),
          ),
          Text(
            subtitle,
            style: const TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w500,
              color: AppColors.textSecondary,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildContactCards() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.borderSubtle, width: 0.8),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.015),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        children: [
          _buildContactRow(
            icon: Icons.phone_in_talk_outlined,
            accentColor: AppColors.yemenEmerald,
            label: 'رقم التواصل المباشر',
            value: _supportPhone,
            actionLabel: 'اتصال',
            onActionTap: () => _makeCall(_supportPhone),
            onCopyTap: () => _copyToClipboard(
              _supportPhone,
              'تم نسخ رقم التواصل: $_supportPhone',
            ),
          ),
          const Divider(
            height: 1,
            thickness: 0.8,
            color: AppColors.borderSubtle,
          ),
          _buildContactRow(
            icon: Icons.alternate_email_rounded,
            accentColor: AppColors.yemenRed,
            label: 'البريد الإلكتروني الرسمي',
            value: _supportEmail,
            actionLabel: 'مراسلة',
            onActionTap: () => _sendEmail(_supportEmail),
            onCopyTap: () => _copyToClipboard(
              _supportEmail,
              'تم نسخ البريد الإلكتروني: $_supportEmail',
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildContactRow({
    required IconData icon,
    required Color accentColor,
    required String label,
    required String value,
    required String actionLabel,
    required VoidCallback onActionTap,
    required VoidCallback onCopyTap,
  }) {
    return InkWell(
      onTap: onActionTap,
      borderRadius: BorderRadius.circular(12),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 13),
        child: Row(
          children: [
            Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: accentColor.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(icon, color: accentColor, size: 19),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    label,
                    style: const TextStyle(
                      fontSize: 11.5,
                      color: AppColors.textSecondary,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    value,
                    style: const TextStyle(
                      fontSize: 13.5,
                      fontWeight: FontWeight.w800,
                      color: AppColors.yemenBlack,
                      fontFamily: 'monospace',
                    ),
                  ),
                ],
              ),
            ),
            IconButton(
              tooltip: 'نسخ',
              onPressed: onCopyTap,
              visualDensity: VisualDensity.compact,
              icon: const Icon(
                Icons.copy_rounded,
                size: 16,
                color: AppColors.textSecondary,
              ),
            ),
            const SizedBox(width: 4),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: AppColors.yemenBlack,
                borderRadius: BorderRadius.circular(6),
              ),
              child: Text(
                actionLabel,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 11.5,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCategoryTabs() {
    return SizedBox(
      height: 36,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: _categories.length,
        separatorBuilder: (_, _) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          final category = _categories[index];
          final isSelected = _selectedCategory == category['id'];

          return GestureDetector(
            onTap: () => setState(() => _selectedCategory = category['id']!),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 180),
              padding: const EdgeInsets.symmetric(horizontal: 14),
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: isSelected ? AppColors.yemenBlack : Colors.white,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(
                  color: isSelected
                      ? AppColors.yemenBlack
                      : AppColors.borderSubtle,
                  width: 0.8,
                ),
              ),
              child: Text(
                category['label']!,
                style: TextStyle(
                  color: isSelected ? Colors.white : AppColors.textSecondary,
                  fontSize: 12,
                  fontWeight: isSelected ? FontWeight.w700 : FontWeight.w600,
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildFaqContainer(List<Map<String, String>> faqs) {
    if (faqs.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.borderSubtle, width: 0.8),
        ),
        alignment: Alignment.center,
        child: const Text(
          'لا توجد أسئلة في هذا التصنيف',
          style: TextStyle(color: AppColors.textSecondary, fontSize: 12.5),
        ),
      );
    }

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.borderSubtle, width: 0.8),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.015),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: ListView.separated(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        padding: EdgeInsets.zero,
        itemCount: faqs.length,
        separatorBuilder: (_, _) => const Divider(
          height: 1,
          thickness: 0.8,
          color: AppColors.borderSubtle,
        ),
        itemBuilder: (context, index) {
          final faq = faqs[index];
          return Theme(
            data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
            child: ExpansionTile(
              tilePadding: const EdgeInsets.symmetric(
                horizontal: 16,
                vertical: 2,
              ),
              childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 14),
              iconColor: AppColors.yemenRed,
              collapsedIconColor: AppColors.textSecondary,
              title: Text(
                faq['question']!,
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: AppColors.yemenBlack,
                ),
              ),
              children: [
                Align(
                  alignment: Alignment.centerRight,
                  child: Text(
                    faq['answer']!,
                    style: const TextStyle(
                      fontSize: 12.5,
                      height: 1.6,
                      color: AppColors.textSecondary,
                    ),
                    textAlign: TextAlign.right,
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildContactForm() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.borderSubtle, width: 0.8),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.015),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text(
              'تصنيف الرسالة',
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w700,
                color: AppColors.yemenBlack,
              ),
            ),
            const SizedBox(height: 8),

            // Segmented Selector Track
            Container(
              padding: const EdgeInsets.all(3),
              decoration: BoxDecoration(
                color: AppColors.backgroundSubtle,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: AppColors.borderSubtle, width: 0.8),
              ),
              child: Row(
                children: [
                  _buildSegmentOption('general', 'استفسار عام'),
                  _buildSegmentOption('technical', 'مشكلة فنية'),
                  _buildSegmentOption('suggestion', 'مقترح تطوير'),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // Subject Field
            const Text(
              'الموضوع',
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w700,
                color: AppColors.yemenBlack,
              ),
            ),
            const SizedBox(height: 6),
            TextFormField(
              controller: _subjectController,
              textAlign: TextAlign.right,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: AppColors.yemenBlack,
              ),
              decoration: _inputDecoration(
                hint: 'مثال: استفسار حول متابعة بلاغ سابق',
              ),
              validator: (val) {
                if (val == null || val.trim().isEmpty) {
                  return 'يرجى كتابة موضوع الرسالة';
                }
                return null;
              },
            ),

            const SizedBox(height: 14),

            // Message Body Field
            const Text(
              'التفاصيل',
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w700,
                color: AppColors.yemenBlack,
              ),
            ),
            const SizedBox(height: 6),
            TextFormField(
              controller: _messageController,
              textAlign: TextAlign.right,
              maxLines: 4,
              style: const TextStyle(
                fontSize: 13,
                height: 1.5,
                color: AppColors.yemenBlack,
              ),
              decoration: _inputDecoration(
                hint: 'اشرح استفسارك أو المشكلة التي واجهتك بالتفصيل...',
              ),
              validator: (val) {
                if (val == null || val.trim().isEmpty) {
                  return 'يرجى كتابة تفاصيل الرسالة';
                }
                return null;
              },
            ),

            const SizedBox(height: 18),

            ElevatedButton(
              onPressed: _isSubmitting ? null : _submitTicket,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.yemenRed,
                disabledBackgroundColor: AppColors.yemenRed.withValues(
                  alpha: 0.4,
                ),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
                elevation: 0,
              ),
              child: _isSubmitting
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : const Text(
                      'إرسال الرسالة',
                      style: TextStyle(
                        fontSize: 13.5,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
            ),
          ],
        ),
      ),
    );
  }

  InputDecoration _inputDecoration({required String hint}) {
    return InputDecoration(
      hintText: hint,
      hintStyle: const TextStyle(
        fontSize: 12,
        color: AppColors.textMuted,
        fontWeight: FontWeight.normal,
      ),
      filled: true,
      fillColor: AppColors.backgroundSubtle,
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: AppColors.borderSubtle, width: 0.8),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: AppColors.borderSubtle, width: 0.8),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: AppColors.yemenRed, width: 1.0),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: AppColors.yemenRed, width: 0.8),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: AppColors.yemenRed, width: 1.0),
      ),
    );
  }

  Widget _buildSegmentOption(String value, String label) {
    final isSelected = _selectedTicketType == value;
    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() => _selectedTicketType = value),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 160),
          padding: const EdgeInsets.symmetric(vertical: 8),
          decoration: BoxDecoration(
            color: isSelected ? Colors.white : Colors.transparent,
            borderRadius: BorderRadius.circular(6),
            boxShadow: isSelected
                ? [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.05),
                      blurRadius: 4,
                      offset: const Offset(0, 1),
                    ),
                  ]
                : null,
          ),
          alignment: Alignment.center,
          child: Text(
            label,
            style: TextStyle(
              fontSize: 11.5,
              fontWeight: isSelected ? FontWeight.w800 : FontWeight.w500,
              color: isSelected
                  ? AppColors.yemenBlack
                  : AppColors.textSecondary,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSupportFooter() {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.borderSubtle, width: 0.8),
      ),
      child: const Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.verified_user_outlined,
            color: AppColors.yemenEmerald,
            size: 16,
          ),
          SizedBox(width: 8),
          Text(
            'منصة بادر — نظام البلاغات والمشاريع المجتمعية',
            style: TextStyle(
              fontSize: 11.5,
              fontWeight: FontWeight.w600,
              color: AppColors.textSecondary,
            ),
          ),
        ],
      ),
    );
  }
}
