@props([
    'title' => null,
    'description' => null,
    'image' => null,
])

@php
    $siteName = setting('site_name', 'بــــادر');
    $computedTitle = !empty($title) ? $title . ' - ' . $siteName : setting('meta_title', $siteName . ' - البوابة الوطنية الموحدة للبلاغات والشكاوى التنموية');
    $metaDesc = !empty($description) ? $description : setting('meta_description', 'منصة بادر - المنظومة الوطنية الذكية لربط المواطن بالجهات الحكومية، رصد الشكاوى وإشراك المجتمع في الرقابة ومتابعة المشاريع.');
    $metaKeywords = setting('meta_keywords', 'بادر, بلاغات, شكاوى, اليمن, مشاريع تنموية, رقابة مجتمعية');
    $metaAuthor = setting('meta_author', 'الجمهورية اليمنية - منصة بادر');
    $metaRobots = setting('meta_robots', 'index, follow');
    $canonicalUrl = setting('canonical_url') ?: url()->current();

    $faviconUrl = setting_url('site_favicon');
    $appleIconUrl = setting_url('site_apple_icon') ?: $faviconUrl;
    $ogImageUrl = !empty($image) ? $image : setting_url('og_image');
    $ogTitle = setting('og_title') ?: $computedTitle;
    $ogDesc = setting('og_description') ?: $metaDesc;

    $twitterCard = setting('twitter_card', 'summary_large_image');
    $twitterSite = setting('twitter_site', '');


    $googleVerification = setting('google_site_verification');
    $googleAnalyticsId = setting('google_analytics_id');
    $customHeadScripts = setting('custom_head_scripts');
@endphp

<!-- Primary Document Title & Standard Meta Tags -->
<title>{{ $computedTitle }}</title>
<meta name="description" content="{{ $metaDesc }}">
@if($metaKeywords)
<meta name="keywords" content="{{ $metaKeywords }}">
@endif
@if($metaAuthor)
<meta name="author" content="{{ $metaAuthor }}">
@endif
<meta name="robots" content="{{ $metaRobots }}">
<meta name="theme-color" content="#CE1126">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="{{ $siteName }}">
<meta name="application-name" content="{{ $siteName }}">

<!-- Favicon & Mobile Icons -->
@if($faviconUrl)
<link rel="icon" href="{{ $faviconUrl }}">
@else
<link rel="icon" href="{{ asset('favicon.ico') }}">
@endif

@if($appleIconUrl)
<link rel="apple-touch-icon" href="{{ $appleIconUrl }}">
@endif

<link rel="canonical" href="{{ $canonicalUrl }}">

<!-- Open Graph / Facebook / WhatsApp Meta Tags -->
<meta property="og:locale" content="ar_YE">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDesc }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
@if($ogImageUrl)
<meta property="og:image" content="{{ $ogImageUrl }}">
@endif

<!-- Twitter / X Card Meta Tags -->
<meta name="twitter:card" content="{{ $twitterCard }}">
@if($twitterSite)
<meta name="twitter:site" content="{{ $twitterSite }}">
@endif

<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDesc }}">
@if($ogImageUrl)
<meta name="twitter:image" content="{{ $ogImageUrl }}">
@endif

<!-- Search Engine Verification -->
@if($googleVerification)
<meta name="google-site-verification" content="{{ $googleVerification }}">
@endif

<!-- Analytics & Custom Scripts Injection -->
@if($googleAnalyticsId)
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $googleAnalyticsId }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{ $googleAnalyticsId }}');
</script>
@endif

@if($customHeadScripts)
{!! $customHeadScripts !!}
@endif
