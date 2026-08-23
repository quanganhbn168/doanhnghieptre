<?php

namespace App\Actions\BusinessApplications;

use App\Models\Business;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class StoreSignedMembershipApplication
{
    public function __invoke(Business $business, UploadedFile $document): Media
    {
        return $business
            ->addMedia($document)
            ->usingName('Đơn gia nhập Hội đã ký')
            ->usingFileName('don-gia-nhap-hoi-da-ky-'.$business->id.'.'.$document->extension())
            ->toMediaCollection('signed_membership_application');
    }
}
