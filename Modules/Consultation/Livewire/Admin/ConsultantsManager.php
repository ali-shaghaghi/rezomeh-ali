<?php

namespace Modules\Consultation\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Consultation\Models\Consultant;

/**
 * CRUD مشاوران — از جدول ادمین‌ها (اعضای تیم، شامل خود صاحب سایت)
 * / تخصص / بیو / عکس / نرخ ساعتی / امتیاز / فعال
 */
class ConsultantsManager extends Component
{
    use WithFileUploads;

    public bool $showForm = false;

    public bool $editing = false;

    public ?int $editId = null;

    public array $form = [
        'admin_id' => '',
        'specialty' => '',
        'bio' => '',
        'hourly_rate' => '',
        'rating' => '0',
        'is_active' => true,
    ];

    // عکس مشاور — آپلود مستقیم یا حفظ عکس فعلی
    public $avatarUpload = null;

    public ?string $existingAvatar = null;

    public bool $successToast = false;

    public string $successMessage = '';

    public function openCreateForm(): void
    {
        $this->resetErrorBag();
        $this->editing = false;
        $this->editId = null;
        $this->form = ['admin_id' => '', 'specialty' => '', 'bio' => '', 'hourly_rate' => '', 'rating' => '0', 'is_active' => true];
        $this->avatarUpload = null;
        $this->existingAvatar = null;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->resetErrorBag();
        $consultant = Consultant::findOrFail($id);

        $this->editing = true;
        $this->editId = $id;
        $this->form = [
            'admin_id' => (string) ($consultant->admin_id ?? ''),
            'specialty' => $consultant->specialty,
            'bio' => $consultant->bio ?? '',
            'hourly_rate' => (string) $consultant->hourly_rate,
            'rating' => (string) $consultant->rating,
            'is_active' => $consultant->is_active,
        ];
        $this->avatarUpload = null;
        $this->existingAvatar = $consultant->avatar;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'form.admin_id' => ['required', 'integer', 'exists:admins,id'],
            'form.specialty' => ['required', 'string', 'max:150'],
            'form.bio' => ['nullable', 'string', 'max:3000'],
            'form.hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'form.rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'form.is_active' => ['boolean'],
            'avatarUpload' => ['nullable', 'image', 'max:2048'],
        ], [], [
            'form.admin_id' => 'عضو تیم',
            'form.specialty' => 'تخصص',
        ]);

        $data = [
            'admin_id' => (int) $this->form['admin_id'],
            // مشاور از جدول ادمین‌ها خوانده می‌شود — پیوند کاربر قدیمی برداشته می‌شود
            'user_id' => null,
            'specialty' => $this->form['specialty'],
            'bio' => $this->form['bio'] ?: null,
            'hourly_rate' => $this->form['hourly_rate'] ?: 0,
            'rating' => $this->form['rating'] ?: 0,
            'is_active' => $this->form['is_active'],
        ];

        if ($this->avatarUpload) {
            $data['avatar'] = $this->avatarUpload->store('consultants/avatars', 'public');
        }

        Consultant::updateOrCreate(['id' => $this->editId], $data);

        $this->showForm = false;
        $this->notifySuccess($this->editing ? 'مشاور ویرایش شد.' : 'مشاور جدید اضافه شد.');
    }

    public function removeAvatar(): void
    {
        if ($this->editId) {
            $consultant = Consultant::find($this->editId);
            if ($consultant?->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($consultant->avatar);
                $consultant->update(['avatar' => null]);
            }
        }
        $this->existingAvatar = null;
        $this->avatarUpload = null;
    }

    public function delete(int $id): void
    {
        Consultant::findOrFail($id)->delete();
        $this->notifySuccess('مشاور حذف شد.');
    }

    /**
     * افزودن سریع یک عضو تیم (ادمین) به عنوان مشاور + باز شدن فرم برای تکمیل مشخصات
     */
    public function quickAdd(int $adminId): void
    {
        $admin = \App\Models\Admin::findOrFail($adminId);

        $consultant = Consultant::firstOrCreate(
            ['admin_id' => $admin->id],
            [
                'specialty' => 'مشاوره تیم',
                'hourly_rate' => 0,
                'rating' => 0,
                'is_active' => true,
            ]
        );

        $this->notifySuccess('«'.$admin->name.'» به فهرست مشاوران اضافه شد — مشخصات را کامل کنید.');
        $this->edit($consultant->id);
    }

    public function toggleActive(int $id): void
    {
        $consultant = Consultant::findOrFail($id);
        $consultant->update(['is_active' => ! $consultant->is_active]);
    }

    public function render()
    {
        $admins = \App\Models\Admin::orderBy('name')->get(['id', 'name']);

        return view('consultation::livewire.admin.consultants-manager', [
            'consultants' => Consultant::with(['user', 'admin'])->latest()->get(),
            // مشاوران از جدول ادمین‌ها (اعضای تیم) خوانده می‌شوند — شامل خود شما
            'admins' => $admins,
            'linkedConsultants' => Consultant::whereIn('admin_id', $admins->pluck('id'))->get()->keyBy('admin_id'),
        ]);
    }

    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }
}