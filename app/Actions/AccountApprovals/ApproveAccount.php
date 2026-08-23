<?php

namespace App\Actions\AccountApprovals;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApproveAccount
{
    public function __invoke(User $account, int $reviewerId): void
    {
        DB::transaction(function () use ($account, $reviewerId): void {
            $account->forceFill([
                'is_active' => true,
                'approval_status' => 'approved',
                'account_approved_by' => $reviewerId,
                'account_approved_at' => now(),
            ])->save();
        });
    }
}
