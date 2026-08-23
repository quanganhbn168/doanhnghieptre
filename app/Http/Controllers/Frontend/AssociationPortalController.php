<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Intro;
use App\Models\TradePost;
use App\Services\AssociationHomeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssociationPortalController extends Controller
{
    public function about(): View
    {
        $intros = Intro::query()
            ->visibleOnSite()
            ->orderBy('sort_order')
            ->get();

        return view('frontend.association.about', compact('intros'));
    }

    public function aboutShow(string $slug): View
    {
        $intro = Intro::query()
            ->visibleOnSite()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('frontend.association.intro', compact('intro'));
    }

    public function businesses(AssociationHomeService $associationHome): View
    {
        return view('frontend.association.index', [
            'pageTitle' => 'Doanh nghiệp',
            'pageLead' => 'Khám phá các doanh nghiệp đã được xác thực và công bố trên nền tảng.',
            'pageIcon' => 'fa-building',
            'itemType' => 'business',
            'items' => $associationHome->businesses(24),
        ]);
    }

    public function events(AssociationHomeService $associationHome): View
    {
        return view('frontend.association.index', [
            'pageTitle' => 'Sự kiện',
            'pageLead' => 'Các chương trình kết nối, chia sẻ và xúc tiến thương mại sắp diễn ra.',
            'pageIcon' => 'fa-calendar-days',
            'itemType' => 'event',
            'items' => $associationHome->events(24),
        ]);
    }

    public function trade(Request $request, AssociationHomeService $associationHome): View
    {
        $filters = $request->validate([
            'type' => ['nullable', 'string', 'in:'.implode(',', array_keys(TradePost::TYPE_OPTIONS))],
        ]);

        return view('frontend.association.index', [
            'pageTitle' => 'Giao thương',
            'pageLead' => 'Cơ hội mua bán, hợp tác và kết nối nhu cầu giữa các doanh nghiệp.',
            'pageIcon' => 'fa-handshake',
            'itemType' => 'trade',
            'items' => $associationHome->tradePosts(24, $filters['type'] ?? null),
            'tradeTypes' => TradePost::TYPE_OPTIONS,
            'activeTradeType' => $filters['type'] ?? null,
        ]);
    }

    public function tradeShow(string $slug): View
    {
        $tradePost = TradePost::query()
            ->with(['business', 'member', 'industries'])
            ->where('slug', $slug)
            ->where('status', 'approved')
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->firstOrFail();

        return view('frontend.association.trade-detail', compact('tradePost'));
    }
}
