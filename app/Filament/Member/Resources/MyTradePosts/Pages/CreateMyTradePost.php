<?php

namespace App\Filament\Member\Resources\MyTradePosts\Pages;

use App\Filament\Member\Resources\MyTradePosts\MyTradePostResource;
use App\Models\TradePost;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateMyTradePost extends CreateRecord
{
    protected static string $resource = MyTradePostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            ...$data,
            'member_id' => auth('web')->user()->member->id,
            'slug' => $this->availableSlug($data['title']),
            'status' => 'pending',
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
