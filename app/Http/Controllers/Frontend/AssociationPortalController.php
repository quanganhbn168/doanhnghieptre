<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\AssociationHomeService;
use Illuminate\View\View;

class AssociationPortalController extends Controller
{
    public function about(): View
    {
        return view('frontend.association.about');
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

    public function trade(AssociationHomeService $associationHome): View
    {
        return view('frontend.association.index', [
            'pageTitle' => 'Giao thương',
            'pageLead' => 'Cơ hội mua bán, hợp tác và kết nối nhu cầu giữa các doanh nghiệp.',
            'pageIcon' => 'fa-handshake',
            'itemType' => 'trade',
            'items' => $associationHome->tradePosts(24),
        ]);
    }
}
