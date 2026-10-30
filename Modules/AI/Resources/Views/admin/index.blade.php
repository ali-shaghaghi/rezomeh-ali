@extends('adminpanel::layouts.master')

@section('title', 'دستیار هوشمند')

@section('content')
    <div class="mb-8">
        <h2 class="text-2xl font-bold sm:text-3xl">دستیار هوشمند (AI Agent)</h2>
        <p class="mt-1 text-slate-500 dark:text-slate-400">تولید مقاله، تحلیل پروژه‌ها و انتشار در تلگرام — همه در یک مرکز</p>
    </div>

    <div class="space-y-8">
        @if (! app(\Modules\AI\Services\AiService::class)->isEnabled())
            <div class="flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm dark:border-amber-800 dark:bg-amber-950/30">
                <x-icon name="bell" size="5" class="shrink-0 text-amber-500" />
                <div>
                    <p class="font-bold text-amber-700 dark:text-amber-400">هوش مصنوعی فعال نیست</p>
                    <p class="text-xs text-amber-600/90 dark:text-amber-400/80">برای استفاده از ابزارها، کلید API را در <a href="{{ route('admin.settings.group', 'ai') }}" class="font-bold underline">تنظیمات → هوش مصنوعی</a> وارد کنید. اتصال تلگرام مستقل از این تنظیم است.</p>
                </div>
            </div>
        @endif

        {{-- ابزار ۱: تولید مقاله --}}
        <section class="chart-card">
            <div class="mb-4 flex items-center gap-2">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                    <x-icon name="edit" size="5" />
                </span>
                <div>
                    <h3 class="text-lg font-bold">تولید مقاله سئو شده</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">مقاله دو‌زبانه با ساختار قالب سایت — قابل ذخیره مستقیم به‌عنوان پیش‌نویس وبلاگ</p>
                </div>
            </div>
            @livewire('ai.article-writer', key('ai-article-writer'))
        </section>

        {{-- ابزار ۲: دستیار پروژه --}}
        <section class="chart-card">
            <div class="mb-4 flex items-center gap-2">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-400">
                    <x-icon name="orders" size="5" />
                </span>
                <div>
                    <h3 class="text-lg font-bold">دستیار مدیریت پروژه‌ها</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">تحلیل درخواست‌های در انتظار + پیشنهاد قیمت، زمان و تکنولوژی</p>
                </div>
            </div>
            @livewire('ai.project-assistant', key('ai-project-assistant'))
        </section>

        {{-- ابزار ۳: تلگرام --}}
        <section class="chart-card">
            <div class="mb-4 flex items-center gap-2">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
                </span>
                <div>
                    <h3 class="text-lg font-bold">اتصال تلگرام و انتشار خودکار</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">اتصال ربات به کانال، انتشار مقاله/محصول جدید و تولید کپشن تبلیغاتی با AI</p>
                </div>
            </div>
            @livewire('ai.telegram-publisher', key('ai-telegram-publisher'))
        </section>
    </div>
@endsection
