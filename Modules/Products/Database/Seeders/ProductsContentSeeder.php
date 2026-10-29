<?php

namespace Modules\Products\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Products\Enums\LicenseType;
use Modules\Products\Enums\ProductType;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;

class ProductsContentSeeder extends Seeder
{
    /**
     * محتوای نمونه: دسته‌بندی + محصولات + فایل دانلودی نمونه
     */
    public function run(): void
    {
        // ===== دسته‌بندی‌ها =====
        $categoryDefs = [
            // [fa name, slug, fa desc, en name, en desc]
            ['قالب‌های وب', 'web-templates', 'قالب‌های آماده وب‌سایت', 'Web Templates', 'Ready-to-use website templates'],
            ['CRM و پنل مدیریت', 'crm-panels', 'نرم‌افزارهای مدیریت کسب‌وکار', 'CRM & Admin Panels', 'Business management software'],
            ['اسکریپت‌ها و ابزارها', 'scripts', 'اسکریپت‌های کاربردی و ابزارها', 'Scripts & Tools', 'Useful scripts and utilities'],
        ];

        $categoryIds = [];
        foreach ($categoryDefs as [$name, $slug, $description, $nameEn, $descriptionEn]) {
            $categoryIds[$name] = ProductCategory::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $description,
                    'name_en' => $nameEn,
                    'description_en' => $descriptionEn,
                    'is_active' => true,
                ]
            )->id;
        }

        // ===== محصولات =====
        // [fa name, slug, fa desc, en name, en desc, price, sale, type, category, published, showInResume, file]
        $productDefs = [
            ['قالب فروشگاهی رزومه', 'resume-shop-template',
             'قالب فروشگاهی رزومه با پنل کاربری',
             'Resume Shop Template', 'Resume shop template with a user panel.',
             2500000, 1900000, ProductType::Template, 'قالب‌های وب', true, true, 'products/resume-shop-template.zip'],
            ['CRM مدیریت مشتریان', 'customer-crm',
             'سیستم مدیریت ارتباط با مشتری با گزارش‌گیری',
             'Customer CRM', 'A customer relationship management system with reporting.',
             8500000, null, ProductType::Crm, 'CRM و پنل مدیریت', true, true, 'products/customer-crm.zip'],
            ['اسکریپت ارسال خبرنامه', 'newsletter-script',
             'اسکریپت خودکار ارسال خبرنامه',
             'Newsletter Sender Script', 'An automated newsletter sending script.',
             1200000, 980000, ProductType::Script, 'اسکریپت‌ها و ابزارها', true, false, 'products/newsletter-script.zip'],
            ['قالب وبلاگ سرعت بالا', 'fast-blog-template',
             'قالب وبلاگ سبک و سئوفرندلی',
             'Fast Blog Template', 'A lightweight, SEO-friendly blog template.',
             1800000, null, ProductType::Template, 'قالب‌های وب', true, true, 'products/fast-blog-template.zip'],
            ['پنل مدیریت انبار', 'inventory-panel',
             'سیستم مدیریت موجودی انبار',
             'Inventory Management Panel', 'An inventory and stock management system.',
             6500000, 5900000, ProductType::Crm, 'CRM و پنل مدیریت', true, false, 'products/inventory-panel.zip'],
            ['ربات تلگرام پاسخگوی خودکار', 'telegram-bot-script',
             'ربات پاسخگوی خودکار به پیام‌ها',
             'Telegram Auto-Reply Bot', 'An automatic reply bot for Telegram messages.',
             900000, null, ProductType::Script, 'اسکریپت‌ها و ابزارها', true, false, 'products/telegram-bot-script.zip'],
        ];

        foreach ($productDefs as [$name, $slug, $desc, $nameEn, $descEn, $price, $salePrice, $type, $categoryName, $published, $showInResume, $filePath]) {
            Product::firstOrCreate(['slug' => $slug], [
                'category_id' => $categoryIds[$categoryName] ?? null,
                'name' => $name,
                'name_en' => $nameEn,
                'description' => $desc,
                'description_en' => $descEn,
                'short_description' => $desc,
                'short_description_en' => $descEn,
                'price' => $price,
                'sale_price' => $salePrice,
                'type' => $type->value,
                'file_path' => $filePath,
                'demo_url' => 'https://alishaghaghi.ir/demo/'.$slug,
                'version' => '1.0.0',
                'is_published' => $published,
                'show_in_resume' => $showInResume,
            ]);
        }

        // ===== لایسنس نمونه برای ادمین (برای تست MyProducts) =====
        $admin = User::first();
        $product = Product::first();

        if ($admin && $product) {
            \Modules\Products\Models\License::firstOrCreate(
                ['license_key' => '11111111-1111-1111-1111-111111111111'],
                [
                    'user_id' => $admin->id,
                    'product_id' => $product->id,
                    'type' => LicenseType::Lifetime->value,
                    'max_activations' => LicenseType::Lifetime->maxActivations(),
                    'activations_count' => 0,
                    'expires_at' => null,
                ]
            );
        }
    }
}
