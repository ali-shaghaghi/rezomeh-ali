<x-userpanel.layout :title="'تیکت جدید'">
    {{-- فرم تیکت جدید — Livewire از ماژول Tickets --}}
    <livewire:tickets.ticket-conversation :create="true" :key="'ticket-create'" />
</x-userpanel.layout>
