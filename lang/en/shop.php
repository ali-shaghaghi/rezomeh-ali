<?php

/*
|--------------------------------------------------------------------------
| Shop (products / cart / checkout) — English
|--------------------------------------------------------------------------
*/

return [

    // ===== Products page =====
    'title' => 'Digital Products',
    'badge' => 'Template · CRM · Script',
    'subtitle' => 'Ready-made templates, software and scripts — get your license right after purchase.',
    'search_in_products' => 'Search products...',
    'all_categories' => 'All categories',
    'all_types' => 'All types',
    'sort' => [
        'newest' => 'Newest',
        'cheapest' => 'Cheapest',
        'most_expensive' => 'Most expensive',
        'popular' => 'Most popular',
    ],
    'clear_filters' => 'Clear filters',
    'loading' => 'Loading...',
    'no_products' => 'No products found with these filters.',
    'show_all_products' => 'Show all products',
    'view_details' => 'View details',
    'general' => 'General',
    'no_category' => 'Uncategorized',
    'discount_percent' => ':percent% OFF',
    'version' => 'v:version',
    'category_label' => 'Category: :category',
    'product_price' => 'Product price',
    'downloadable' => 'Downloadable',
    'view_demo' => 'View live demo',
    'breadcrumb' => [
        'home' => 'Home',
        'products' => 'Products',
    ],

    // ===== Product types =====
    'types' => [
        'template' => 'Template',
        'crm' => 'CRM / Software',
        'script' => 'Script',
        'other' => 'Other',
    ],

    // ===== License types =====
    'licenses' => [
        'single' => 'Single-user',
        'multi' => 'Multi-user',
        'lifetime' => 'Lifetime',
    ],
    'license_label' => ':type license',
    'license_activations' => ':type license — usable on :count devices',

    // ===== Cart =====
    'cart_title' => 'Shopping Cart',
    'cart_badge' => 'Cart',
    'cart_heading' => 'Your cart',
    'cart_subtitle' => 'Your selected products — you can change quantities and proceed to payment.',
    'cart_empty' => 'Your cart is empty',
    'item_not_found' => 'Cart item not found.',
    'invalid_discount' => 'Invalid discount code.',
    'invalid_payment_method' => 'Invalid payment method.',
    'crypto_disabled' => 'Crypto payment is currently disabled.',
    'cart_empty_desc' => 'Pick products from the store to get started.',
    'browse_products' => 'Browse products',
    'cart_items' => 'Cart items',
    'quantity' => 'Quantity',
    'quantity_colon' => 'Qty: :count',
    'remove_item' => 'Remove item',
    'clear_cart' => 'Clear cart',
    'subtotal' => 'Subtotal',
    'payable' => 'Amount due',
    'checkout_now' => 'Checkout & pay',
    'vat_note' => 'VAT is calculated at the final step.',
    'added_to_cart' => 'Product added to cart',
    'adding' => 'Adding...',
    'added' => '✓ Added',
    'add_to_cart' => 'Add to cart',
    'please_wait' => 'Please wait...',
    'online_demo' => 'Live demo',
    'recommendations' => [
        'special' => 'Special picks for you',
        'similar' => 'You might also like these',
        'based_on' => 'Based on your interest in:',
        'add' => 'Add :name to cart',
    ],

    // ===== Checkout — steps =====
    'steps' => [
        'review' => 'Cart review',
        'personal' => 'Personal info',
        'invoice' => 'Invoice',
        'payment' => 'Payment',
        'review_short' => 'Order',
        'personal_short' => 'Details',
        'invoice_short' => 'Invoice',
        'payment_short' => 'Pay',
        'badge' => 'Complete purchase',
        'step_of' => 'Step :step — :title',
        'review_desc' => 'Review your products, license type and discount code.',
        'personal_desc' => 'Your contact details will appear on the invoice.',
        'invoice_desc' => 'Check the totals, 9% VAT, discount and payment method.',
        'payment_desc' => 'Complete the payment and receive your product licenses.',
    ],
    'checkout_title' => 'Checkout',

    // ===== Step 1 =====
    'review_order' => 'Order review',
    'discount_code' => 'Discount code',
    'discount_code_placeholder' => 'Discount code (e.g. SAVE10)',
    'discount_applied' => 'Code ":code" applied — :percent% off',
    'remove' => 'Remove',
    'apply_code' => 'Apply code',
    'order_summary' => 'Order summary',
    'items_subtotal' => 'Items subtotal',
    'discount' => 'Discount',
    'discount_percent_short' => 'Discount (:percent%)',
    'discount_code_short' => 'Discount (code :code)',
    'vat' => 'VAT (:percent%)',
    'vat_short' => 'VAT (:percent%)',
    'continue_to_personal' => 'Continue to personal info',
    'back_to_cart' => 'Back to cart',
    'back' => 'Back',

    // ===== Step 2 =====
    'personal_info' => 'Personal info',
    'personal_info_desc' => 'Your contact details will appear on the invoice. These fields are pre-filled from your profile.',
    'full_name' => 'Full name *',
    'full_name_placeholder' => 'e.g. Ali Shaghaghi',
    'phone' => 'Phone number *',
    'email_optional' => 'Email (optional)',
    'notes_optional' => 'Notes (optional)',
    'notes_placeholder' => 'Anything we should know...',
    'continue_to_invoice' => 'Continue to invoice',
    'your_cart' => 'Your cart',

    // ===== Step 3 =====
    'invoice' => 'Invoice',
    'col_product' => 'Product',
    'col_license' => 'License',
    'col_quantity' => 'Qty',
    'col_price' => 'Price',
    'final_amount' => 'Total',
    'payment_method' => 'Payment method',
    'continue_to_payment' => 'Continue to payment',

    // ===== Step 4 =====
    'payment' => 'Payment',
    'selected_method' => 'Your selected payment method:',
    'manual_title' => 'Manual payment (card-to-card)',
    'manual_desc' => 'After placing the order, the card number and invoice will be shown. Your order is activated after admin approval and your license will be issued.',
    'crypto_title' => 'Pay with crypto wallet (USDT)',
    'crypto_desc' => 'The equivalent USDT amount is calculated after the order is placed and the wallet address is shown. After the transfer, send the transaction (TxID) to confirm your order.',
    'wallet_address' => 'Wallet address (:network):',
    'crypto_warning' => '⚠️ Please deposit only on the :network network.',
    'installment_title' => 'Installment plan (30/30/40)',
    'installment_first' => 'Down payment (30%)',
    'installment_second' => 'Second installment (30%)',
    'installment_third' => 'Third installment (40%)',
    'installment_desc' => 'The order is settled after full payment and the license is issued.',
    'gateway_title' => 'Online gateway',
    'gateway_desc' => 'You will be redirected to the payment gateway. After a successful payment, your product licenses are issued immediately and your cart is emptied.',
    'pay_now' => 'Pay & complete purchase',
    'processing' => 'Processing...',
    'total' => 'Total',

    // ===== Payment methods =====
    'payment_methods' => [
        'gateway' => 'Online gateway',
        'installment' => 'Installment plan (30/30/40)',
        'manual' => 'Card-to-card / manual',
        'crypto' => 'Crypto wallet (USDT)',
    ],

    // ===== Product page tabs (description/features) =====
    'tab_description' => 'Description',
    'tab_features' => 'Features',
    'features' => [
        'category' => 'Category',
        'product_type' => 'Product type',
        'version' => 'Version',
        'licenses' => 'License types',
        'download' => 'File download',
        'demo' => 'Live demo',
        'yes' => 'Yes',
        'no' => 'No',
    ],
];
