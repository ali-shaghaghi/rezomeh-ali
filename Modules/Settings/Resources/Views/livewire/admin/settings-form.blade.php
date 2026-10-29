<div>
    {{-- کارت فرم گروه فعال --}}
    <section class="chart-card observe-in">
        <h3 class="mb-1 flex items-center gap-2 text-lg font-bold">
            <x-icon name="{{ $schema['icon'] }}" />
            {{ $schema['title'] }}
        </h3>
        <p class="mb-5 text-xs text-slate-500 dark:text-slate-400">تغییرات پس از ذخیره بلافاصله در کل سیستم اعمال می‌شود</p>

        {{-- نوتیفیکیشن موفقیت --}}
        @if($successToast)
            <div class="px-4 py-3 mb-5 text-sm rounded-xl bg-emerald-100 text-emerald-700 animate-slide-up dark:bg-emerald-900/30 dark:text-emerald-300"
                 x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition.opacity.duration.500ms>
                {{ $successMessage }}
            </div>
        @endif

        <form wire:submit.prevent="save" class="space-y-5">
            @foreach($schema['fields'] as $key => $field)
                <div>
                    @if($field['type'] === 'boolean')
                        {{-- سوییچ بولین — سبز وقتی فعال است --}}
                        <label class="flex items-center justify-between gap-3 p-3 rounded-xl cursor-pointer bg-slate-50 dark:bg-slate-700/50 transition-colors has-[:checked]:bg-emerald-50 has-[:checked]:border has-[:checked]:border-emerald-200 dark:has-[:checked]:bg-emerald-900/20 dark:has-[:checked]:border-emerald-800">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $field['label'] }}</span>
                            <input type="checkbox" wire:model="values.{{ $key }}"
                                   class="w-10 h-5 rounded-full appearance-none cursor-pointer transition bg-slate-300 checked:bg-emerald-500 relative before:content-[''] before:absolute before:top-0.5 before:right-0.5 before:w-4 before:h-4 before:rounded-full before:bg-white before:transition checked:before:right-5 dark:bg-slate-600" />
                        </label>
                    @elseif($field['type'] === 'select')
                        {{-- سلکت --}}
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">{{ $field['label'] }}</label>
                        <select wire:model.live="values.{{ $key }}"
                                class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                            @foreach($field['options'] as $optKey => $optLabel)
                                <option value="{{ $optKey }}">{{ $optLabel }}</option>
                            @endforeach
                        </select>
                        @error("values.{$key}") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    @elseif($field['type'] === 'textarea')
                        {{-- متن بلند --}}
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">{{ $field['label'] }}</label>
                        <textarea wire:model.blur="values.{{ $key }}" rows="3"
                                  class="w-full px-3 py-2 text-sm transition border resize-none rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"></textarea>
                        @error("values.{$key}") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    @else
                        {{-- text / email / number / password --}}
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">{{ $field['label'] }}</label>
                        <input type="{{ $field['type'] === 'password' ? 'password' : $field['type'] }}"
                               wire:model.blur="values.{{ $key }}"
                               dir="{{ in_array($field['type'], ['email', 'number', 'password']) ? 'ltr' : 'auto' }}"
                               class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                               @if($field['type'] === 'password') placeholder="برای حفظ مقدار فعلی خالی بگذارید" @endif />
                        @error("values.{$key}") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    @endif
                </div>
            @endforeach

            {{-- دکمه ذخیره --}}
            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all hover:shadow-lg hover:shadow-primary-500/35 disabled:opacity-50">
                <span wire:loading.remove>ذخیره تنظیمات</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    در حال ذخیره...
                </span>
            </button>
        </form>
    </section>
</div>
