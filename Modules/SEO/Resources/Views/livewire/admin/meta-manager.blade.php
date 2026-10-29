<div>
    {{-- سربرگ --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">مدیریت سئو</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                متا تگ‌ها، اسکیما و FAQ هر صفحه — دوزبانه (فارسی / انگلیسی)
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('seo.sitemap') }}" target="_blank" class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-semibold transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-primary-600">
                sitemap.xml
            </a>
            <a href="{{ route('seo.robots') }}" target="_blank" class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-semibold transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-primary-600">
                robots.txt
            </a>
        </div>
    </div>

    {{-- انتخاب زبان --}}
    <div class="mb-5 flex gap-2">
        <button type="button" wire:click="$set('locale', 'fa')"
                class="rounded-xl px-5 py-2.5 text-sm font-bold transition {{ $locale === 'fa' ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/25' : 'border border-slate-200 text-slate-600 dark:border-slate-700 dark:text-slate-300' }}">
            🇮🇷 فارسی
        </button>
        <button type="button" wire:click="$set('locale', 'en')"
                class="rounded-xl px-5 py-2.5 text-sm font-bold transition {{ $locale === 'en' ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/25' : 'border border-slate-200 text-slate-600 dark:border-slate-700 dark:text-slate-300' }}">
            🇬🇧 English
        </button>
    </div>

    {{-- جدول صفحات --}}
    <div class="chart-card overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3 text-right font-bold">صفحه (route)</th>
                    <th class="px-4 py-3 text-right font-bold">عنوان سئو</th>
                    <th class="px-4 py-3 text-center font-bold">اسکیما</th>
                    <th class="px-4 py-3 text-center font-bold">FAQ</th>
                    <th class="px-4 py-3 text-center font-bold">وضعیت</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($routes as $route => $metas)
                    @php $m = $metas[$locale]; @endphp
                    <tr class="border-t border-slate-100 dark:border-slate-800" wire:key="route-{{ $route }}">
                        <td class="px-4 py-3 font-mono text-xs" dir="ltr">{{ $route }}</td>
                        <td class="max-w-xs px-4 py-3">
                            <span class="line-clamp-1 text-xs">{{ $m?->title ?? '— (پیش‌فرض خودکار)' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($m?->schema_type)
                                <span class="rounded-md bg-violet-100 px-2 py-0.5 text-[10px] font-bold text-violet-600 dark:bg-violet-900/40 dark:text-violet-400">{{ $m->schema_type }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-xs">
                            {{ $m?->faq_json ? count($m->faq_json).' سوال' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($m?->noindex)
                                <span class="rounded-md bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-600 dark:bg-rose-900/40 dark:text-rose-400">noindex</span>
                            @elseif ($m)
                                <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400">شخصی‌سازی</span>
                            @else
                                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] text-slate-500 dark:bg-slate-800">پیش‌فرض</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-left">
                            <button type="button" wire:click="openEdit('{{ $route }}')"
                                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-600 dark:text-slate-300">
                                ویرایش
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">صفحه‌ای یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('seo::livewire.admin.meta-guide')
    @include('seo::livewire.admin.meta-modal')
</div>
