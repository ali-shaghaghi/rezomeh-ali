<?php

namespace Modules\Orders\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\OrderTask;
use Modules\Orders\Services\OrderService;

class OrdersIndex extends Component
{
    use WithPagination;

    // فیلترها
    public string $search = '';

    public string $status = '';

    public string $priority = '';

    // مرتب‌سازی — پیش‌فرض: نزدیک‌ترین ددلاین (برنامه کاری)
    public string $sortBy = 'deadline';

    // حالت صفحه: لیست | ایجاد | ویرایش
    public bool $showForm = false;

    public bool $editing = false;

    public ?Order $editingOrder = null;

    // فیلدهای فرم
    public string $title = '';

    public string $description = '';

    public string $order_status = 'pending';

    public string $order_priority = 'medium';

    public $amount = '';

    public $user_id = '';

    public $deadline = null;

    public $start_date = null;

    // مودال مدیریت تسک‌ها (برنامه کاری)
    public bool $tasksModalOpen = false;

    public ?Order $tasksOrder = null;

    public bool $taskEditing = false;

    public int $taskEditId = 0;

    // فیلدهای فرم تسک
    public string $task_title = '';

    public string $task_description = '';

    public string $task_status = 'todo';

    public $task_start_date = null;

    public $task_due_date = null;

    public $task_progress = 0;

    // مودال پرداخت
    public bool $paymentModalOpen = false;

    public ?Order $paymentOrder = null;

    public $payment_amount = '';

    public string $payment_method = 'زرین‌پال';

    public string $payment_transaction_id = '';

    public string $payment_description = '';

    // مودال حذف
    public bool $confirmingDelete = false;

    public $deleteId = null;

    // نوتیفیکیشن
    public bool $successToast = false;

    public string $successMessage = '';

