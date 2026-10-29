<?php

namespace Modules\Blog\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Blog\Services\BlogService;
use Modules\Core\Models\Comment;

class CommentsManager extends Component
{
    use WithPagination;

    // جستجو و فیلتر
    public string $search = '';

    public string $statusFilter = '';

    // مودال حذف
    public bool $confirmingDelete = false;

    public $deleteId = null;

    // مودال پاسخ ادمین به نظر
    public bool $replying = false;

    public $replyId = null;

    public string $replyContent = '';

    // نوتیفیکیشن
    public bool $successToast = false;

    public string $successMessage = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $service = app(BlogService::class);

        $comments = $service->commentsList([
            'search' => $this->search,
            'status' => $this->statusFilter,
        ]);

        // آمار نظرات
        $stats = [
            'total' => Comment::count(),
            'pending' => Comment::where('status', 'pending')->count(),
            'approved' => Comment::where('status', 'approved')->count(),
            'rejected' => Comment::where('status', 'rejected')->count(),
        ];

        return view('blog::livewire.admin.comments-manager', [
            'comments' => $comments,
            'stats' => $stats,
        ]);
    }

    // تایید نظر (ریاکتیو)
    public function approve(int $id): void
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('update', $comment);

        app(BlogService::class)->setCommentStatus($comment, 'approved');
        $this->notifySuccess('نظر تایید شد.');
    }

    // رد نظر (ریاکتیو)
    public function reject(int $id): void
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('update', $comment);

        app(BlogService::class)->setCommentStatus($comment, 'rejected');
        $this->notifySuccess('نظر رد شد.');
    }

    // بازگشت به حالت در انتظار تایید
    public function pend(int $id): void
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('update', $comment);

        app(BlogService::class)->setCommentStatus($comment, 'pending');
        $this->notifySuccess('نظر به حالت در انتظار تایید بازگشت.');
    }

    /** باز کردن مودال پاسخ به نظر (زیر هر کامنت در پنل ادمین) */
    public function startReply(int $id): void
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('update', $comment);

        $this->replyId = $id;
        $this->replyContent = '';
        $this->replying = true;
        $this->resetErrorBag();
    }

    public function cancelReply(): void
    {
        $this->replying = false;
        $this->replyId = null;
        $this->replyContent = '';
        $this->resetErrorBag();
    }

    /**
     * ثبت پاسخ ادمین — برای هر دو نوع پاسخ (ادمین و کاربر) انتشار نهایی
     * فقط با تأیید مدیر انجام می‌شود، پس وضعیت پیش‌فرض pending است.
     */
    public function submitReply(): void
    {
        $parent = Comment::findOrFail($this->replyId);
        $this->authorize('update', $parent);

        $this->validate([
            'replyContent' => ['required', 'string', 'min:2', 'max:3000'],
        ], [
            'replyContent.required' => 'متن پاسخ الزامی است.',
            'replyContent.min' => 'متن پاسخ باید حداقل ۲ کاراکتر باشد.',
            'replyContent.max' => 'متن پاسخ نمی‌تواند بیش از ۳۰۰۰ کاراکتر باشد.',
        ]);

        // حداکثر یک سطح عمق: پاسخ به یک پاسخ → به همان ریشه متصل می‌شود
        Comment::create([
            'user_id' => null,
            'admin_id' => auth()->id(),
            'parent_id' => $parent->parent_id ?? $parent->id,
            'blog_post_id' => $parent->blog_post_id,
            'content' => trim($this->replyContent),
            'status' => 'pending',
        ]);

        $this->cancelReply();
        $this->notifySuccess('پاسخ ثبت شد و پس از تأیید منتشر می‌شود.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function deleteComment(): void
    {
        $comment = Comment::findOrFail($this->deleteId);
        $this->authorize('delete', $comment);

        $comment->delete();
        $this->confirmingDelete = false;
        $this->deleteId = null;
        $this->notifySuccess('نظر با موفقیت حذف شد.');
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'statusFilter']);
        $this->resetPage();
    }

    // نمایش نوتیفیکیشن موفقیت
    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }
}
