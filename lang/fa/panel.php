<?php

/*
|--------------------------------------------------------------------------
| پنل کاربر — فارسی (داشبورد، سفارش‌ها، تیکت‌ها و عمومی)
|--------------------------------------------------------------------------
*/

return [
    // تب‌های سایدبار شل
    'tabs' => [
        'dashboard' => 'داشبورد',
        'orders' => 'سفارش‌ها',
        'products' => 'محصولات من (لایسنس‌ها)',
        'projects' => 'پروژه‌های من',
        'tickets' => 'تیکت‌ها',
        'consultations' => 'مشاوره‌های من',
        'notifications' => 'اعلان‌ها',
        'security' => 'تنظیمات',
    ],
    'open_menu' => 'باز کردن منوی پنل کاربری',
    'logout' => 'خروج از حساب',
    'logout_confirm' => 'آیا از حساب خود خارج می‌شوید؟',

    // داشبورد
    'greeting' => 'سلام، :name 👋',
    'realtime_hint' => 'سوییچ ریل‌تایم بین بخش‌ها — بدون بارگذاری صفحه',
    'stats' => [
        'orders_total' => 'کل سفارش‌ها',
        'orders_active' => 'در جریان',
        'orders_completed' => 'تکمیل‌شده',
        'total_spent' => 'پرداخت (تومان)',
        'tickets_total' => 'کل تیکت‌ها',
        'tickets_open' => 'تیکت باز',
    ],
    'need_consultation' => 'نیاز به مشاوره دارید؟',
    'need_consultation_desc' => 'جلسه مشاوره تخصصی خود را آنلاین رزرو کنید',
    'request_consultation' => 'درخواست مشاوره',
    'latest_orders' => 'آخرین سفارش‌ها',
    'latest_tickets' => 'آخرین تیکت‌ها',
    'view_all' => 'همه ←',
    'no_orders_yet' => 'هنوز سفارشی نداری.',
    'no_tickets_yet' => 'تیکتی نداری.',
    'request_project' => 'ثبت درخواست پروژه',
    'create_new_ticket' => 'ایجاد تیکت جدید',

    // سفارش‌ها
    'my_orders' => 'سفارش‌های من',
    'new_order' => 'سفارش جدید',
    'search_orders' => 'جستجو در شماره یا عنوان سفارش...',
    'filter_all' => 'همه',
    'filter_pending' => 'در انتظار',
    'filter_review' => 'در بررسی',
    'filter_accepted' => 'پذیرفته‌شده',
    'filter_developing' => 'در توسعه',
    'filter_completed' => 'تکمیل‌شده',
    'filter_cancelled' => 'لغوشده',
    'paid' => 'تسویه‌شده',
    'amount' => 'مبلغ:',
    'toman' => 'تومان',
    'deadline' => 'ددلاین:',
    'paid_amount' => 'پرداخت‌شده: :amount تومان',
    'view_order' => 'مشاهده سفارش',
    'no_orders' => 'سفارشی پیدا نشد!',
    'no_orders_desc' => 'هنوز سفارشی ثبت نکرده‌ای.',
    'change_filters' => 'فیلترها را تغییر دهید.',
    'create_order' => 'ثبت سفارش جدید',

    // تیکت‌ها
    'my_tickets' => 'تیکت‌های من',
    'new_ticket' => 'تیکت جدید',
    'filter_open' => 'باز',
    'filter_in_progress' => 'در حال بررسی',
    'filter_waiting' => 'در انتظار پاسخ شما',
    'filter_closed' => 'بسته',
    'ticket_status_open' => 'باز',
    'ticket_status_in_progress' => 'در حال بررسی',
    'ticket_status_waiting' => 'در انتظار پاسخ شما',
    'ticket_status_closed' => 'بسته',
    'priority_low' => 'اولویت: پایین',
    'priority_medium' => 'اولویت: متوسط',
    'priority_high' => 'اولویت: بالا',
    'priority_urgent' => 'اولویت: فوری',
    'replies' => ':count پاسخ',
    'view_conversation' => 'مشاهده گفتگو',
    'date' => 'تاریخ:',
];
