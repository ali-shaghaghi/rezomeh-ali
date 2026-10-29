<x-userpanel.layout :title="__('panel.tabs.dashboard')">
    {{-- ===== هدر خوش‌آمد ===== --}}
    <section class="mb-2">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <span class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-white text-xl font-black shadow-lg shadow-primary-500/30 shrink-0">
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </span>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('panel.greeting', ['name' => auth()->user()->name]) }}</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('panel.realtime_hint') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== شل ریل‌تایم پنل (Livewire) — هر بخش از ماژول خودش ===== --}}
    <livewire:userpanel.panel-shell :initial-tab="$initialTab" :key="'panel-shell'" />
</x-userpanel.layout>
