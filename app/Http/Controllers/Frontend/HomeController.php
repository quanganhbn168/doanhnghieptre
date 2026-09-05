<?php

namespace App\Http\Controllers\Frontend;

use App\Filament\Member\Resources\MyTradePosts\MyTradePostResource;
use App\Services\AssociationHomeService;
use Illuminate\View\View;

class HomeController extends FrontendController
{
    public function index(AssociationHomeService $associationHome): View
    {
        $user = auth('web')->user();
        $canPost = $user?->hasApprovedAccount() && $user->hasApprovedBusiness();
        $newsItems = $this->homeNews(5);

        if ($newsItems->isEmpty()) {
            $newsItems = $this->news(5);
        }

        return view('frontend.home', [
            ...$associationHome->home(),
            'newsItems' => $newsItems,
            'tradeSubmissionUrl' => $canPost
                ? MyTradePostResource::getUrl('create', panel: 'member')
                : ($user ? route('account.dashboard') : route('membership.create')),
            'tradeSubmissionLabel' => $canPost ? 'Đăng tin giao thương' : ($user ? 'Theo dõi hồ sơ hội viên' : 'Đăng ký để đăng tin'),
            'homepageTitle' => 'DNT Bắc Ninh | Kết nối doanh nghiệp, phát triển bền vững',
            'homepageDescription' => 'Nền tảng thông tin và kết nối của cộng đồng doanh nhân trẻ Bắc Ninh.',
        ]);
    }
}
