<?php

use App\Providers\AppServiceProvider;
use Modules\AdminPanel\Providers\AdminPanelServiceProvider;
use Modules\Analytics\Providers\AnalyticsServiceProvider;
use Modules\Auth\Providers\AuthServiceProvider;
use Modules\Blog\Providers\BlogServiceProvider;
use Modules\Contact\Providers\ContactServiceProvider;
use Modules\Notifications\Providers\NotificationsServiceProvider;
use Modules\Consultation\Providers\ConsultationServiceProvider;
use Modules\Core\Providers\CoreServiceProvider;
use Modules\Orders\Providers\OrdersServiceProvider;
use Modules\Products\Providers\ProductsServiceProvider;
use Modules\ProjectDev\Providers\ProjectDevServiceProvider;
use Modules\Public\Providers\PublicServiceProvider;
use Modules\Resume\Providers\ResumeServiceProvider;
use Modules\SMS\Providers\SmsServiceProvider;
use Modules\Settings\Providers\SettingsServiceProvider;
use Modules\Tickets\Providers\TicketsServiceProvider;
use Modules\Users\Providers\UsersServiceProvider;
use Modules\UserPanel\Providers\UserPanelServiceProvider;
use Modules\SEO\Providers\SeoServiceProvider;
use Modules\AI\Providers\AiServiceProvider;
use Modules\Gallery\Providers\GalleryServiceProvider;

return [
    AppServiceProvider::class,
    CoreServiceProvider::class,
    AuthServiceProvider::class,
    AdminPanelServiceProvider::class,
    UsersServiceProvider::class,
    UserPanelServiceProvider::class,
    SmsServiceProvider::class,
    ConsultationServiceProvider::class,
    PublicServiceProvider::class,
    ResumeServiceProvider::class,
    BlogServiceProvider::class,
    OrdersServiceProvider::class,
    TicketsServiceProvider::class,
    ProjectDevServiceProvider::class,
    ProductsServiceProvider::class,
    ContactServiceProvider::class,
    SettingsServiceProvider::class,
    AnalyticsServiceProvider::class,
    NotificationsServiceProvider::class,
    SeoServiceProvider::class,
    AiServiceProvider::class,
    GalleryServiceProvider::class,
];
