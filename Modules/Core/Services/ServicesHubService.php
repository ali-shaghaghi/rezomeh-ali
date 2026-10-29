<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Log;

/**
 * Services Hub — رجیستر مرکزی برای اتصال بین‌ماژولی سرویس‌ها.
 *
 * هر ماژول در ServiceProvider خود سرویس‌های قابل‌اشتراک را اینجا ثبت می‌کند
 * و سایر ماژول‌ها بدون وابستگی مستقیم به کلاس، از طریق کلید آن‌ها را کشف می‌کنند.
 *
 * ponytail: این یک رجیستر ساده در-حافظه است (O(1) lookup)؛
 * برای سناریوهای توزیع‌شده باید با یک cache/persistent store جایگزین شود.
 */
class ServicesHubService
{
    /**
     * سرویس‌های ثبت‌شده: key => callable که نمونه سرویس را برمی‌گرداند
     *
     * @var array<string, callable>
     */
    protected array $services = [];

    /**
     * ثبت یک سرویس در هاب
     *
     * @param  string  $key  کلید یکتای سرویس (مثلاً 'projectdev', 'sms')
     * @param  callable  $factory  تابعی که نمونه سرویس را برمی‌گرداند (lazy resolution)
     */
    public function register(string $key, callable $factory): void
    {
        $this->services[$key] = $factory;
    }

    /**
     * آیا سرویسی با این کلید ثبت شده است؟
     */
    public function has(string $key): bool
    {
        return isset($this->services[$key]);
    }

    /**
     * گرفتن سرویس ثبت‌شده — اگر یافت نشد null برمی‌گرداند
     */
    public function get(string $key): mixed
    {
        if (! $this->has($key)) {
            return null;
        }

        return ($this->services[$key])();
    }

    /**
     * گرفتن سرویس یا پرتاب exception
     *
     * @throws \RuntimeException
     */
    public function require(string $key): mixed
    {
        $service = $this->get($key);

        if ($service === null) {
            throw new \RuntimeException("سرویس «{$key}» در Services Hub ثبت نشده است.");
        }

        return $service;
    }

    /**
     * لیست کلید تمام سرویس‌های ثبت‌شده
     *
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys($this->services);
    }

    /**
     * لاگ‌گیری سرویس‌های ثبت‌شده (دیباگ)
     */
    public function debug(): void
    {
        Log::info('Services Hub registered services', ['services' => $this->keys()]);
    }
}
