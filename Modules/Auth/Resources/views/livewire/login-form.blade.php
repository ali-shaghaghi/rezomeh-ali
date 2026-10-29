<div x-data="{ showPassword: @entangle('showPassword'), activeTab: @entangle('loginType') }">
    {{-- Login Type Tabs with sliding indicator --}}
    <div class="relative flex gap-2 p-1 mb-6 border rounded-xl bg-slate-800/50 border-slate-700/50">
        {{-- Sliding indicator --}}
        <div class="absolute transition-all duration-300 ease-in-out rounded-lg shadow-lg top-1 bottom-1 bg-primary-500 shadow-primary-500/30"
             :style="activeTab === 'email' ? 'right: 4px; left: calc(50% - 4px);' : 'right: calc(50% - 4px); left: 4px;'"></div>

        <button
            type="button"
            x-on:click="activeTab = 'email'; $wire.set('loginType', 'email')"
            class="flex-1 py-2.5 px-4 rounded-lg text-sm font-medium transition-colors duration-200 relative z-10"
            :class="activeTab === 'email' ? 'text-white' : 'text-slate-400 hover:text-white'"
        >
            <span class="flex items-center justify-center gap-2">
                <x-icon name="mail" size="4" />
                {{ __('auth.email') }}
            </span>
        </button>
        <button
            type="button"
            x-on:click="activeTab = 'phone'; $wire.set('loginType', 'phone')"
            class="flex-1 py-2.5 px-4 rounded-lg text-sm font-medium transition-colors duration-200 relative z-10"
            :class="activeTab === 'phone' ? 'text-white' : 'text-slate-400 hover:text-white'"
        >
            <span class="flex items-center justify-center gap-2">
                <x-icon name="users" size="4" />
                {{ __('auth.phone') }}
            </span>
        </button>
    </div>

    {{-- Error Message --}}
    @if($errorMessage)
        <div class="p-3 mb-4 text-sm text-center border rounded-xl bg-rose-500/10 border-rose-500/30 text-rose-400" role="alert">
            {{ $errorMessage }}
        </div>
    @endif

    {{-- Login Form --}}
    <form wire:submit="login" novalidate>
        <fieldset class="min-w-0 p-0 m-0 border-0">
            <legend class="sr-only">{{ __('auth.login_form') }}</legend>

            {{-- Credential Input --}}
            <div class="mb-4">
                <label for="credential" class="block mb-2 text-sm font-medium text-slate-300" x-text="activeTab === 'email' ? @js(__('auth.email')) : @js(__('auth.phone_number'))"></label>
                <div class="gradient-border-wrap">
                    <div class="relative flex items-center input-inner">
                        <span class="absolute z-10 -translate-y-1/2 right-3 top-1/2 text-slate-400">
                            <template x-if="activeTab === 'email'">
                                <x-icon name="mail" />
                            </template>
                            <template x-if="activeTab === 'phone'">
                                <x-icon name="users" />
                            </template>
                        </span>
                        <input
                            :type="activeTab === 'email' ? 'email' : 'tel'"
                            id="credential"
                            wire:model="credential"
                            :placeholder="activeTab === 'email' ? 'email@example.com' : '۰۹۱۲۱۲۳۴۵۶۷'"
                            class="w-full py-3 pl-4 text-white bg-transparent outline-none pr-11 placeholder-slate-500"
                            {{ $isLoading ? 'disabled' : '' }}
                            :dir="activeTab === 'phone' ? 'ltr' : 'rtl'"
                        />
                    </div>
                </div>
                @error('credential')
                    <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Input --}}
            <div class="mb-4">
                <label for="password" class="block mb-2 text-sm font-medium text-slate-300">
                    {{ __('auth.password') }}
                </label>
                <div class="gradient-border-wrap">
                    <div class="relative flex items-center input-inner">
                        <span class="absolute z-10 -translate-y-1/2 right-3 top-1/2 text-slate-400">
                            <x-icon name="shield" />
                        </span>
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            id="password"
                            wire:model="password"
                            placeholder="{{ __('auth.password_placeholder') }}"
                            class="w-full py-3 pl-12 text-white bg-transparent outline-none pr-11 placeholder-slate-500"
                            {{ $isLoading ? 'disabled' : '' }}
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute z-10 transition-colors duration-75 -translate-y-1/2 left-3 top-1/2 text-slate-400 hover:text-white"
                        >
                        <template x-if="showPassword">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                        </template>
                        <template x-if="!showPassword">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        </template>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember Me & Forgot Password --}}
            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model="remember"
                        class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-primary-500 focus:ring-primary-500/20"
                        {{ $isLoading ? 'disabled' : '' }}
                    />
                    <span class="text-sm text-slate-400">{{ __('auth.remember_me') }}</span>
                </label>
                <a href="{{ route('auth.password.request') }}" class="text-sm transition-colors duration-150 text-primary-400 hover:text-primary-300">
                    {{ __('auth.forgot_password') }}
                </a>
            </div>

            {{-- Submit Button --}}
            <button
                type="submit"
                class="btn-primary w-full py-3.5 px-4 rounded-xl text-base font-semibold text-white flex items-center justify-center gap-2 transition-all duration-150"
                {{ $isLoading ? 'disabled' : '' }}
            >
                @if($isLoading)
                    <svg class="w-5 h-5 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <span>{{ __('auth.processing') }}</span>
                @else
                    <span>{{ __('auth.login') }}</span>
                @endif
            </button>
        </fieldset>
    </form>

    {{-- Social Login Divider --}}
    <div class="flex items-center gap-4 my-5 mt-2">
        <div class="flex-1 border-t border-slate-700/50"></div>
        <span class="text-[11px] text-slate-500">{{ __('auth.or_login_with') }}</span>
        <div class="flex-1 border-t border-slate-700/50"></div>
    </div>



    {{-- Footer --}}
    <footer class="mt-4 text-center">
        {{-- Social Login Icons --}}
        <div class="flex items-center justify-center gap-3">
            <a href="{{ route('auth.socialite.redirect', 'google') }}"
               class="flex items-center justify-center w-10 h-10 transition-all duration-150 border rounded-lg border-slate-700/50 bg-white/5 text-slate-400 hover:bg-white/10 hover:border-slate-600 hover:text-white"
               title="{{ __('auth.login_with', ['provider' => 'Google']) }}">
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.06z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
            </a>
        </div>
    </footer>
</div>
