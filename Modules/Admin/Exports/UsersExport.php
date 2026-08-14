<?php

namespace Modules\Admin\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return User::with('roles')->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'email',
            'email_verified_at',
            'phone',
            'phone_verified_at',
            'two_factor_enabled',
            'last_login_at',
            'last_login_ip',
            'is_active',
            'social_provider',
            'social_id',
            'avatar',
            'role',
            'created_at',
            'updated_at',
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i:s') : '',
            $user->phone ?? '',
            $user->phone_verified_at ? $user->phone_verified_at->format('Y-m-d H:i:s') : '',
            $user->two_factor_enabled ? 'بله' : 'خیر',
            $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : '',
            $user->last_login_ip ?? '',
            $user->is_active ? 'فعال' : 'غیرفعال',
            $user->social_provider ?? '',
            $user->social_id ?? '',
            $user->avatar ?? '',
            $user->roles->pluck('slug')->implode(',') ?: '',
            $user->created_at->format('Y-m-d H:i:s'),
            $user->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}