<x-public.layout :title="'درخواست پروژه جدید'">
    {{-- ===== هدر ===== --}}
    <section class="mb-8">
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">درخواست پروژه جدید</h1>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            فرم زیر را تکمیل کنید؛ پس از بررسی کارشناس، قیمت نهایی و برنامه پرداخت قسطی (۳۰٪ پیش‌پرداخت، ۳۰٪ نیمه‌کار، ۴۰٪ تحویل) اعلام می‌شود.
        </p>
    </section>

    <livewire:public.projectdev.request-form />
</x-public.layout>

