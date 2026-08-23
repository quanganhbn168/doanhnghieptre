<?php

namespace App\Http\Controllers\Frontend;

use App\Services\AssociationHomeService;
use Illuminate\View\View;

class HomeController extends FrontendController
{
    public function index(AssociationHomeService $associationHome): View
    {
        $newsItems = $this->homeNews(5);

        if ($newsItems->isEmpty()) {
            $newsItems = $this->news(5);
        }

        return view('frontend.home', [
            ...$associationHome->home(),
            'newsItems' => $newsItems,
            'homepageTitle' => 'DNT Bắc Ninh | Kết nối doanh nghiệp, phát triển bền vững',
            'homepageDescription' => 'Nền tảng thông tin và kết nối của cộng đồng doanh nhân trẻ Bắc Ninh.',
        ]);
    }
}
