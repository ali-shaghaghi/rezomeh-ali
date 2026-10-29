<div class="glass-card p-6 sm:p-8">
    <h3 class="mb-6 flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white">
        <x-icon name="mail" class="text-primary-500" />
        {{ __('public.contact.direct_message') }}
    </h3>

    @if ($sent)
        <div class="p-6 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-center" role="status">
            <p class="font-bold text-emerald-600 dark:text-emerald-400">{{ __('public.contact.sent') }} ✅</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('public.contact.reply_soon') }}</p>
        </div>
    @else
        @if ($errors->has('send'))
            <p class="mb-4 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-sm font-bold text-rose-600 dark:text-rose-400">{{ $errors->first('send') }}</p>
        @endif

        <form wire:submit="send" class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="contact-name" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('public.contact.name') }}</label>
                <input type="text" wire:model="name" id="contact-name"
                       class="w-full px-4 py-3 text-sm rounded-xl bg-white/80 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 outline-none transition-all">
                @error('name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="contact-email" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('public.contact.email') }}</label>
                <input type="email" wire:model="email" id="contact-email" dir="ltr"
                       class="w-full px-4 py-3 text-sm rounded-xl bg-white/80 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 outline-none transition-all">
                @error('email') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="contact-subject" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('public.contact.subject') }}</label>
                <input type="text" wire:model="subject" id="contact-subject"
                       class="w-full px-4 py-3 text-sm rounded-xl bg-white/80 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 outline-none transition-all">
                @error('subject') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="contact-message" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('public.contact.message') }}</label>
                <textarea wire:model="message" id="contact-message" rows="4"
                          class="w-full px-4 py-3 text-sm rounded-xl bg-white/80 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 outline-none transition-all resize-y"></textarea>
                @error('message') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <button type="submit" wire:loading.attr="disabled" wire:target="send"
                        class="btn-primary !px-6 !py-3 !text-xs">
                    {{ __('public.contact.send_message') }}
                </button>
            </div>
        </form>
    @endif
</div>