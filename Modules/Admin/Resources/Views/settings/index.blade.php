@extends('admin::layouts.master')

@section('title', 'تنظیمات')

@section('content')
    <div class="mb-8">
        <h2 class="text-2xl font-bold sm:text-3xl">تنظیمات</h2>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Sidebar Navigation --}}
        <aside class="space-y-2 lg:col-span-1">
            <button type="button" class="nav-link active w-full" onclick="showTab('profile')">
                <x-icon name="users" />
                پروفایل
            </button>
            <button type="button" class="nav-link w-full" onclick="showTab('appearance')">
                <x-icon name="palette" />
                ظاهر
            </button>
            <button type="button" class="nav-link w-full" onclick="showTab('security')">
                <x-icon name="shield" />
                امنیت
            </button>
        </aside>

        {{-- Content --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Profile Tab --}}
            <div id="tab-profile" class="space-y-6">
                <section class="chart-card observe-in">
                    <h3 class="mb-4 flex items-center gap-2 text-lg font-bold">
                        <x-icon name="users" />
                        اطلاعات پروفایل
                    </h3>
                    <livewire:admin.settings.profile-form />
                </section>

                <section class="chart-card observe-in">
                    <h3 class="mb-4 flex items-center gap-2 text-lg font-bold">
                        <x-icon name="users" />
                        تصویر پروفایل
                    </h3>
                    <livewire:admin.settings.avatar-form />
                </section>
            </div>

            {{-- Appearance Tab --}}
            <div id="tab-appearance" class="space-y-6 hidden">
                <section class="chart-card observe-in">
                    <h3 class="mb-4 flex items-center gap-2 text-lg font-bold">
                        <x-icon name="palette" />
                        پیکربندی ظاهر
                    </h3>
                    <livewire:admin.settings.logo-form />
                </section>

                <section class="chart-card observe-in">
                    <h3 class="mb-4 flex items-center gap-2 text-lg font-bold">
                        <x-icon name="palette" />
                        تم سایت
                    </h3>
                    <button type="button" data-theme-toggle class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm transition hover:border-primary-300 dark:border-slate-700">
                        <span class="dark:hidden"><x-icon name="moon" /></span>
                        <span class="hidden dark:inline"><x-icon name="sun" /></span>
                        تغییر تم
                    </button>
                </section>
            </div>

            {{-- Security Tab --}}
            <div id="tab-security" class="space-y-6 hidden">
                <section class="chart-card observe-in">
                    <h3 class="mb-4 flex items-center gap-2 text-lg font-bold">
                        <x-icon name="shield" />
                        تغییر رمز عبور
                    </h3>
                    <livewire:admin.auth.change-password />
                </section>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showTab(tabName) {
            document.querySelectorAll('[id^="tab-"]').forEach(el => el.classList.add('hidden'));
            document.getElementById('tab-' + tabName).classList.remove('hidden');
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
            event.target.closest('.nav-link').classList.add('active');
        }
    </script>
    @endpush
@endsection