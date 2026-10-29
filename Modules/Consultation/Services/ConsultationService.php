<?php

namespace Modules\Consultation\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Consultation\Enums\ConsultationStatus;
use Modules\Consultation\Models\Consultant;
use Modules\Consultation\Models\Consultation;
use Modules\Consultation\Models\ConsultationSlot;
use Modules\Consultation\Models\ConsultationType;
use Modules\Orders\Enums\PaymentMethod;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\OrderService;
use Modules\Orders\Services\PaymentService;

/**
 * رزرو مشاوره — تراکنشی: قفل اسلات + رزرو + سفارش + پرداخت + SMS
 */
class ConsultationService
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * رزرو مشاوره: قفل اسلات → ساخت رزرو → ساخت Order → SMS کاربر و مدیر
     *
     * @param  array{name?: string, phone?: string, email?: string, notes?: string}  $info
     */
    public function book(User $user, Consultant $consultant, ConsultationType $type, ConsultationSlot $slot, array $info = []): Consultation
    {
        return DB::transaction(function () use ($user, $consultant, $type, $slot, $info) {
            // قفل اسلات — جلوگیری از رزرو همزمان (double-booking)
            $locked = ConsultationSlot::whereKey($slot->id)
                ->where('is_booked', false)
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                throw new \RuntimeException('این زمان همین الان توسط دیگری رزرو شد؛ لطفاً زمان دیگری انتخاب کنید.');
            }

            $locked->update([
                'is_booked' => true,
                'booked_at' => now(),
            ]);

            // سفارش برای پرداخت مبلغ مشاوره
            $order = $this->orderService->save(new Order, [
                'user_id' => $user->id,
                'title' => 'مشاوره '.$type->name.' — '.$consultant->personName(),
                'description' => ($info['notes'] ?? null) ?: ('رزرو مشاوره '.$type->name.' با '.$consultant->personName()),
                'status' => 'pending',
                'amount' => $type->price,
                'paid_amount' => 0,
            ]);

            $consultation = Consultation::create([
                'user_id' => $user->id,
                'consultant_id' => $consultant->id,
                'type_id' => $type->id,
                'slot_id' => $locked->id,
                'order_id' => $order->id,
                'mode' => $type->mode,
                'status' => ConsultationStatus::Pending->value,
                'notes' => $info['notes'] ?? null,
            ]);

            // پرداخت مشاوره از طریق PaymentService مشترک (درگاه)
            // ponytail: فعلاً درگاه شبیه‌ساز است — وایمی تا callback واقعی
            $this->paymentService->create($order, [
                'amount' => $type->price,
                'method' => PaymentMethod::Gateway,
                'description' => 'مشاوره '.$type->name.' — '.$consultant->personName(),
            ]);

            // اطلاع‌رسانی SMS — کاربر و مدیر (در حالت LogDriver فقط لاگ می‌شود)
            $this->notify(
                $user,
                $consultant,
                $type,
                $locked,
                $info['name'] ?? $user->name,
                $info['phone'] ?? null
            );

            // اعلان درون‌برنامه‌ای «مشاوره رزرو شد» (ماژول Notifications)
            try {
                app(\Modules\Notifications\Services\NotificationService::class)
                    ->notifyUsersWithPermission(
                        'view_notifications',
                        \Modules\Notifications\Notifications\ConsultationBookedNotification::class,
                        [
                            'user_name' => $user->name,
                            'type_name' => $type->name,
                            'date' => optional($locked->start_at)->format('Y/m/d H:i'),
                        ]
                    );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Notification failed: '.$e->getMessage());
            }

            return $consultation;
        });
    }

    /**
     * تغییر وضعیت رزرو — لغو، اسلات را آزاد می‌کند
     */
    public function setStatus(Consultation $consultation, ConsultationStatus $status): Consultation
    {
        // «عدم حضور» برگشت‌ناپذیر است: پس از ثبت، دیگر نمی‌توان به حالت حاضر (تاییدشده)
        // یا هر وضعیت دیگری بازگشت — نه از پنل ادمین، نه از پنل کاربر.
        if ($consultation->status === ConsultationStatus::NoShow->value
            && $status !== ConsultationStatus::NoShow) {
            throw new \RuntimeException('وضعیت «عدم حضور» برگشت‌ناپذیر است و قابل تغییر نیست.');
        }

        if ($consultation->status === $status->value) {
            return $consultation->refresh();
        }

        return DB::transaction(function () use ($consultation, $status) {
            $consultation->update(['status' => $status->value]);

            if ($status === ConsultationStatus::Cancelled) {
                $consultation->slot()->update([
                    'is_booked' => false,
                    'booked_at' => null,
                ]);
            }

            return $consultation->refresh();
        });
    }

    /**
     * ارسال SMS اطلاع‌رسانی به کاربر و مدیر
     */
    private function notify(User $user, Consultant $consultant, ConsultationType $type, ConsultationSlot $slot, string $name, ?string $contactPhone = null): void
    {
        $consultantName = $consultant->admin?->name ?? $consultant->user?->name ?? 'مشاور';
        // تاریخ شمسی در پیامک (قرارداد پنل و کاربران ایرانی)
        $date = jdate('Y/m/d', $slot->date);
        $time = substr($slot->start_time, 0, 5);

        $userPhone = $contactPhone ?: ($user->phone ?: '');

        // SMS به کاربر
        if ($userPhone) {
            send_sms($userPhone, 'consultation_booked_user', [
                'name' => $name,
                'consultant' => $consultantName,
                'date' => $date,
                'time' => $time,
            ], $user);
        }

        // SMS به مدیر — شماره از تنظیمات
        $adminPhone = \Modules\Settings\Models\Setting::get('contact_phone', '');

        if ($adminPhone) {
            send_sms($adminPhone, 'consultation_booked_admin', [
                'user' => $name,
                'consultant' => $consultantName,
                'date' => $date,
                'time' => $time,
            ]);
        }
    }
}