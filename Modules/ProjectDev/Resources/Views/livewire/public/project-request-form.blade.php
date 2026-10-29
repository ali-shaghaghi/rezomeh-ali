<div>
    @if ($successToast)
        <div class="flex items-center gap-2 p-4 mb-4 text-sm font-bold rounded-2xl glass-card !border-emerald-300/60 dark:!border-emerald-700/40 !text-emerald-600 dark:!text-emerald-400 animate-slide-up"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.opacity.duration.500ms>
            <x-icon name="check" size="4" />
            {{ $successMessage }}
        </div>
    @endif

    @if ($createdRequestId)
        <div class="text-center py-6 glass-card rounded-2xl">
            <span class="mx-auto mb-4 flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 text-white shadow-lg shadow-emerald-500/30">
                <x-icon name="check" size="8" />
            </span>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('project.success_title') }}</h3>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto leading-6">{{ __('project.success_desc') }}</p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <button type="button" wire:click="resetForm" class="btn-outline !px-6 !py-3 !text-xs">{{ __('project.new_request') }}</button>
                <a href="{{ route('user.dashboard', ['tab' => 'projects']) }}" class="btn-primary !px-6 !py-3 !text-xs">{{ __('project.my_requests') }}</a>
            </div>
        </div>
    @else
        <div class="glass-card p-6 sm:p-8">
            <h3 class="mb-1 flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white">
                <x-icon name="cart" />
                {{ __('project.form_title') }}
            </h3>
            <p class="mb-6 text-xs text-slate-500 dark:text-slate-400">{{ __('project.form_subtitle') }}</p>

            <form wire:submit="submit" class="space-y-5">
                {{-- موضوع پروژه --}}
                <div>
                    <label for="project-title" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('project.project_title') }} <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="form.title" id="project-title" maxlength="190"
                           placeholder="{{ __('project.project_title_placeholder') }}"
                           class="w-full px-4 py-3 text-sm rounded-xl bg-white/80 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 outline-none transition-all" />
                    @error('form.title') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                {{-- نوع پروژه --}}
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('project.project_type') }} <span class="text-rose-500">*</span></label>
                    <div class="grid gap-2.5 sm:grid-cols-2">
                        @forelse ($types as $type)
                            <button type="button" wire:click="selectType({{ $type->id }})" wire:key="type-{{ $type->id }}"
                                    class="text-right p-4 rounded-xl border transition-all duration-200 {{ $form['project_type_id'] === $type->id ? 'border-primary-400 bg-primary-50/60 dark:bg-primary-950/30 ring-2 ring-primary-400/20' : 'border-slate-200 dark:border-slate-700 bg-white/60 dark:bg-slate-800/40 hover:border-primary-300' }}">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-sm text-slate-800 dark:text-slate-100">{{ $type->name }}</span>
                                    <span class="text-xs font-bold text-primary-600 dark:text-primary-400 shrink-0">{{ format_price_for_locale((float) $type->base_price) }}</span>
                                </span>
                                @if ($type->description)
                                    <span class="block mt-1 text-[11px] text-slate-500 dark:text-slate-400 leading-5">{{ \Illuminate\Support\Str::limit($type->description, 80) }}</span>
                                @endif
                            </button>
                        @empty
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('project.no_types') }}</p>
                        @endforelse
                    </div>
                    @error('form.project_type_id') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                {{-- ویژگی‌ها (گزینه‌های قیمت‌گذاری‌شده) --}}
                @if ($options)
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('project.required_options') }}</label>
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            @foreach ($options as $option)
                                @php($checked = in_array($option->id, $selectedOptionIds, true))
                                <button type="button" wire:click="toggleOption({{ $option->id }})" wire:key="opt-{{ $option->id }}"
                                        class="flex items-center justify-between gap-3 p-3.5 rounded-xl border transition-all duration-200 {{ $checked ? 'border-emerald-400 bg-emerald-50/60 dark:bg-emerald-950/30' : 'border-slate-200 dark:border-slate-700 bg-white/60 dark:bg-slate-800/40 hover:border-emerald-300' }}">
                                    <span class="flex items-center gap-2.5 min-w-0">
                                        <span class="flex items-center justify-center w-5 h-5 rounded-md border shrink-0 transition {{ $checked ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 dark:border-slate-600' }}">
                                            @if ($checked)<x-icon name="check" size="3.5" />@endif
                                        </span>
                                        <span class="text-sm text-slate-800 dark:text-slate-100 truncate">{{ $option->name }}</span>
                                    </span>
                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 shrink-0">+{{ number_format((float) $option->price) }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ویژگی دلخواه (افزودن) --}}
                <div class="p-4 rounded-xl border border-dashed border-slate-300 dark:border-slate-600">
                    <p class="mb-3 text-xs font-bold text-slate-600 dark:text-slate-300">{{ __('project.custom_option_hint') }}</p>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" wire:model="customOptionForm.name" maxlength="190" placeholder="{{ __('project.custom_option_name') }}"
                               class="flex-1 px-3.5 py-2.5 text-sm rounded-xl bg-white/80 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 outline-none transition-all" />
                        <input type="text" wire:model="customOptionForm.note" maxlength="500" placeholder="{{ __('project.custom_option_note') }}"
                               class="flex-1 px-3.5 py-2.5 text-sm rounded-xl bg-white/80 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 outline-none transition-all" />
                        <button type="button" wire:click="addCustomOption" class="btn-outline !px-5 !py-2.5 !text-xs shrink-0">
                            <x-icon name="plus" size="4" />
                            {{ __('project.add') }}
                        </button>
                    </div>
                    @error('customOptionForm.name') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror

                    @if ($customOptions)
                        <ul class="mt-3 space-y-2">
                            @foreach ($customOptions as $i => $custom)
                                <li class="flex items-center justify-between gap-2 p-2.5 rounded-lg bg-slate-50 dark:bg-slate-700/40 text-xs">
                                    <span class="text-slate-700 dark:text-slate-200">
                                        <b>{{ $custom['name'] }}</b>
                                        @if ($custom['note'])<span class="text-slate-400"> — {{ $custom['note'] }}</span>@endif
                                    </span>
                                    <button type="button" wire:click="removeCustomOption({{ $i }})" class="text-rose-500 hover:text-rose-600 font-bold">{{ __('project.remove') }}</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- توضیحات --}}
                <div>
                    <label for="project-desc" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('project.description') }}</label>
                    <textarea wire:model="form.description" id="project-desc" rows="4" maxlength="3000"
                              placeholder="{{ __('project.description_placeholder') }}"
                              class="w-full px-4 py-3 text-sm rounded-xl bg-white/80 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 outline-none transition-all resize-y"></textarea>
                    @error('form.description') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                {{-- جمع تخمینی + دکمه ثبت --}}
                <div class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-xl bg-gradient-to-l from-primary-50 to-indigo-50/50 dark:from-primary-950/20 dark:to-indigo-950/10">
                    <div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('project.estimated_price') }}</p>
                        <p class="text-xl font-black text-primary-600 dark:text-primary-400">{{ format_price_for_locale((float) $estimatedPrice) }}</p>
                    </div>
                    <button type="submit" wire:loading.attr="disabled" wire:target="submit" class="btn-primary !px-6 !py-3 !text-xs">{{ __('project.submit') }}</button>
                </div>
            </form>
        </div>
    @endif
</div>