    // آغاز با فیلتر وضعیت (برای صفحه «در انتظار»)
    public function mount(string $initialStatus = ''): void
    {
        $this->status = $initialStatus;
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'priority' => ['except' => ''],
        'sortBy' => ['except' => 'deadline'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPriority(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $service = app(OrderService::class);

        $orders = $service->list([
            'search' => $this->search,
            'status' => $this->status,
            'priority' => $this->priority,
            'sort' => $this->sortBy,
        ], 9);

        return view('orders::livewire.admin.orders-index', [
            'orders' => $orders,
            'stats' => $service->stats(),
        ]);
    }

    // ===== فرم ایجاد/ویرایش =====

    public function openCreateForm(): void
    {
        $this->authorize('create', Order::class);

        $this->resetErrorBag();
        $this->editing = false;
        $this->editingOrder = null;
        $this->title = '';
        $this->description = '';
        $this->order_status = 'pending';
        $this->order_priority = 'medium';
        $this->amount = '';
        // سفارش باید به یک کاربر واقعی (جدول users) تعلق بگیرد —
        // شناسه ادمین اینجا معتبر نیست؛ ادمین باید مشتری را از فرم انتخاب کند.
        $this->user_id = auth()->user() instanceof \App\Models\Admin ? '' : auth()->id();
        $this->deadline = null;
        $this->start_date = null;
        $this->showForm = true;
    }

    public function openEditForm(int $id): void
    {
        $order = Order::findOrFail($id);
        $this->authorize('update', $order);

        $this->resetErrorBag();
        $this->editing = true;
        $this->editingOrder = $order;
        $this->title = $order->title;
        $this->description = $order->description ?? '';
        $this->order_status = $order->status;
        $this->order_priority = $order->priority;
        $this->amount = $order->amount;
        $this->user_id = $order->user_id;
        $this->deadline = $order->deadline?->format('Y-m-d');
        $this->start_date = $order->start_date?->format('Y-m-d');
        $this->showForm = true;
    }

    public function saveOrder(): void
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order_status' => 'required|in:pending,review,accepted,development,completed,cancelled',
            'order_priority' => 'required|in:low,medium,high,urgent',
            'amount' => 'required|numeric|min:0',
            'user_id' => 'required|exists:users,id',
            'deadline' => 'nullable|date',
            'start_date' => 'nullable|date',
        ];

        $validated = $this->validate($rules);

        $data = [
            'user_id' => $validated['user_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'status' => $validated['order_status'],
            'priority' => $validated['order_priority'],
            'amount' => $validated['amount'],
            'deadline' => $validated['deadline'] ?: null,
            'start_date' => $validated['start_date'] ?: null,
        ];

        if ($this->editing) {
            $this->authorize('update', $this->editingOrder);
            $order = app(OrderService::class)->save($this->editingOrder, $data);
            $message = 'سفارش «'.$order->title.'» به‌روزرسانی شد.';
        } else {
            $this->authorize('create', Order::class);
            $order = app(OrderService::class)->save(new Order, $data);
            $message = 'سفارش «'.$order->title.'» با شماره '.$order->order_number.' ایجاد شد.';
        }

        $this->showForm = false;
        $this->notifySuccess($message);
    }

    // ===== مدیریت تسک‌ها (برنامه کاری ددلاین‌محور) =====

    public function openTasksModal(int $id): void
    {
        $order = Order::with('tasks')->findOrFail($id);
        $this->authorize('update', $order);

        $this->tasksOrder = $order;
        $this->tasksModalOpen = true;
    }

    public function openTaskCreate(): void
    {
        $this->resetErrorBag();
        $this->taskEditing = false;
        $this->taskEditId = 0;
        $this->task_title = '';
        $this->task_description = '';
        $this->task_status = 'todo';
        $this->task_progress = 0;
        // پیش‌فرض بازه: از امروز تا ددلاین سفارش
        $this->task_start_date = now()->format('Y-m-d');
        $this->task_due_date = $this->tasksOrder?->deadline?->format('Y-m-d');
    }

    public function openTaskEdit(int $taskId): void
    {
        $task = OrderTask::findOrFail($taskId);
        $this->authorize('update', $task->order);

        $this->resetErrorBag();
        $this->taskEditing = true;
        $this->taskEditId = $task->id;
        $this->task_title = $task->title;
        $this->task_description = $task->description ?? '';
        $this->task_status = $task->status;
        $this->task_start_date = $task->start_date?->format('Y-m-d');
        $this->task_due_date = $task->due_date?->format('Y-m-d');
        $this->task_progress = $task->progress;
    }

    public function saveTask(): void
    {
        $this->authorize('update', $this->tasksOrder);

        $validated = $this->validate([
            'task_title' => 'required|string|max:255',
            'task_description' => 'nullable|string|max:2000',
            'task_status' => 'required|in:todo,in_progress,done',
            'task_start_date' => 'nullable|date',
            'task_due_date' => 'nullable|date|after_or_equal:task_start_date',
            'task_progress' => 'required|integer|min:0|max:100',
        ], [
            'task_title.required' => 'عنوان تسک الزامی است.',
            'task_due_date.after_or_equal' => 'تاریخ پایان باید بعد از تاریخ شروع باشد.',
        ]);

        $data = [
            'title' => $validated['task_title'],
            'description' => $validated['task_description'] ?: null,
            'status' => $validated['task_status'],
            'start_date' => $validated['task_start_date'] ?: null,
            'due_date' => $validated['task_due_date'] ?: null,
            'progress' => $validated['task_progress'],
            'sort_order' => $this->tasksOrder->tasks()->max('sort_order') + 1,
        ];

        if ($this->taskEditing) {
            $task = OrderTask::findOrFail($this->taskEditId);
            $task->update($data);
            $message = 'تسک «'.$task->title.'» به‌روزرسانی شد.';
        } else {
            $task = $this->tasksOrder->tasks()->create($data);
            $message = 'تسک «'.$task->title.'» اضافه شد.';
        }

        $this->tasksOrder->load('tasks');
        $this->openTaskCreate();
        $this->notifySuccess($message);
    }

    public function quickTaskProgress(int $taskId, int $progress): void
    {
        $task = OrderTask::findOrFail($taskId);
        $this->authorize('update', $task->order);

        $task->update([
            'progress' => max(0, min(100, $progress)),
            'status' => $progress >= 100 ? 'done' : ($progress > 0 ? 'in_progress' : 'todo'),
        ]);

        $this->tasksOrder->load('tasks');
        $this->notifySuccess('پیشرفت تسک «'.$task->title.'» به '.$progress.'٪ رسید.');
    }

    public function toggleTaskDone(int $taskId): void
    {
        $task = OrderTask::findOrFail($taskId);
        $this->authorize('update', $task->order);

        $task->update([
            'status' => $task->status === 'done' ? 'todo' : 'done',
            'progress' => $task->status === 'done' ? 0 : 100,
        ]);

        $this->tasksOrder->load('tasks');
        $this->notifySuccess('تسک «'.$task->title.'» '.($task->status === 'done' ? 'انجام‌شده علامت خورد' : 'به حالت بازگشت').'.');
    }

    public function deleteTask(int $taskId): void
    {
        $task = OrderTask::findOrFail($taskId);
        $this->authorize('update', $task->order);

        $task->delete();
        $this->tasksOrder->load('tasks');
        $this->openTaskCreate();
        $this->notifySuccess('تسک «'.$task->title.'» حذف شد.');
    }

    // ===== عملیات ریاکتیو =====

    // تغییر سریع وضعیت از روی کارت
    public function quickStatus(int $id, string $status): void
    {
        $order = Order::findOrFail($id);
        $this->authorize('update', $order);

        app(OrderService::class)->setStatus($order, $status);
        $this->notifySuccess('وضعیت سفارش «'.$order->title.'» به «'.$order->status_label.'» تغییر کرد.');
    }

    // تغییر سریع اولویت از روی کارت
    public function quickPriority(int $id, string $priority): void
    {
        $order = Order::findOrFail($id);
        $this->authorize('update', $order);

        app(OrderService::class)->setPriority($order, $priority);
        $this->notifySuccess('اولویت سفارش «'.$order->title.'» به «'.$order->priority_label.'» تغییر کرد.');
    }

    // ===== پرداخت =====

    public function openPaymentModal(int $id): void
    {
        $order = Order::findOrFail($id);
        $this->authorize('update', $order);

        $this->resetErrorBag();
        $this->paymentOrder = $order;
        // مبلغ پیشنهادی: باقیمانده سفارش
        $remaining = max(0, (float) $order->amount - (float) $order->paid_amount);
        $this->payment_amount = $remaining > 0 ? $remaining : '';
        $this->payment_method = 'زرین‌پال';
        $this->payment_transaction_id = '';
        $this->payment_description = '';
        $this->paymentModalOpen = true;
    }

    public function savePayment(): void
    {
        $this->authorize('update', $this->paymentOrder);

        $validated = $this->validate([
            'payment_amount' => 'required|numeric|min:1000',
            'payment_method' => 'required|string|max:255',
            'payment_transaction_id' => 'nullable|string|max:255',
            'payment_description' => 'nullable|string|max:500',
        ]);

        app(OrderService::class)->addPayment($this->paymentOrder, [
            'amount' => $validated['payment_amount'],
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['payment_transaction_id'] ?: null,
            'description' => $validated['payment_description'] ?: null,
            'status' => 'completed',
        ]);

        $this->paymentModalOpen = false;
        $this->notifySuccess('پرداخت برای سفارش «'.$this->paymentOrder->title.'» ثبت شد.');
    }

    // ===== حذف =====

    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function deleteOrder(): void
    {
        $order = Order::findOrFail($this->deleteId);
        $this->authorize('delete', $order);

        app(OrderService::class)->delete($order);

        $this->confirmingDelete = false;
        $this->deleteId = null;
        $this->notifySuccess('سفارش «'.$order->title.'» با موفقیت حذف شد.');
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'priority', 'sortBy']);
        $this->resetPage();
    }

    // نمایش نوتیفیکیشن موفقیت
    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }
}
