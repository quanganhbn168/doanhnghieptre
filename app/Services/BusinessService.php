<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Arr;

class BusinessService
{
    public function __construct(private readonly MediaService $mediaService) {}

    public function create(array $data): Business
    {
        $business = Business::query()->create($this->payload($data));
        $this->syncLogo($business, $data);

        return $business;
    }

    public function update(Business $business, array $data): void
    {
        $business->update($this->payload($data));
        $this->syncLogo($business, $data);
    }

    private function payload(array $data): array
    {
        return Arr::except($data, ['logo', 'logo_remove', 'industries']);
    }

    private function syncLogo(Business $business, array $data): void
    {
        $this->mediaService->syncSingle($business, 'logo', $data['logo'] ?? null, (bool) ($data['logo_remove'] ?? false));
    }
}
