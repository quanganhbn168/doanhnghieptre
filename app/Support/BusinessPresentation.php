<?php

namespace App\Support;

use Illuminate\Support\Str;

class BusinessPresentation
{
    public const SIZES = ['small' => 'Quy mô nhỏ', 'medium' => 'Quy mô vừa', 'large' => 'Quy mô lớn'];

    public const TYPES = [
        'limited' => 'Công ty TNHH', 'joint_stock' => 'Công ty cổ phần', 'private' => 'Doanh nghiệp tư nhân',
        'household' => 'Hộ kinh doanh', 'cooperative' => 'Hợp tác xã', 'other' => 'Loại hình khác',
    ];

    public static function statusColor(string $status): string
    {
        return match ($status) {
            'approved' => 'success', 'chapter_pending' => 'info', 'pending' => 'warning', 'rejected' => 'danger', default => 'gray',
        };
    }

    public static function location(?string $district, ?string $province): ?string
    {
        $location = collect([$district, $province])->map(fn ($value) => trim((string) $value))
            ->filter()->unique(fn (string $value) => Str::lower($value))->join(' · ');

        return $location ?: null;
    }

    public static function phoneUrl(?string $phone): ?string
    {
        $number = preg_replace('/[^0-9+]/', '', (string) $phone);

        return preg_match('/^\+?[0-9]{5,15}$/', $number) ? 'tel:'.$number : null;
    }

    public static function emailUrl(?string $email): ?string
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? 'mailto:'.$email : null;
    }

    public static function websiteUrl(?string $website): ?string
    {
        $website = trim((string) $website);
        if ($website === '') {
            return null;
        }

        $url = preg_match('#^https?://#i', $website) ? $website : 'https://'.$website;
        $parts = parse_url($url);

        return filter_var($url, FILTER_VALIDATE_URL) && ! isset($parts['user']) && ! isset($parts['pass']) ? $url : null;
    }
}
