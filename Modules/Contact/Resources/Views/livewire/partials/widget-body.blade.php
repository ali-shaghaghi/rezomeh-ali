{{-- شبکه‌های اجتماعی --}}
@if (! empty($socialLinks))
    <div class="mb-5">
        <p class="mb-2.5 text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('public.contact.social') }}</p>
        <div class="grid grid-cols-4 gap-2.5">
            @foreach ($socialLinks as $link)
                <a href="{{ $link['url'] }}"
                   target="_blank" rel="noopener noreferrer"
                   title="{{ $link['label'] }}"
                   class="group/link flex aspect-square flex-col items-center justify-center gap-1 rounded-2xl border border-slate-200 bg-slate-50 transition-all duration-200 hover:-translate-y-0.5 hover:border-transparent hover:shadow-md dark:border-slate-700 dark:bg-slate-800/60">
                    @include('contact::livewire.partials.social-icon', ['key' => $link['icon']])
                    <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400">{{ $link['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endif

{{-- اطلاعات تماس مستقیم — با همان استایل شبکه‌های اجتماعی --}}
@if (! empty($contactInfo['email']) || ! empty($contactInfo['phone']))
    <div class="space-y-2">
        <p class="mb-1 text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('public.contact.direct') }}</p>
        @if (! empty($contactInfo['email']))
            <a href="mailto:{{ $contactInfo['email'] }}"
               class="flex items-center gap-2.5 rounded-xl border border-slate-200 px-3 py-2 text-xs font-medium transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-primary-600 dark:hover:text-primary-400">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400 shrink-0">
                    <x-heroicon name="envelope" class="h-4 w-4" />
                </span>
                <span dir="ltr">{{ $contactInfo['email'] }}</span>
            </a>
        @endif
        @if (! empty($contactInfo['phone']))
            <a href="tel:{{ $contactInfo['phone'] }}"
               class="flex items-center gap-2.5 rounded-xl border border-slate-200 px-3 py-2 text-xs font-medium transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-primary-600 dark:hover:text-primary-400">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400 shrink-0">
                    <x-heroicon name="phone" class="h-4 w-4" />
                </span>
                <span dir="ltr">{{ $contactInfo['phone'] }}</span>
            </a>
        @endif
    </div>
@endif

{{-- دکمه چت آنلاین — پیام سریع حذف شد (#۲۱)؛ ارتباط زنده از طریق چت انجام می‌شود --}}
@if (! empty($socialLinks) || ! empty($contactInfo['email']) || ! empty($contactInfo['phone']))
    <div class="mt-5 border-t border-slate-200 pt-4 dark:border-slate-700">
        <button type="button"
                wire:click="setTab('chat')"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-l from-primary-500 to-violet-600 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary-500/25 transition hover:opacity-90">
            <x-heroicon name="chat-bubble" class="h-4 w-4" />
            {{ __('public.contact.chat') }}
        </button>
    </div>
@endif
