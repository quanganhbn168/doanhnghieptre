<?php

namespace App\Filament\Resources\ProfessionalGroups\Schemas;

use App\Models\Business;
use Filament\Forms\Components\Select;

class ProfessionalGroupSelect
{
    public static function make(): Select
    {
        return Select::make('industries')->label('Khối ngành nghề')
            ->relationship('industries', 'name', modifyQueryUsing: fn ($query) => $query->memberGroups()->where('is_active', true)->orderBy('sort_order'))
            ->multiple()->required()->minItems(1)->maxItems(5)
            ->maxItemsMessage('Mỗi doanh nghiệp chỉ được chọn tối đa 5 khối ngành nghề.')
            ->exists(table: 'industries', column: 'id', modifyRuleUsing: fn ($rule) => $rule->where('is_member_group', true)->where('is_active', true))
            ->helperText(function (?Business $record): string {
                $help = 'Chọn từ 1 đến tối đa 5 khối; khối đầu tiên là khối chính.';
                $legacy = $record?->industries->filter(fn ($industry) => ! $industry->is_member_group || ! $industry->is_active)->pluck('name')->join(', ');

                return $legacy ? $help.' Phân loại cũ: '.$legacy.'. Vui lòng chọn lại theo danh mục hiện tại.' : $help;
            })
            ->saveRelationshipsUsing(function (Business $record, ?array $state): void {
                // The option query excludes retired groups; sync the full relation after validation
                // so an explicit new selection also replaces historical, out-of-scope pivots.
                $record->industries()->sync(collect($state ?? [])->values()->mapWithKeys(
                    fn ($id, $index) => [(int) $id => ['is_primary' => $index === 0]],
                )->all());
            })
            ->searchable()->preload();
    }
}
