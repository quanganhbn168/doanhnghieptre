<?php

namespace App\Filament\Resources\TradePosts\Pages;

use App\Filament\Resources\TradePosts\TradePostResource;
use App\Models\TradePost;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateTradePost extends CreateRecord
{
    protected static string $resource = TradePostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            ...$data,
            'slug' => $this->availableSlug($data['title']),
            'status' => 'draft',
        ];
    }

    private function availableSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'co-hoi-giao-thuong';
        $slug = $base;
        $suffix = 2;

        while (TradePost::query()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
