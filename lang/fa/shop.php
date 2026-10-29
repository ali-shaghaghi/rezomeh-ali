<?php

/*
|--------------------------------------------------------------------------
| فروشگاه (محصولات/سبد/checkout) — فارسی
|--------------------------------------------------------------------------
*/

return [

    // ===== صفحه محصولات =====
    'title' => 'محصولات دیجیتال',
    'badge' => 'قالب · CRM · اسکریپت',
    'subtitle' => 'قالب‌ها، نرم‌افزارها و اسکریپت‌های آماده — بلافاصله پس از خرید لایسنس دریافت کنید.',
    'search_in_products' => 'جستجو در محصولات...',
    'all_categories' => 'همه دسته‌ها',
    'all_types' => 'همه انواع',
    'sort' => [
        'newest' => 'جدیدترین',
        'cheapest' => 'ارزان‌ترین',
        'most_expensive' => 'گران‌ترین',
        'popular' => 'محبوب‌ترین',
    ],
    'clear_filters' => 'حذف فیلترها',
    'loading' => 'در حال بارگذاری...',
    'no_products' => 'محصولی با این فیلترها یافت نشد.',
    'show_all_products' => 'نمایش همه محصولات',
    'view_details' => 'مشاهده جزئیات',
    'general' => 'عمومی',
    'no_category' => 'بدون دسته',
    'discount_percent' => '٪:percent تخفیف',
    'version' => 'نسخه :version',
    'category_label' => 'دسته: :category',
    'product_price' => 'قیمت محصول',
    'downloadable' => 'دانلودی',
    'view_demo' => 'مشاهده دموی آنلاین',
    'breadcrumb' => [
        'home' => 'خانه',
        'products' => 'محصولات',
    ],

    // ===== انواع محصول =====
    'types' => [
        'template' => 'قالب',
        'crm' => 'CRM / نرم‌افزار',
        'script' => 'اسکریپت',
        'other' => 'سایر',
    ],

    // ===== انواع لایسنس =====
    'licenses' => [
        'single' => 'تک‌کاربره',
        'multi' => 'چندکاربره',
        'lifetime' => 'مادام‌العمر',
    ],
    'license_label' => 'لایسنس :type',
    'license_activations' => 'لایسنس :type — قابل استفاده روی :count دستگاه',

    // ===== سبد خرید =====
    'cart_title' => 'سبد خرید',
    'cart_badge' => 'سبد خرید',
    'cart_heading' => 'سبد خرید شما',
    'cart_subtitle' => 'محصولات انتخابی شما — می‌توانید تعداد را تغییر دهید و به پرداخت بروید.',
    'cart_empty' => 'سبد خرید شما خالی است',
    'item_not_found' => 'آیتم سبد یافت نشد.',
    'invalid_discount' => 'کد تخفیف نامعتبر است.',
    'invalid_payment_method' => 'روش پرداخت نامعتبر است.',
    'crypto_disabled' => 'پرداخت با ارز دیجیتال در حال حاضر فعال نیست.',
    'cart_empty_desc' => 'برای شروع، محصولات را از فروشگاه انتخاب کنید.',
    'browse_products' => 'مشاهده محصولات',
    'cart_items' => 'آیتم‌های سبد',
    'quantity' => 'تعداد',
    'quantity_colon' => 'تعداد: :count',
    'remove_item' => 'حذف آیتم',
    'clear_cart' => 'خالی کردن سبد',
    'subtotal' => 'جمع کل',
    'payable' => 'مبلغ قابل پرداخت',
    'checkout_now' => 'تکمیل خرید و پرداخت',
    'vat_note' => 'مالیات بر ارزش افزوده در مرحله پایانی محاسبه می‌شود.',
    'added_to_cart' => 'محصول به سبد خرید اضافه شد',
    'adding' => 'در حال افزودن...',
    'added' => '✓ اضافه شد',
    'add_to_cart' => 'افزودن به سبد خرید',
    'please_wait' => 'لطفاً صبر کنید...',
    'online_demo' => 'دموی آنلاین',
    'recommendations' => [
        'special' => 'پیشنهاد ویژه برای شما',
        'similar' => 'شاید این‌ها هم به کارتان بیاید',
        'based_on' => 'بر اساس علاقه شما به:',
        'add' => 'افزودن :name به سبد',
    ],

    // ===== Checkout — مراحل =====
    'steps' => [
        'review' => 'بازبینی سبد',
        'personal' => 'اطلاعات فردی',
        'invoice' => 'صورت‌حساب',
        'payment' => 'پرداخت',
        'review_short' => 'سفارش',
        'personal_short' => 'اطلاعات',
        'invoice_short' => 'فاکتور',
        'payment_short' => 'پرداخت',
        'badge' => 'تکمیل خرید',
        'step_of' => 'مرحله :step — :title',
        'review_desc' => 'محصولات، نوع لایسنس و کد تخفیف خود را بررسی کنید.',
        'personal_desc' => 'اطلاعات تماس شما روی فاکتور ثبت می‌شود.',
        'invoice_desc' => 'جمع، مالیات ۹٪، تخفیف و روش پرداخت را بررسی کنید.',
        'payment_desc' => 'پرداخت را تکمیل کنید و لایسنس محصولات خود را دریافت کنید.',
    ],
    'checkout_title' => 'پرداخت',

    // ===== مرحله ۱ =====
    'review_order' => 'بازبینی سفارش',
    'discount_code' => 'کد تخفیف',
    'discount_code_placeholder' => 'کد تخفیف (مثال: SAVE10)',
    'discount_applied' => 'کد «:code» اعمال شد — ٪:percent تخفیف',
    'remove' => 'حذف',
    'apply_code' => 'اعمال کد',
    'order_summary' => 'خلاصه سفارش',
    'items_subtotal' => 'جمع کالاها',
    'discount' => 'تخفیف',
    'discount_percent_short' => 'تخفیف (٪:percent)',
    'discount_code_short' => 'تخفیف (کد :code)',
    'vat' => 'مالیات بر ارزش افزوده (٪:percent)',
    'vat_short' => 'مالیات (٪:percent)',
    'continue_to_personal' => 'ادامه به اطلاعات فردی',
    'back_to_cart' => 'بازگشت به سبد',
    'back' => 'بازگشت',

    // ===== مرحله ۲ =====
    'personal_info' => 'اطلاعات فردی',
    'personal_info_desc' => 'اطلاعات تماس شما روی فاکتور ثبت می‌شود. این فیلدها از پروفایل شما پر شده‌اند.',
    'full_name' => 'نام و نام خانوادگی *',
    'full_name_placeholder' => 'مثال: علی شقاقی',
    'phone' => 'شماره تلفن *',
    'email_optional' => 'ایمیل (اختیاری)',
    'notes_optional' => 'توضیحات (اختیاری)',
    'notes_placeholder' => 'هر نکته‌ای که لازم است بدانیم...',
    'continue_to_invoice' => 'ادامه به صورت‌حساب',
    'your_cart' => 'سبد شما',

    // ===== مرحله ۳ =====
    'invoice' => 'صورت‌حساب',
    'col_product' => 'محصول',
    'col_license' => 'لایسنس',
    'col_quantity' => 'تعداد',
    'col_price' => 'قیمت',
    'final_amount' => 'مبلغ نهایی',
    'payment_method' => 'روش پرداخت',
    'continue_to_payment' => 'ادامه به پرداخت',

    // ===== مرحله ۴ =====
    'payment' => 'پرداخت',
    'selected_method' => 'روش پرداخت انتخابی شما:',
    'manual_title' => 'پرداخت دستی (کارت به کارت)',
    'manual_desc' => 'پس از ثبت سفارش، شماره کارت و فاکتور برای شما نمایش داده می‌شود. سفارش شما پس از تأیید مدیر فعال می‌شود و لایسنس شما صادر خواهد شد.',
    'crypto_title' => 'پرداخت با کیف پول ارز دیجیتال (USDT)',
    'crypto_desc' => 'مبلغ معادل به USDT پس از ثبت سفارش محاسبه و آدرس کیف پول نمایش داده می‌شود. پس از واریز، تراکنش (TxID) را ارسال کنید تا سفارش تأیید شود.',
    'wallet_address' => 'آدرس کیف پول (:network):',
    'crypto_warning' => '⚠️ لطفاً فقط روی شبکه :network واریز کنید.',
    'installment_title' => 'پرداخت اقساطی (۳۰/۳۰/۴۰)',
    'installment_first' => 'پیش‌پرداخت (۳۰٪)',
    'installment_second' => 'قسط دوم (۳۰٪)',
    'installment_third' => 'قسط سوم (۴۰٪)',
    'installment_desc' => 'سفارش پس از پرداخت کامل تسویه می‌شود و لایسنس صادر می‌گردد.',
    'gateway_title' => 'درگاه آنلاین',
    'gateway_desc' => 'شما به درگاه پرداخت منتقل می‌شوید. پس از پرداخت موفق، لایسنس محصولات شما بلافاصله صادر و سبد شما خالی می‌شود.',
    'pay_now' => 'پرداخت و تکمیل خرید',
    'processing' => 'در حال پردازش...',
    'total' => 'کل',

    // ===== روش‌های پرداخت =====
    'payment_methods' => [
        'gateway' => 'درگاه آنلاین',
        'installment' => 'پرداخت اقساطی (۳۰/۳۰/۴۰)',
        'manual' => 'کارت به کارت / دستی',
        'crypto' => 'کیف پول ارز دیجیتال (USDT)',
    ],

    // ===== تب‌های صفحه محصول (توضیحات/ویژگی‌ها) =====
    'tab_description' => 'توضیحات محصول',
    'tab_features' => 'ویژگی‌ها',
    'features' => [
        'category' => 'دسته‌بندی',
        'product_type' => 'نوع محصول',
        'version' => 'نسخه',
        'licenses' => 'انواع لایسنس',
        'download' => 'دانلود فایل',
        'demo' => 'نمایش دمو',
        'yes' => 'دارد',
        'no' => 'ندارد',
    ],
];
