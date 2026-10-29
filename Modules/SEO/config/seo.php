<?php

/*
|--------------------------------------------------------------------------
| تنظیمات ماژول SEO
|--------------------------------------------------------------------------
| قابل استفاده مجدد در پروژه‌های فروشگاهی، سازمانی، ویزیت و مشاوره.
| هر پروژه فقط باید brand و default_schema را تنظیم کند.
*/

return [
    // برند پیش‌فرض (در تنظیمات ادمین قابل بازنویسی است)
    'brand' => [
        'name' => 'علی شقاقی',
        'name_en' => 'Ali Shaghaghi',
        'type' => 'Person', // Person | Organization
        'logo' => '', // مسیر لوگو در storage
    ],

    // اسکیمای پیش‌فرض کل سایت (در تنظیمات ادمین قابل تغییر)
    'default_schema' => 'ProfessionalService',

    // خزنده‌های هوش مصنوعی — در robots.txt اجازه داده می‌شوند (#AI SEO)
    'ai_bots' => [
        'GPTBot',          // OpenAI
        'OAI-SearchBot',   // OpenAI Search
        'ChatGPT-User',    // ChatGPT browsing
        'ClaudeBot',       // Anthropic
        'Claude-Web',      // Anthropic (legacy)
        'anthropic-ai',    // Anthropic
        'PerplexityBot',   // Perplexity
        'Google-Extended', // Google AI (Gemini)
        'Applebot-Extended', // Apple Intelligence
        'Bytespider',      // ByteDance
        'CCBot',           // Common Crawl
    ],

    // صفحاتی که در robots.txt مسدود می‌شوند
    'disallow_paths' => [
        '/admin',
        '/dashboard',
        '/checkout',
        '/cart',
        '/auth',
        '/storage/products/file*', // فایل‌های دانلودی محصول
    ],

    // آستانه‌های سئو
    'meta' => [
        'title_max' => 60,
        'description_max' => 160,
        'keywords_max' => 20,
    ],
];
