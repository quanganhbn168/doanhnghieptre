<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\MembershipNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MembershipTrackingController extends Controller
{
    public function show(Business $business): View
    {
        $business->load(['chapter', 'statusHistories', 'media']);

        return view('account.membership-tracking', [
            'business' => $business,
            'editUrl' => in_array($business->status, ['draft', 'changes_requested'], true)
                ? URL::temporarySignedRoute('membership.track.edit', now()->addDays(30), ['business' => $business->id]) : null,
            'documentUrl' => $business->hasMedia('signed_membership_application')
                ? URL::temporarySignedRoute('membership.track.document', now()->addMinutes(30), ['business' => $business->id]) : null,
            'steps' => [
                ['label' => 'Văn phòng kiểm tra', 'done' => (bool) $business->association_approved_at || $business->status === 'approved', 'active' => $business->status === 'pending'],
                ['label' => 'Chi hội thẩm định', 'done' => (bool) $business->chapter_reviewed_at || $business->status === 'approved', 'active' => $business->status === 'chapter_pending'],
                ['label' => 'Trưởng ban chuẩn y', 'done' => $business->status === 'approved', 'active' => $business->status === 'board_pending'],
            ],
        ]);
    }

    public function requestLink(Request $request): RedirectResponse
    {
        $data = $request->validate(['application_code' => ['required', 'string', 'max:40'], 'email' => ['required', 'email', 'max:255']]);
        $email = mb_strtolower(trim($data['email']));
        $business = Business::query()->where('application_code', trim($data['application_code']))
            ->where(fn ($query) => $query
                ->where(fn ($guest) => $guest->whereNull('submitted_by_user_id')->where('application_email', $email))
                ->orWhereHas('submittedBy', fn ($users) => $users->where('email', $email)))->first();
        if ($business) {
            app(MembershipNotificationService::class)->updated($business);
        }

        return back()->with('success', 'Nếu mã hồ sơ và email trùng khớp, Hội sẽ gửi liên kết theo dõi đến email đã đăng ký.');
    }

    public function document(Business $business): BinaryFileResponse
    {
        $document = $business->getFirstMedia('signed_membership_application');
        abort_unless($document, 404);

        return response()->download($document->getPath(), $document->file_name, ['Content-Type' => $document->mime_type]);
    }
}
