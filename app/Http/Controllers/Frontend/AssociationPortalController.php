<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Intro;
use App\Models\TradePost;
use App\Services\AssociationHomeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function eventShow(string $slug): View
    {
        $event = Event::query()->publiclyVisible()->where('slug', $slug)->firstOrFail();
        $registeredSlots = (int) $event->registrations()->where('status', 'registered')->sum(DB::raw('guest_count + 1'));
        $remainingSlots = $event->capacity ? max(0, $event->capacity - $registeredSlots) : null;
        $registrationIsOpen = $event->registrationIsOpen() && ($remainingSlots === null || $remainingSlots > 0);

        return view('frontend.association.event-detail', compact('event', 'remainingSlots', 'registrationIsOpen'));
    }

    public function trade(Request $request): View
    {
        $filters = $request->validate([
            'type' => ['nullable', 'string', 'in:'.implode(',', array_keys(TradePost::TYPE_OPTIONS))],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $search = trim($filters['q'] ?? '');
        $items = TradePost::query()->publiclyVisible()->with('business')
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($search !== '', fn ($query) => $query->where(fn ($posts) => $posts
                ->where('title', 'like', '%'.$search.'%')->orWhere('summary', 'like', '%'.$search.'%')
                ->orWhereHas('business', fn ($businesses) => $businesses->where('name', 'like', '%'.$search.'%'))))
            ->orderByDesc('approved_at')->orderByDesc('id')->paginate(24)->withQueryString();
        $items->getCollection()->each(fn (TradePost $post) => $post->setAttribute('business_name', $post->business?->name));

        return view('frontend.association.index', [
            'pageTitle' => 'Chợ doanh nghiệp',
            'pageLead' => 'Tìm dịch vụ, giới thiệu sản phẩm và kết nối trực tiếp với doanh nghiệp hội viên.',
            'pageIcon' => 'fa-handshake',
            'itemType' => 'trade',
            'items' => $items,
            'search' => $search,
            'tradeTypes' => TradePost::TYPE_OPTIONS,
            'activeTradeType' => $filters['type'] ?? null,
        ]);
    }

    public function tradeShow(string $slug): View
    {
        $tradePost = TradePost::query()
            ->with(['business', 'member', 'industries'])
            ->where('slug', $slug)
            ->publiclyVisible()
            ->firstOrFail();

        return view('frontend.association.trade-detail', compact('tradePost'));
    }
}
