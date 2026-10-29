@extends('adminpanel::layouts.master')

@section('title', 'مدیریت نقش‌ها')

@push('styles')
<style>
    .modal-backdrop {
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .modal-backdrop.active {
        opacity: 1;
    }
    .modal-panel {
        opacity: 0;
        transform: scale(0.95) translateY(10px);
        transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .modal-panel.active {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
    .role-card {
        opacity: 0;
        transform: translateY(20px);
    }
    .role-card.show {
        animation: slideUp 0.5s ease-out forwards;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .permissions-scroll::-webkit-scrollbar {
        width: 4px;
    }
    .permissions-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .permissions-scroll::-webkit-scrollbar-thumb {
        background: #6366f1;
        border-radius: 10px;
    }
    .items-center {
        align-items: center;!important
    }   
</style>
@endpush

@section('content')
    {{-- Title --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">مدیریت نقش‌ها</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $roles->count() }} نقش تعریف شده — نقش‌ها فقط به ادمین‌ها (کادر فنی) قابل اتصال‌اند</p>
        </div>
        <button onclick="openModal()" class="group flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/25 transition-all duration-200 hover:shadow-xl hover:shadow-primary-500/35 hover:-translate-y-0.5 active:translate-y-0">
            <span class="flex items-center justify-center w-5 h-5 transition duration-300 rounded-md bg-white/20 group-hover:rotate-90">
                <x-icon name="plus" size="3" />
            </span>
            اضافه کردن نقش
        </button>
    </div>

    {{-- Tabs: کاربران / ادمین‌ها / نقش‌ها --}}
    @include('users::partials.tabs', ['activeTab' => 'roles'])

 
    {{-- Add/Edit Role Modal --}}
    <div id="role-modal" class="fixed inset-0 z-50 flex items-end justify-center hidden animate-slide-up modal-backdrop sm:items-center sm:p-4 bg-black/50 backdrop-blur-sm" onclick="if(event.target===this)closeModal()">
        <div class="modal-panel w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200/50 bg-white p-4 shadow-2xl dark:border-slate-700/50 dark:bg-surface-800 sm:p-5">
            {{-- Header --}}
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                        <x-icon name="shield" size="4" />
                    </span>
                    <div>
                        <h3 id="modal-title" class="text-base font-bold">اضافه کردن نقش جدید</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">تعریف نقش و دسترسی‌های آن</p>
                    </div>
                </div>
                <button onclick="closeModal()" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                    <x-icon name="close" />
                </button>
            </div>

            <form id="role-form" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST" />

                {{-- Name & Slug --}}
                <div class="grid gap-3 mb-1 sm:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">نام نقش</label>
                        <input type="text" name="name" id="role-name" required class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="مثال: مدیر فروش" />
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">نام انگلیسی (slug)</label>
                        <input type="text" name="slug" id="role-slug" required dir="ltr" class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="sales-manager" />
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">توضیحات</label>
                    <textarea name="description" id="role-description" rows="2" class="w-full px-3 py-2 text-sm transition border resize-none rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white" placeholder="توضیحات نقش..."></textarea>
                </div>

                {{-- Permissions --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">دسترسی‌ها</label>
                        <button type="button" onclick="toggleAllPermissions()" class="text-xs transition text-primary-500 hover:text-primary-600">انتخاب همه</button>
                    </div>
                    <div class="p-2 overflow-y-auto border permissions-scroll max-h-40 rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-700/50">
                        <div class="grid grid-cols-1 gap-1.5 sm:grid-cols-2">
                            @foreach($permissions as $permission)
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs transition hover:bg-slate-100 dark:hover:bg-slate-600/50">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="permission-checkbox h-3.5 w-3.5 rounded border-slate-300 text-primary-500 focus:ring-primary-500/20" />
                                    <span class="text-slate-700 dark:text-slate-300">{{ $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 pt-3 mt-4">
                    <button type="submit" class="flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all hover:shadow-lg hover:shadow-primary-500/35 hover:-translate-y-0.5 active:translate-y-0">
                        ذخیره نقش
                    </button>
                    <button type="button" onclick="closeModal()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                        لغو
                    </button>
                </div>
            </form>
        </div>
    </div>



    {{-- Roles Grid --}}
    <div class="grid gap-6 sm:grid-cols-2 animate-slide-up">
        @foreach($roles as $index => $role)
            <section class="flex flex-col role-card chart-card">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-bold">{{ $role->name }}</h3>
                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                        @if($role->slug === 'user')
                            {{ $role->users_count }} کاربر
                        @else
                            {{ $role->admins_count }} عضو کادر فنی
                        @endif
                    </span>
                </div>

                <p class="mb-3 text-sm text-slate-500 dark:text-slate-400">{{ $role->description ?: 'بدون توضیحات' }}</p>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-700">
                    <p class="mb-2 text-xs font-medium text-slate-500">دسترسی‌ها:</p>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($role->permissions->take(5) as $permission)
                            <span class="px-2 py-1 mb-1 ml-1 text-xs rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                {{ $permission->name }}
                            </span>
                        @empty
                            <span class="text-xs text-slate-400">بدون دسترسی</span>
                        @endforelse
                        @if($role->permissions->count() > 5)
                            <span class="px-2 py-1 text-xs font-medium rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-300">
                                +{{ $role->permissions->count() - 5 }} بیشتر
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-2 pt-3 mt-4 mb-0 border-slate-100 dark:border-slate-700">
                    @if($role->slug !== 'user')
                        <a href="{{ route('admin.admins.index', ['role' => $role->slug]) }}" class="flex-1 px-3 py-2 text-xs font-medium text-center transition rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">
                            مشاهده اعضا
                        </a>
                    @else
                        <a href="{{ route('admin.users.index') }}" class="flex-1 px-3 py-2 text-xs font-medium text-center transition rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">
                            مشاهده کاربران
                        </a>
                    @endif
                    <button onclick='editRole({{ json_encode($role) }})' class="px-3 py-2 text-xs font-medium transition rounded-lg bg-slate-100 text-slate-600 hover:bg-primary-100 hover:text-primary-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-primary-900/40 dark:hover:text-primary-300">
                        ویرایش
                    </button>
                    @if(($role->slug === 'user' ? $role->users_count : $role->admins_count) === 0)
                        <button onclick="confirmDeleteRole({{ $role->id }}, '{{ $role->name }}')" class="px-3 py-2 text-xs font-medium transition rounded-lg bg-slate-100 text-rose-500 hover:bg-rose-100 dark:bg-slate-700 dark:text-rose-400 dark:hover:bg-rose-900/30">
                            حذف
                        </button>
                    @endif
                </div>
            </section>
        @endforeach
    </div>

    @push('scripts')
    <script>
        // Animate cards on load
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.role-card').forEach((card, i) => {
                setTimeout(() => card.classList.add('show'), i * 80);
            });
        });

        function openModal(data = null) {
            const modal = document.getElementById('role-modal');
            const title = document.getElementById('modal-title');
            const form = document.getElementById('role-form');
            const methodInput = document.getElementById('form-method');

            if (data) {
                title.textContent = 'ویرایش نقش: ' + data.name;
                document.getElementById('role-name').value = data.name;
                document.getElementById('role-slug').value = data.slug;
                document.getElementById('role-description').value = data.description || '';
                form.action = '{{ url("admin/users/roles") }}/' + data.id;
                methodInput.value = 'PUT';
            } else {
                title.textContent = 'اضافه کردن نقش جدید';
                document.getElementById('role-name').value = '';
                document.getElementById('role-slug').value = '';
                document.getElementById('role-description').value = '';
                form.action = '{{ route("admin.users.roles.store") }}';
                methodInput.value = 'POST';
                document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
            }

            modal.classList.remove('hidden');
            requestAnimationFrame(() => {
                requestAnimationFrame(() => modal.classList.add('active'));
            });
            document.body.style.overflow = 'hidden';
        }

        function editRole(data) {
            openModal(data);
            // Check existing permissions
            document.querySelectorAll('.permission-checkbox').forEach(cb => {
                cb.checked = data.permissions.some(p => p.id == cb.value);
            });
        }

        function closeModal() {
            const modal = document.getElementById('role-modal');
            modal.classList.remove('active');
            setTimeout(() => modal.classList.add('hidden'), 300);
            document.body.style.overflow = '';
        }

        function toggleAllPermissions() {
            const checkboxes = document.querySelectorAll('.permission-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
        }

        function confirmDeleteRole(id, name) {
            if (confirm('آیا از حذف نقش «' + name + '» مطمئن هستید؟')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url("admin/users/roles") }}/' + id;
                form.innerHTML = '@csrf @method('DELETE')';
                document.body.appendChild(form);
                form.submit();
            }
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    </script>
    @endpush
@endsection
