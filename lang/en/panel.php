<?php

/*
|--------------------------------------------------------------------------
| User panel — English (dashboard, orders, tickets & common)
|--------------------------------------------------------------------------
*/

return [
    // Panel shell sidebar tabs
    'tabs' => [
        'dashboard' => 'Dashboard',
        'orders' => 'Orders',
        'products' => 'My products (licenses)',
        'projects' => 'My projects',
        'tickets' => 'Tickets',
        'consultations' => 'My consultations',
        'notifications' => 'Notifications',
        'security' => 'Settings',
    ],
    'open_menu' => 'Open user panel menu',
    'logout' => 'Sign out',
    'logout_confirm' => 'Are you sure you want to sign out?',

    // Dashboard
    'greeting' => 'Hi, :name 👋',
    'realtime_hint' => 'Switch between sections in real time — no page reload',
    'stats' => [
        'orders_total' => 'Total orders',
        'orders_active' => 'In progress',
        'orders_completed' => 'Completed',
        'total_spent' => 'Paid (Toman)',
        'tickets_total' => 'Total tickets',
        'tickets_open' => 'Open tickets',
    ],
    'need_consultation' => 'Need a consultation?',
    'need_consultation_desc' => 'Book your expert consultation session online',
    'request_consultation' => 'Request consultation',
    'latest_orders' => 'Latest orders',
    'latest_tickets' => 'Latest tickets',
    'view_all' => 'View all →',
    'no_orders_yet' => "You don't have any orders yet.",
    'no_tickets_yet' => "You don't have any tickets yet.",
    'request_project' => 'Request a project',
    'create_new_ticket' => 'Create a new ticket',

    // Orders
    'my_orders' => 'My orders',
    'new_order' => 'New order',
    'search_orders' => 'Search by order number or title...',
    'filter_all' => 'All',
    'filter_pending' => 'Pending',
    'filter_review' => 'In review',
    'filter_accepted' => 'Accepted',
    'filter_developing' => 'In development',
    'filter_completed' => 'Completed',
    'filter_cancelled' => 'Canceled',
    'paid' => 'Paid',
    'amount' => 'Amount:',
    'toman' => 'Toman',
    'deadline' => 'Deadline:',
    'paid_amount' => 'Paid: :amount Toman',
    'view_order' => 'View order',
    'no_orders' => 'No orders found!',
    'no_orders_desc' => "You haven't placed any orders yet.",
    'change_filters' => 'Try changing the filters.',
    'create_order' => 'Place a new order',

    // Tickets
    'my_tickets' => 'My tickets',
    'new_ticket' => 'New ticket',
    'filter_open' => 'Open',
    'filter_in_progress' => 'In progress',
    'filter_waiting' => 'Waiting for your reply',
    'filter_closed' => 'Closed',
    'ticket_status_open' => 'Open',
    'ticket_status_in_progress' => 'In progress',
    'ticket_status_waiting' => 'Waiting for your reply',
    'ticket_status_closed' => 'Closed',
    'priority_low' => 'Priority: low',
    'priority_medium' => 'Priority: medium',
    'priority_high' => 'Priority: high',
    'priority_urgent' => 'Priority: urgent',
    'replies' => ':count replies',
    'view_conversation' => 'View conversation',
    'date' => 'Date:',
];
