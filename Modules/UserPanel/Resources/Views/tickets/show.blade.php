<x-userpanel.layout :title="'تیکت'">
    {{-- گفتگوی تیکت — Livewire زنده از ماژول Tickets --}}
    <livewire:tickets.ticket-conversation :ticket-id="$ticketId" :key="'ticket-conversation-'.$ticketId" />
</x-userpanel.layout>
