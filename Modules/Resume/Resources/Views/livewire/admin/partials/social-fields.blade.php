{{-- فیلدهای شبکه‌های اجتماعی فرم ویرایش پروفایل رزومه --}}
{{-- تلگرام · واتساپ · اینستاگرام · روبیکا · بله · ایتا · تلفن همراه · ایمیل --}}
@php
    $inputClass = 'w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white';
    $labelClass = 'flex items-center gap-1.5 mb-1 text-xs font-medium text-slate-700 dark:text-slate-300';
@endphp

<div class="p-4 space-y-4 rounded-2xl border border-slate-200/70 bg-slate-50/50 dark:border-slate-700/60 dark:bg-slate-800/30">
    <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-700">
        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
            <x-icon name="users" size="4" />
        </span>
        <h4 class="text-sm font-bold text-slate-900 dark:text-white">شبکه‌های اجتماعی</h4>
        <span class="px-2 py-0.5 text-[11px] font-medium rounded-lg bg-slate-200/70 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300">اختیاری</span>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="{{ $labelClass }}"><x-social name="telegram" class="w-3.5 h-3.5" /> تلگرام</label>
            <input type="text" wire:model.blur="profile_telegram_url" dir="ltr" class="{{ $inputClass }}" placeholder="https://t.me/username" />
            @error('profile_telegram_url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="{{ $labelClass }}"><x-social name="whatsapp" class="w-3.5 h-3.5" /> واتساپ</label>
            <input type="text" wire:model.blur="profile_whatsapp_url" dir="ltr" class="{{ $inputClass }}" placeholder="https://wa.me/989120000000" />
            @error('profile_whatsapp_url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="{{ $labelClass }}"><x-social name="instagram" class="w-3.5 h-3.5" /> اینستاگرام</label>
            <input type="text" wire:model.blur="profile_instagram_url" dir="ltr" class="{{ $inputClass }}" placeholder="https://instagram.com/username" />
            @error('profile_instagram_url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="{{ $labelClass }}"><x-social name="rubika" class="w-3.5 h-3.5" /> روبیکا</label>
            <input type="text" wire:model.blur="profile_rubika_url" dir="ltr" class="{{ $inputClass }}" placeholder="https://rubika.ir/username" />
            @error('profile_rubika_url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="{{ $labelClass }}"><x-social name="bale" class="w-3.5 h-3.5" /> بله</label>
            <input type="text" wire:model.blur="profile_bale_url" dir="ltr" class="{{ $inputClass }}" placeholder="https://ble.ir/username" />
            @error('profile_bale_url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="{{ $labelClass }}"><x-social name="eita" class="w-3.5 h-3.5" /> ایتا</label>
            <input type="text" wire:model.blur="profile_eita_url" dir="ltr" class="{{ $inputClass }}" placeholder="https://eita.me/username" />
            @error('profile_eita_url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="{{ $labelClass }}"><x-social name="phone" class="w-3.5 h-3.5" /> تلفن همراه</label>
            <input type="text" wire:model.blur="profile_phone" dir="ltr" class="{{ $inputClass }}" placeholder="09120000000" />
            @error('profile_phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="{{ $labelClass }}"><x-social name="mail" class="w-3.5 h-3.5" /> ایمیل</label>
            <input type="email" wire:model.blur="profile_email" dir="ltr" class="{{ $inputClass }}" placeholder="email@example.com" />
            @error('profile_email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
