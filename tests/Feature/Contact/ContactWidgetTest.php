<?php

namespace Tests\Feature\Contact;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Contact\Livewire\FloatingContactWidget;
use Modules\Contact\Models\ContactMessage;
use Modules\Contact\Services\ContactService;
use Modules\Settings\Models\Setting;
use Tests\TestCase;

class ContactWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        // کش تنظیمات بین تست‌ها پاک شود تا هر تست از دیتابیس تازه بشنود
        Setting::flushCache();
    }

    private function user(): User
    {
        $user = User::create(['name' => 'علی شقاقی', 'email' => 'contact-user@test.com', 'password' => 'password', 'is_active' => true]);
        $user->roles()->attach(\Modules\Core\Models\Role::where('slug', 'user')->firstOrFail());

        return $user;
    }

    private function admin(): \App\Models\Admin
    {
        // ادمین از جدول جدا (گارد admin)
        return $this->makeAdmin('contact-admin@test.com', 'super-admin');
    }

    // ===== CHECKPOINT ۹: آیکون شناور در سه لایوت =====

    public function test_widget_renders_on_public_layout(): void
    {
        Setting::set('contact_email', 'admin@example.com', 'contact');

        $this->get('/')->assertOk()->assertSeeLivewire('contact.floating-widget');
    }

    public function test_widget_renders_on_userpanel_layout(): void
    {
        Setting::set('contact_email', 'admin@example.com', 'contact');

        $this->actingAs($this->user())
            ->get('/dashboard')
            ->assertOk()
            ->assertSeeLivewire('contact.floating-widget');
    }

    public function test_widget_renders_on_admin_layout(): void
    {
        Setting::set('contact_email', 'admin@example.com', 'contact');

        $this->actingAs($this->admin(), 'admin')
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSeeLivewire('contact.floating-widget');
    }

    public function test_widget_is_hidden_when_no_channels_configured(): void
    {
        // هیچ تنظیمات تماسی وجود ندارد → ویجت رندر نمی‌شود
        $this->assertEmpty(app(ContactService::class)->socialLinks());
        $this->assertFalse(app(ContactService::class)->hasChannels());

        $this->get('/')->assertDontSee('floating-contact', false);
    }

    // ===== لینک سوشیال از Settings =====

    public function test_social_links_read_from_settings(): void
    {
        Setting::set('social_instagram', 'https://instagram.com/alishaghaghi', 'contact');
        Setting::set('social_telegram', 'https://t.me/alishaghaghi', 'contact');

        $links = collect(app(ContactService::class)->socialLinks())->keyBy('key');

        // شبکه‌های تنظیم‌نشده (مثل github) نباید در لیست باشند
        $this->assertCount(2, $links);
        $this->assertTrue($links->has('instagram'));
        $this->assertSame('https://instagram.com/alishaghaghi', $links['instagram']['url']);
        $this->assertSame('اینستاگرام', $links['instagram']['label']);
        $this->assertTrue($links->has('telegram'));
        $this->assertSame('https://t.me/alishaghaghi', $links['telegram']['url']);
    }

    public function test_whatsapp_link_normalizes_phone_number(): void
    {
        Setting::set('social_whatsapp', '+98 912 123 4567', 'contact');

        $this->assertSame('https://wa.me/989121234567', app(ContactService::class)->whatsappLink());
    }

    public function test_whatsapp_link_passes_through_full_url(): void
    {
        Setting::set('social_whatsapp', 'https://wa.me/989121234567', 'contact');

        $this->assertSame('https://wa.me/989121234567', app(ContactService::class)->whatsappLink());
    }

    public function test_social_url_returns_null_for_unknown_network(): void
    {
        $this->assertNull(app(ContactService::class)->socialUrl('myspace'));
    }

    public function test_contact_info_read_from_settings(): void
    {
        Setting::set('contact_email', 'info@example.com', 'contact');
        Setting::set('contact_phone', '02112345678', 'contact');
        Setting::set('contact_address', 'تهران، ایران', 'contact');

        $info = app(ContactService::class)->contactInfo();

        $this->assertSame('info@example.com', $info['email']);
        $this->assertSame('02112345678', $info['phone']);
        $this->assertSame('تهران، ایران', $info['address']);
    }

    public function test_has_channels_true_with_only_contact_info(): void
    {
        Setting::set('contact_phone', '02112345678', 'contact');

        $this->assertTrue(app(ContactService::class)->hasChannels());
    }

    // ===== فرم پیام (Livewire) =====

    public function test_guest_can_send_message_via_widget(): void
    {
        Setting::set('contact_email', 'admin@example.com', 'contact');

        Livewire::test(FloatingContactWidget::class)
            ->set('name', 'کاربر مهمان')
            ->set('email', 'guest@example.com')
            ->set('message', 'سلام، می‌خواستم درباره قالب‌ها بپرسم.')
            ->call('sendMessage')
            ->assertHasNoErrors()
            ->assertSet('sent', true);

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'کاربر مهمان',
            'email' => 'guest@example.com',
            'message' => 'سلام، می‌خواستم درباره قالب‌ها بپرسم.',
            'status' => 'new',
        ]);
    }

    public function test_widget_prefills_logged_in_user(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)
            ->test(FloatingContactWidget::class)
            ->assertSet('name', $user->name)
            ->assertSet('email', $user->email);
    }

    public function test_message_requires_minimum_length(): void
    {
        Livewire::test(FloatingContactWidget::class)
            ->set('name', 'کاربر')
            ->set('message', 'کوتاه')
            ->call('sendMessage')
            ->assertHasErrors(['message']);
    }

    public function test_name_is_required(): void
    {
        Livewire::test(FloatingContactWidget::class)
            ->set('message', 'این یک پیام تستی طولانی است')
            ->call('sendMessage')
            ->assertHasErrors(['name']);
    }

    public function test_invalid_email_is_rejected(): void
    {
        Livewire::test(FloatingContactWidget::class)
            ->set('name', 'کاربر')
            ->set('email', 'not-an-email')
            ->set('message', 'این یک پیام تستی طولانی است')
            ->call('sendMessage')
            ->assertHasErrors(['email']);
    }

    public function test_message_records_authenticated_user_id(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)
            ->test(FloatingContactWidget::class)
            ->set('message', 'پیام کاربر واردشده برای تست')
            ->call('sendMessage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contact_messages', [
            'user_id' => $user->id,
            'name' => $user->name,
        ]);
    }

    public function test_message_form_resets_after_send(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)
            ->test(FloatingContactWidget::class)
            ->set('message', 'پیام تستی برای ریست فرم')
            ->call('sendMessage')
            ->assertSet('message', '')
            ->assertSet('name', $user->name);
    }

    // ===== روت POST /contact/message =====

    public function test_contact_route_stores_message(): void
    {
        Setting::set('contact_email', 'admin@example.com', 'contact');

        $this->post(route('public.contact.message'), [
            'name' => 'کاربر روت',
            'email' => 'route@example.com',
            'message' => 'پیام از طریق روت POST',
        ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', ['name' => 'کاربر روت']);
    }

    // ===== مدل =====

    public function test_unread_scope_returns_only_new_messages(): void
    {
        $new = ContactMessage::create(['name' => 'الف', 'message' => 'پیام جدید', 'status' => 'new']);
        ContactMessage::create(['name' => 'ب', 'message' => 'پیام خوانده‌شده', 'status' => 'read']);

        $this->assertSame(1, ContactMessage::unread()->count());
        $this->assertSame($new->id, ContactMessage::unread()->first()->id);
    }

    public function test_status_label_attribute(): void
    {
        $message = ContactMessage::create(['name' => 'الف', 'message' => 'تست', 'status' => 'new']);

        $this->assertSame('جدید', $message->status_label);
        $this->assertTrue($message->is_unread);
    }

    public function test_unread_count_service(): void
    {
        ContactMessage::create(['name' => 'الف', 'message' => 'تست ۱', 'status' => 'new']);
        ContactMessage::create(['name' => 'ب', 'message' => 'تست ۲', 'status' => 'new']);
        ContactMessage::create(['name' => 'ج', 'message' => 'تست ۳', 'status' => 'read']);

        $this->assertSame(2, app(ContactService::class)->unreadCount());
    }

    public function test_send_message_without_admin_email_does_not_throw(): void
    {
        // هیچ contact_email تنظیم نشده → فقط لاگ می‌شود، رکورد ذخیره می‌شود
        $message = app(ContactService::class)->sendMessage([
            'name' => 'کاربر',
            'message' => 'پیام بدون ایمیل مدیر',
        ]);

        $this->assertDatabaseHas('contact_messages', ['id' => $message->id]);
    }
}
