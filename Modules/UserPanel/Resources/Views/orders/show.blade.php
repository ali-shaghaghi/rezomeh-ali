<x-userpanel.layout :title="'سفارش'">
    {{-- جزئیات سفارش — Livewire زنده از ماژول Orders --}}
    <livewire:orders.order-details :order-number="$orderNumber" :key="'order-details-'.$orderNumber" />
</x-userpanel.layout>
