<div class="space-y-6">
    {{-- ===== پروفایل ===== --}}
    <section class="glass-card p-6 sm:p-8">
        <h3 class="mb-1 flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white">
            <x-icon name="users" />
            اطلاعات پروفایل
        </h3>
        <p class="mb-5 text-xs text-slate-500 dark:text-slate-400">نام و ایمیل حساب کاربری شما</p>
        @livewire('admin.settings.profile-form', key('user-profile-form'))
    </section>

    {{-- ===== رمز عبور ===== --}}
    <section class="glass-card p-6 sm:p-8">
        <h3 class="mb-1 flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white">
            <x-icon name="shield" />
            تغییر رمز عبور
        </h3>
        <p class="mb-5 text-xs text-slate-500 dark:text-slate-400">امنیت حساب خود را با رمز قوی حفظ کنید</p>
        @livewire('admin.settings.change-password-form', key('user-change-password'))
    </section>

    {{-- ===== امنیت پیشرفته ===== --}}
    <section class="glass-card p-6 sm:p-8">
        <h3 class="mb-1 flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white">
            <x-icon name="shield" />
            تنظیمات امنیتی
        </h3>
        <p class="mb-5 text-xs text-slate-500 dark:text-slate-400">
            ورود دو عاملی و تایید ایمیل — این قابلیت‌ها اختیاری‌اند و به‌صورت پیش‌فرض غیرفعال‌اند
        </p>
        @livewire('admin.settings.security-toggles', ['theme' => 'light'], key('user-security-toggles'))
    </section>
</div>
