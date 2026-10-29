@extends('adminpanel::layouts.master')

@section('title', 'مدیریت ادمین‌ها')

@push('styles')
<style>
    .modal-backdrop { opacity: 0; transition: opacity 0.3s ease; }
    .modal-backdrop.active { opacity: 1; }
    .modal-panel { opacity: 0; transform: scale(0.95) translateY(10px); transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
    .modal-panel.active { opacity: 1; transform: scale(1) translateY(0); }
</style>
@endpush

@section('content')
    {{-- Title --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6 animate-slide-up">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">مدیریت ادمین‌ها</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $admins->total() }} عضو کادر فنی — ساخته‌شده فقط از داخل پنل</p>
        </div>
        <button onclick="openAdminModal()" class="group flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/25 transition-all hover:-translate-y-0.5">
            <span class="flex items-center justify-center w-5 h-5 rounded-md bg-white/20 group-hover:rotate-90 transition">
                <x-icon name="plus" size="3" />
            </span>
            افزودن ادمین
        </button>
    </div>

    {{-- Tabs --}}
    @include('users::partials.tabs', ['activeTab' => 'admins'])

    {{-- Filters --}}
    <div class="mb-6 animate-slide-up chart-card">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="جستجوی نام، ایمیل یا تلفن..." class="flex-1 min-w-[180px] rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800" />
            <select name="role" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه نقش‌ها</option>
                @foreach($roles as $r)
                    <option value="{{ $r->slug }}" {{ request('role') === $r->slug ? 'selected' : '' }}>{{ $r->name }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-surface-800">
                <option value="">همه وضعیت‌ها</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>فعال</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
            </select>
            <button type="submit" class="rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-medium text-white shadow-md shadow-primary-500/30 transition hover:brightness-110">
                اعمال فیلتر
            </button>
        </form>
    </div>

    {{-- Success / Error --}}
    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600 dark:text-emerald-300">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-600 dark:text-rose-300">{{ session('error') }}</div>
    @endif

    {{-- لینک ورود هش‌شده ادمین‌ها (سراسری) — برای ارسال به ادمین‌های دیگر --}}
    @if ($canRegenerateTokens)
        <div class="mb-6 chart-card animate-slide-up">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold">لینک ورود ادمین‌ها (سراسری)</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">این لینک را فقط برای ادمین‌های مورد اعتماد ارسال کنید. بازتولید، لینک قبلی همه ادمین‌ها را باطل می‌کند.</p>
                </div>
                <form action="{{ route('admin.admins.route-token.global') }}" method="POST"
                      onsubmit="return confirm('روت سراسری بازتولید شود؟ لینک قبلی برای همه ادمین‌ها باطل می‌شود.')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">
                        بازتولید روت سراسری
                    </button>
                </form>
            </div>
            <div class="flex gap-2 mt-3">
                <input type="text" readonly dir="ltr" id="global-login-url" value="{{ $globalLoginUrl }}"
                       onclick="this.select()"
                       class="flex-1 min-w-0 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600 dark:border-slate-700 dark:bg-surface-800 dark:text-slate-300" />
                <button type="button" onclick="copyLoginUrl('global-login-url', this)"
                        class="rounded-xl bg-primary-500 px-4 py-2 text-xs font-medium text-white shadow-md shadow-primary-500/30 transition hover:brightness-110">
                    کپی لینک
                </button>
            </div>
        </div>
    @endif

    {{-- Admins Grid --}}
    <div class="grid gap-6 sm:grid-cols-2 animate-slide-up">
        @forelse($admins as $admin)
            <section class="flex flex-col role-card chart-card">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-violet-600 text-white text-sm font-bold">
                            {{ mb_substr($admin->name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-sm font-bold">{{ $admin->name }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400" dir="ltr">{{ $admin->phone }}</p>
                        </div>
                    </div>
                    @if($admin->is_active)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            فعال
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-medium text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                            غیرفعال
                        </span>
                    @endif
                </div>

                <p class="mb-2 text-xs text-slate-500 dark:text-slate-400" dir="ltr">{{ $admin->email }}</p>

                {{-- Role select (inline) --}}
                <div class="pt-3 border-t border-slate-100 dark:border-slate-700">
                    <p class="mt-1 mb-1.5 text-xs font-medium text-slate-500">نقش:</p>
                    <form action="{{ route('admin.admins.role', $admin) }}" method="POST" class="flex flex-wrap gap-1.5 items-center">
                        @csrf
                        @method('PUT')
                        <select name="role" onchange="this.form.submit()" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs dark:border-slate-700 dark:bg-surface-800">
                            @foreach($roles as $role)
                                <option value="{{ $role->slug }}" {{ $admin->hasRole($role->slug) ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </form>
                    <p class="mt-2 text-[10px] text-slate-400">آخرین ورود: {{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'هرگز' }}</p>
                </div>

                {{-- لینک ورود اختصاصی این ادمین --}}
                <div class="pt-3 mt-3 border-t border-slate-100 dark:border-slate-700">
                    <p class="mb-1.5 text-xs font-medium text-slate-500">لینک ورود اختصاصی:</p>
                    <div class="flex gap-1.5">
                        <input type="text" readonly dir="ltr" id="admin-login-url-{{ $admin->id }}"
                               value="{{ \Modules\Auth\Services\AdminLoginToken::urlFor($admin) }}"
                               onclick="this.select()"
                               class="flex-1 min-w-0 rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-[10px] text-slate-600 dark:border-slate-700 dark:bg-surface-800 dark:text-slate-300" />
                        <button type="button" onclick="copyLoginUrl('admin-login-url-{{ $admin->id }}', this)"
                                class="rounded-lg bg-primary-500 px-2.5 py-1.5 text-[10px] font-medium text-white transition hover:brightness-110">
                            کپی
                        </button>
                        @if ($canRegenerateTokens)
                            <form action="{{ route('admin.admins.route-token.regenerate', $admin) }}" method="POST"
                                  onsubmit="return confirm('روت اختصاصی این ادمین بازتولید شود؟ لینک قبلی او باطل می‌شود.')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" title="تولید/بازتولید روت اختصاصی"
                                        class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-[10px] font-medium text-slate-600 transition hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">
                                    بازتولید
                                </button>
                            </form>
                        @endif
                    </div>
                    @if (! $admin->login_route_token)
                        <p class="mt-1 text-[10px] text-slate-400">فعلاً از روت سراسری استفاده می‌کند — با «بازتولید» روت اختصاصی برای او ساخته می‌شود.</p>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex gap-2 pt-3 mt-3 border-t border-slate-100 dark:border-slate-700">
                    <button onclick='editAdminModal({{ json_encode(["id" => $admin->id, "name" => $admin->name, "email" => $admin->email, "phone" => $admin->phone, "role" => $admin->roles->first()?->slug]) }})'
                            class="flex-1 px-3 py-2 text-xs font-medium transition rounded-lg bg-slate-100 text-slate-600 hover:bg-primary-100 hover:text-primary-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-primary-900/40">
                        ویرایش
                    </button>
                    <form action="{{ route('admin.admins.toggle-status', $admin) }}" method="POST" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full px-3 py-2 text-xs font-medium transition rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">
                            {{ $admin->is_active ? 'غیرفعال‌سازی' : 'فعال‌سازی' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" class="flex-1"
                          onsubmit="return confirm('این ادمین حذف شود؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-3 py-2 text-xs font-medium transition rounded-lg bg-slate-100 text-rose-500 hover:bg-rose-100 dark:bg-slate-700 dark:text-rose-400 dark:hover:bg-rose-900/30">
                            حذف
                        </button>
                    </form>
                </div>
            </section>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                هنوز ادمینی ثبت نشده است. از دکمه «افزودن ادمین» استفاده کنید.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $admins->links() }}
    </div>

    {{-- Add/Edit Admin Modal --}}
    <div id="admin-modal" class="fixed inset-0 z-50 flex items-end justify-center hidden modal-backdrop sm:items-center sm:p-4 bg-black/50 backdrop-blur-sm" onclick="if(event.target===this)closeAdminModal()">
        <div class="modal-panel w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200/50 bg-white p-4 shadow-2xl dark:border-slate-700/50 dark:bg-surface-800 sm:p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 id="admin-modal-title" class="text-base font-bold">افزودن ادمین</h3>
                <button onclick="closeAdminModal()" class="p-2 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                    <x-icon name="close" />
                </button>
            </div>

            <form id="admin-form" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="_method" id="admin-form-method" value="POST" />

                <div>
                    <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">نام و نام خانوادگی</label>
                    <input type="text" name="name" id="admin-name" required class="w-full px-3 py-2 text-sm border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50" />
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">ایمیل</label>
                        <input type="email" name="email" id="admin-email" required dir="ltr" class="w-full px-3 py-2 text-sm border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50" />
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">موبایل (برای ورود)</label>
                        <input type="text" name="phone" id="admin-phone" required dir="ltr" placeholder="09123456789" class="w-full px-3 py-2 text-sm border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50" />
                    </div>
                </div>

                <div>
                    <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">رمز عبور <span id="pw-hint" class="text-slate-400 font-normal">(حداقل ۸ کاراکتر)</span></label>
                    <input type="password" name="password" id="admin-password" class="w-full px-3 py-2 text-sm border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50" />
                    <input type="password" name="password_confirmation" id="admin-password-confirm" class="mt-2 w-full px-3 py-2 text-sm border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50" placeholder="تکرار رمز عبور" />
                </div>

                <div>
                    <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">نقش</label>
                    <select name="role" id="admin-role" required class="w-full px-3 py-2 text-sm border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none dark:border-slate-600 dark:bg-slate-700/50">
                        @foreach($roles as $role)
                            <option value="{{ $role->slug }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-xs text-rose-500">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition hover:brightness-110">
                        ذخیره
                    </button>
                    <button type="button" onclick="closeAdminModal()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                        لغو
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const adminModal = () => document.getElementById('admin-modal');

        function openAdminModal() {
            document.getElementById('admin-modal-title').textContent = 'افزودن ادمین';
            document.getElementById('admin-form').action = '{{ route('admin.admins.store') }}';
            document.getElementById('admin-form-method').value = 'POST';
            ['admin-name', 'admin-email', 'admin-phone', 'admin-password', 'admin-password-confirm'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('admin-password').required = true;

            const m = adminModal();
            m.classList.remove('hidden');
            requestAnimationFrame(() => requestAnimationFrame(() => m.classList.add('active')));
            document.body.style.overflow = 'hidden';
        }

        function editAdminModal(data) {
            document.getElementById('admin-modal-title').textContent = 'ویرایش ادمین: ' + data.name;
            document.getElementById('admin-form').action = '{{ url('admin/admins') }}/' + data.id;
            document.getElementById('admin-form-method').value = 'PUT';
            document.getElementById('admin-name').value = data.name;
            document.getElementById('admin-email').value = data.email;
            document.getElementById('admin-phone').value = data.phone;
            document.getElementById('admin-password').value = '';
            document.getElementById('admin-password-confirm').value = '';
            document.getElementById('admin-password').required = false; // رمز خالی = بدون تغییر
            if (data.role) document.getElementById('admin-role').value = data.role;

            const m = adminModal();
            m.classList.remove('hidden');
            requestAnimationFrame(() => requestAnimationFrame(() => m.classList.add('active')));
            document.body.style.overflow = 'hidden';
        }

        function closeAdminModal() {
            const m = adminModal();
            m.classList.remove('active');
            setTimeout(() => m.classList.add('hidden'), 300);
            document.body.style.overflow = '';
        }

        function copyLoginUrl(inputId, btn) {
            const input = document.getElementById(inputId);
            const done = () => {
                const old = btn.textContent;
                btn.textContent = 'کپی شد ✓';
                setTimeout(() => btn.textContent = old, 1500);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(input.value).then(done).catch(() => { input.select(); document.execCommand('copy'); done(); });
            } else {
                input.select();
                document.execCommand('copy');
                done();
            }
        }
    </script>
    @endpush
@endsection
