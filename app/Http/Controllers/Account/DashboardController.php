<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessStatusHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $member = $user->member;

        $businesses = Business::query()
            ->with(['category', 'chapter', 'industries', 'media'])
            ->withCount('members')
            ->where(function ($query) use ($user, $member): void {
                $query->where('submitted_by_user_id', $user->id);

                if ($member) {
                    $query->orWhereHas('members', fn ($members) => $members->whereKey($member->id));
                }
            })
            ->latest()
            ->get();

        $histories = $businesses->isEmpty()
            ? collect()
            : BusinessStatusHistory::query()
                ->with('business:id,name')
                ->whereIn('business_id', $businesses->modelKeys())
                ->latest('changed_at')
                ->limit(6)
                ->get();

        return view('account.dashboard', compact('user', 'member', 'businesses', 'histories'));
    }
}
