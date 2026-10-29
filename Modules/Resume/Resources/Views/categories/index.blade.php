@extends('adminpanel::layouts.master')

@section('title', 'دسته‌بندی‌های پروژه')

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
    .category-card {
        opacity: 0;
        transform: translateY(20px);
    }
    .category-card.show {
        animation: slideUp 0.5s ease-out forwards;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
    {{-- عنوان و دکمه افزودن --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">دسته‌بندی‌های پروژه</h2>
            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $categories->total() }} دسته‌بندی</p>
        </div>
        <button onclick="openModal()" class="group flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/25 transition-all duration-200 hover:shadow-xl hover:shadow-primary-500/35 hover:-translate-y-0.5 active:translate-y-0">
            <span class="flex items-center justify-center w-5 h-5 transition duration-300 rounded-md bg-white/20 group-hover:rotate-90">
                <x-icon name="plus" size="3" />
            </span>
            افزودن دسته‌بندی جدید
        </button>
    </div>
    {{-- مودال افزودن/ویرایش --}}
    <div id="category-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/50 backdrop-blur-sm modal-backdrop" onclick="if(event.target===this)closeModal()">
        <div class="animate-slide-up modal-panel w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200/50 bg-white p-4 shadow-2xl dark:border-slate-700/50 dark:bg-surface-800 sm:p-5">
            {{-- Header --}}
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center h-9 w-9 rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                        <x-icon name="folder" size="4" />
                    </span>
                    <div>
                        <h3 id="modal-title" class="text-base font-bold">افزودن دسته‌بندی جدید</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">اطلاعات دسته‌بندی را وارد کنید</p>
                    </div>
                </div>
                <button onclick="closeModal()" class="p-2 transition rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                    <x-icon name="close" />
                </button>
            </div>

            <form id="category-form" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST" />

                {{-- نام و اسلاگ --}}
                <div class="grid gap-3 mb-1 sm:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">نام <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="category-name" required
                            class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                            placeholder="مثال: طراحی وب" />
                        @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">Slug</label>
                        <input type="text" name="slug" id="category-slug"
                            class="w-full px-3 py-2 text-sm transition border rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                            placeholder="خالی بگذارید تا خودکار تولید شود" />
                        @error('slug')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- توضیحات --}}
                <div>
                    <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-300">توضیحات</label>
                    <textarea name="description" id="category-description" rows="3"
                            class="w-full px-3 py-2 text-sm transition border resize-none rounded-xl border-slate-200 bg-slate-50 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white"
                            placeholder="توضیحات دسته‌بندی..."></textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- دکمه‌ها --}}
                <div class="flex gap-3 pt-3 mt-2">
                    <button type="submit" class="flex-1 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-500/25 transition-all hover:shadow-lg hover:shadow-primary-500/35 hover:-translate-y-0.5 active:translate-y-0">
                        ذخیره دسته‌بندی
                    </button>
                    <button type="button" onclick="closeModal()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                        لغو
                    </button>
                </div>
            </form>
        </div>
    </div>
    {{-- گرید کارت‌های دسته‌بندی --}}
    <div class="grid gap-6 sm:grid-cols-2 animate-slide-up">
        @foreach($categories as $index => $category)
            <section class="flex flex-col category-card chart-card">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-bold">{{ $category->name }}</h3>
                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                        {{ $category->projects_count }} پروژه
                    </span>
                </div>

                <p class="mb-3 text-sm text-slate-500 dark:text-slate-400">{{ $category->description ?: 'بدون توضیحات' }}</p>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-700">
                    <p class="mt-1 mb-1 text-xs font-medium text-slate-500">شناسه اسلاگ:</p>
                    <div class="flex flex-wrap gap-1.5">
                        <span class="px-2 py-1 text-xs rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                            {{ $category->slug }}
                        </span>
                    </div>
                </div>

                {{-- دکمه‌های عملیات --}}
                <div class="flex gap-2 pt-3 mt-4 border-slate-100 dark:border-slate-700">
                    <button onclick='editCategory({{ json_encode($category) }})' 
                            class="flex-1 px-3 py-2 text-xs font-medium text-center transition rounded-lg bg-slate-100 text-slate-600 hover:bg-primary-100 hover:text-primary-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-primary-900/40 dark:hover:text-primary-300">
                        ویرایش
                    </button>
                    <form action="{{ route('admin.resume.portfolio.categories.destroy', $category->id) }}" method="POST" class="flex-1">
                        @csrf @method('DELETE')
                        <button type="submit" 
                                class="w-full px-3 py-2 text-xs font-medium transition rounded-lg bg-slate-100 text-rose-500 hover:bg-rose-100 dark:bg-slate-700 dark:text-rose-400 dark:hover:bg-rose-900/30"
                                onclick="return confirm('آیا از حذف دسته‌بندی «{{ $category->name }}» مطمئن هستید؟')">
                            حذف
                        </button>
                    </form>
                </div>
            </section>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if ($categories->hasPages())
        <div class="mt-6">
            {{ $categories->links() }}
        </div>
    @endif

    @push('scripts')
    <script>
        // انیمیشن کارت‌ها هنگام بارگذاری
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.category-card').forEach((card, i) => {
                setTimeout(() => card.classList.add('show'), i * 80);
            });
        });

        function openModal(data = null) {
            const modal = document.getElementById('category-modal');
            const title = document.getElementById('modal-title');
            const form = document.getElementById('category-form');
            const methodInput = document.getElementById('form-method');

            if (data) {
                title.textContent = 'ویرایش دسته‌بندی: ' + data.name;
                document.getElementById('category-name').value = data.name;
                document.getElementById('category-slug').value = data.slug || '';
                document.getElementById('category-description').value = data.description || '';
                form.action = '{{ url("admin/resume/portfolio/categories") }}/' + data.id;
                methodInput.value = 'PUT';
            } else {
                title.textContent = 'افزودن دسته‌بندی جدید';
                document.getElementById('category-name').value = '';
                document.getElementById('category-slug').value = '';
                document.getElementById('category-description').value = '';
                form.action = '{{ route("admin.resume.portfolio.categories.store") }}';
                methodInput.value = 'POST';
            }

            // پاک کردن خطاهای قبلی (اختیاری)
            document.querySelectorAll('#category-form .text-red-500').forEach(el => el.remove());

            modal.classList.remove('hidden');
            requestAnimationFrame(() => {
                requestAnimationFrame(() => modal.classList.add('active'));
            });
            document.body.style.overflow = 'hidden';
        }

        function editCategory(data) {
            openModal(data);
        }

        function closeModal() {
            const modal = document.getElementById('category-modal');
            modal.classList.remove('active');
            setTimeout(() => modal.classList.add('hidden'), 300);
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    </script>
    @endpush
@endsection