<?php

namespace Modules\Tickets\Policies;

use App\Models\User;
use Modules\Tickets\Models\Ticket;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage_tickets', 'view_tickets']);
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->hasAnyPermission(['manage_tickets', 'view_tickets']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage_tickets');
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('manage_tickets');
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('manage_tickets');
    }
}
