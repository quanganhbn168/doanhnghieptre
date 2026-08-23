<?php

namespace App\Filament\Member\Resources\MyBusinesses\Pages;

use App\Filament\Member\Resources\MyBusinesses\MyBusinessResource;
use App\Models\Business;
use App\Services\BusinessService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateMyBusiness extends CreateRecord
{
    protected static string $resource = MyBusinessResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            ...$data,
            'slug' => $this->availableSlug($data['name']),
            'submitted_by_user_id' => auth('web')->id(),
            'status' => 'draft',
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(BusinessService::class)->create($data);
    }

    protected function afterCreate(): void
    {
        $member = auth('web')->user()->member;
        $this->record->members()->syncWithoutDetaching([
            $member->id => [
                'role' => 'representative',
                'job_title' => $this->record->representative_job_title,
                'is_primary' => true,
                'status' => 'active',
                'started_at' => now()->toDateString(),
            ],
        ]);
        $this->record->transitionTo('pending', auth('web')->id(), 'Hội viên đã gửi hồ sơ doanh nghiệp từ cổng hội viên.');
    }

    private function availableSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'doanh-nghiep';
        $slug = $base;
        $suffix = 2;

        while (Business::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
