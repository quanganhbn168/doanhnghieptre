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

    public function events(Request $request): View
    {
        $filters = $request->validate([
            'period' => ['nullable', 'string', 'in:upcoming,past'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $period = $filters['period'] ?? 'upcoming';
        $search = trim($filters['q'] ?? '');
        $query = Event::query()->publiclyVisible()
            ->when($search !== '', fn ($query) => $query->where(fn ($events) => $events
                ->where('title', 'like', '%'.$search.'%')->orWhere('summary', 'like', '%'.$search.'%')
                ->orWhere('venue_name', 'like', '%'.$search.'%')));
        $upcomingCount = (clone $query)->ongoingOrUpcoming()->count();
        $pastCount = (clone $query)->past()->count();
        $events = $query->when($period === 'past', fn ($query) => $query->past()->orderByDesc('starts_at'),
            fn ($query) => $query->ongoingOrUpcoming()->orderBy('starts_at'))
            ->orderBy('id')
            ->withSum(['registrations as registered_slots' => fn ($query) => $query->where('status', 'registered')], DB::raw('guest_count + 1'))
            ->paginate(10)->withQueryString()
            ->through(fn (Event $event) => ['event' => $event, ...$this->eventParticipation($event, (int) $event->registered_slots)]);
        $breadcrumbs = [['label' => 'Trang chủ', 'url' => route('home')], ['label' => 'Sự kiện']];

        return view('frontend.association.events', compact('events', 'period', 'search', 'upcomingCount', 'pastCount', 'breadcrumbs'));
    }

    public function eventShow(string $slug): View
    {
        $event = Event::query()->publiclyVisible()->where('slug', $slug)->firstOrFail();
        $registeredSlots = (int) $event->registrations()->where('status', 'registered')->sum(DB::raw('guest_count + 1'));
        $breadcrumbs = [
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Sự kiện', 'url' => route('events.index')],
            ['label' => $event->title],
        ];

        return view('frontend.association.event-detail', [...compact('event', 'breadcrumbs'), ...$this->eventParticipation($event, $registeredSlots)]);
    }

    private function eventParticipation(Event $event, int $registeredSlots): array
    {
        $remainingSlots = $event->capacity ? max(0, $event->capacity - $registeredSlots) : null;
        $registrationIsOpen = $event->registrationIsOpen() && ($remainingSlots === null || $remainingSlots > 0);
        $phase = ($event->ends_at ?? $event->starts_at)->lt(now()) ? 'past' : ($event->starts_at->lte(now()) ? 'ongoing' : 'upcoming');
        $phaseLabel = match ($phase) {
            'past' => 'Đã diễn ra',
            'ongoing' => 'Đang diễn ra',
            default => 'Sắp diễn ra',
        };
        $registrationMessage = match (true) {
            $phase === 'past' => 'Sự kiện đã kết thúc. Hẹn gặp anh/chị tại các chương trình tiếp theo của Hội.',
            $phase === 'ongoing' => 'Chương trình đang diễn ra và đã ngừng nhận đăng ký trực tuyến.',
            $remainingSlots === 0 => 'Sự kiện đã đủ số lượng đăng ký.',
            $event->registration_opens_at?->isFuture() === true => 'Mở đăng ký lúc '.$event->registration_opens_at->format('H:i · d/m/Y').'.',
            $registrationIsOpen => 'Vui lòng điền thông tin để đăng ký tham dự chương trình.',
            default => 'Đã hết thời gian đăng ký tham dự sự kiện này.',
        };

        return compact('remainingSlots', 'registrationIsOpen', 'phase', 'phaseLabel', 'registrationMessage');
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
