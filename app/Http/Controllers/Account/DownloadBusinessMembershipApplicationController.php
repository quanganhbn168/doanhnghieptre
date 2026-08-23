<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadBusinessMembershipApplicationController extends Controller
{
    public function __invoke(Request $request, Business $business): BinaryFileResponse
    {
        if (! $request->user('admin')) {
            $user = $request->user('web');
            $isSubmitter = $business->submitted_by_user_id === $user?->id;
            $isLinkedMember = $user?->member
                && $business->members()->whereKey($user->member->id)->exists();

            abort_unless($isSubmitter || $isLinkedMember, 403);
        }

        $document = $business->getFirstMedia('signed_membership_application');

        abort_unless($document, 404);

        return response()->download(
            $document->getPath(),
            $document->file_name,
            ['Content-Type' => $document->mime_type ?: 'application/octet-stream'],
        );
    }
}
