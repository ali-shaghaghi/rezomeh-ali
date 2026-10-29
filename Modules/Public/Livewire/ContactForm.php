<?php

namespace Modules\Public\Livewire;

use Illuminate\Support\Facades\Mail;
use Livewire\Component;

/**
 * فرم تماس عمومی — پیام به ایمیل مدیر سایت
 */
class ContactForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    public bool $sent = false;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'min:3', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'name' => __('public.contact.name'),
            'email' => __('public.contact.email'),
            'subject' => __('public.contact.subject'),
            'message' => __('public.contact.message'),
        ];
    }

    public function send(): void
    {
        $validated = $this->validate();

        // ایمیل مدیر از تنظیمات (fallback: ایمیل پروفایل رزومه)
        $to = \Modules\Settings\Models\Setting::get('contact_email')
            ?: \Modules\Resume\Models\ResumeProfile::current()->email;

        try {
            Mail::raw(
                "نام: {$validated['name']}\nایمیل: {$validated['email']}\nموضوع: {$validated['subject']}\n\nپیام:\n{$validated['message']}",
                function ($mail) use ($to, $validated) {
                    $mail->to($to)
                        ->subject('تماس از سایت: '.$validated['subject'])
                        ->replyTo($validated['email'], $validated['name']);
                }
            );

            $this->sent = true;
            $this->reset(['name', 'email', 'subject', 'message']);
            $this->dispatch('contact-sent');
        } catch (\Throwable) {
            $this->addError('send', __('public.contact.send_failed'));
        }
    }

    public function render()
    {
        return view('public::livewire.contact-form');
    }
}
