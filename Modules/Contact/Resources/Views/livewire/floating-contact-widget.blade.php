<div x-data="{ open: false }"
     class="fixed bottom-5 right-5 z-[60] print:hidden"
     @keydown.escape.window="open = false"
     @click.outside="open = false">
    {{-- دکمه شناور — فقط وقتی مسیر ارتباطی وجود داشته باشد رندر می‌شود (#۲۱) --}}
    @if ($hasChannels)
        <button type="button"
                @click="open = !open"
                :aria-expanded="open"
                aria-label="{{ __('public.contact.title') }}"
                class="contact-fab group relative flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-violet-600 text-white shadow-xl shadow-primary-500/40 transition-all duration-300 hover:scale-110 hover:shadow-2xl hover:shadow-primary-500/50"
                :class="{ 'rotate-90': open }">
            {{-- دو آیکون روی هم قرار می‌گیرند (absolute) تا از کادر بیرون نزنند --}}
            <span class="absolute inline-flex h-6 w-6 items-center justify-center" :class="open ? 'opacity-0 scale-50' : 'opacity-100 scale-100'" x-transition.opacity.duration.200ms>
                <x-heroicon name="chat-bubble" class="h-6 w-6" />
            </span>
            <span class="absolute inline-flex h-6 w-6 items-center justify-center" :class="open ? 'opacity-100 scale-100' : 'opacity-0 scale-50'" x-transition.opacity.duration.200ms>
                <x-heroicon name="x-mark" class="h-6 w-6" />
            </span>
            {{-- حلقه درخشش --}}
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary-400 opacity-20" x-show="!open"></span>
        </button>
    @endif

    {{-- کارت ویجت --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         x-cloak
         class="contact-widget-card absolute bottom-16 right-0 w-[calc(100vw-2.5rem)] max-w-sm origin-bottom-right overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-2xl backdrop-blur-xl dark:border-slate-700/60 dark:bg-surface-800/95">

        {{-- هدر --}}
        <div class="bg-gradient-to-l from-primary-500 to-violet-600 px-5 py-4 text-white">
            <h3 class="flex items-center gap-2 text-base font-bold">
                <x-heroicon name="chat-bubble" class="h-5 w-5" />
                {{ __('public.contact.title') }}
            </h3>
            <p class="mt-0.5 text-xs text-white/80">{{ __('public.contact.subtitle') }}</p>
        </div>

        {{-- تب‌ها: مسیرها | چت آنلاین --}}
        <div class="flex gap-1 p-2 border-b border-slate-100 dark:border-slate-700">
            <button type="button"
                    wire:click="setTab('channels')"
                    class="flex-1 flex items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-xs font-bold transition {{ $tab === 'channels' ? 'bg-primary-50 text-primary-600 dark:bg-primary-950/50 dark:text-primary-400' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <x-heroicon name="envelope" class="h-4 w-4" />
                {{ __('public.contact.channels') }}
            </button>
            <button type="button"
                    wire:click="setTab('chat')"
                    class="flex-1 flex items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-xs font-bold transition {{ $tab === 'chat' ? 'bg-primary-50 text-primary-600 dark:bg-primary-950/50 dark:text-primary-400' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                <x-heroicon name="chat-bubble" class="h-4 w-4" />
                {{ __('public.contact.chat') }}
            </button>
        </div>

        {{-- محتوای تب‌ها --}}
        <div class="max-h-[60vh] overflow-y-auto">
            @if ($tab === 'chat')
                @include('contact::livewire.partials.chat-panel')
            @else
                <div class="p-5">
                    @include('contact::livewire.partials.widget-body')
                </div>
            @endif
        </div>
    </div>
</div>
