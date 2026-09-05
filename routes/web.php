<?php

use App\Http\Controllers\Account\BusinessApplicationController;
use App\Http\Controllers\Account\DashboardController;
use App\Http\Controllers\Account\DownloadBusinessMembershipApplicationController;
use App\Http\Controllers\Account\MembershipTrackingController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Frontend;
use App\Http\Middleware\PrivateMembershipResponse;
use Illuminate\Support\Facades\Route;

Route::get('/dang-ky-hoi-vien', [BusinessApplicationController::class, 'create'])->name('membership.create');
Route::post('/dang-ky-hoi-vien', [BusinessApplicationController::class, 'store'])->middleware('throttle:frontend-forms')->name('membership.store');
Route::redirect('/register', '/dang-ky-hoi-vien')->name('register');
Route::view('/tra-cuu-ho-so', 'account.membership-lookup')->name('membership.lookup');
Route::post('/tra-cuu-ho-so', [MembershipTrackingController::class, 'requestLink'])->middleware('throttle:frontend-forms')->name('membership.lookup.send');
Route::middleware(['signed', PrivateMembershipResponse::class])->prefix('ho-so-gia-nhap/{business}')->group(function (): void {
    Route::get('/', [MembershipTrackingController::class, 'show'])->name('membership.track');
    Route::get('/bo-sung', [BusinessApplicationController::class, 'edit'])->name('membership.track.edit');
    Route::patch('/bo-sung', [BusinessApplicationController::class, 'update'])->middleware('throttle:frontend-forms')->name('membership.track.update');
    Route::get('/don-gia-nhap', [MembershipTrackingController::class, 'document'])->name('membership.track.document');
});

Route::middleware(['auth', 'account.approved'])->prefix('tai-khoan')->as('account.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/doanh-nghiep/dang-ky', fn () => to_route('membership.create'))->name('businesses.create');
    Route::post('/doanh-nghiep', [BusinessApplicationController::class, 'store'])->middleware('throttle:frontend-forms')->name('businesses.store');
    Route::get('/doanh-nghiep/{business}/chinh-sua', [BusinessApplicationController::class, 'edit'])->name('businesses.edit');
    Route::patch('/doanh-nghiep/{business}', [BusinessApplicationController::class, 'update'])->middleware('throttle:frontend-forms')->name('businesses.update');
});

Route::get('/ho-so-doanh-nghiep/{business}/don-gia-nhap/tai-ve', DownloadBusinessMembershipApplicationController::class)
    ->middleware('auth:web,admin')
    ->name('business.membership-application.download');

Route::get('/', [Frontend\HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [Frontend\SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [Frontend\SeoController::class, 'robots'])->name('robots');
Route::get('/gioi-thieu', [Frontend\AssociationPortalController::class, 'about'])->name('about');
Route::get('/gioi-thieu/{slug}', [Frontend\AssociationPortalController::class, 'aboutShow'])->name('about.show');
Route::get('/doanh-nghiep', [Frontend\AssociationPortalController::class, 'businesses'])->name('businesses.index');
Route::get('/danh-ba-doanh-nghiep', [Frontend\BusinessDirectoryController::class, 'index'])->name('directory.index');
Route::get('/su-kien', [Frontend\AssociationPortalController::class, 'events'])->name('events.index');
Route::get('/su-kien/{slug}', [Frontend\AssociationPortalController::class, 'eventShow'])->name('events.show');
Route::post('/su-kien/{event}/dang-ky', [Frontend\EventRegistrationController::class, 'store'])->middleware('throttle:frontend-forms')->name('events.register');
Route::get('/giao-thuong', [Frontend\AssociationPortalController::class, 'trade'])->name('trade.index');
Route::get('/giao-thuong/{slug}', [Frontend\AssociationPortalController::class, 'tradeShow'])->name('trade.show');
Route::get('/dich-vu', [Frontend\ServiceController::class, 'index'])->name('services.index');
Route::get('/dich-vu/{slug}', [Frontend\ServiceController::class, 'resolve'])->name('services.show');
Route::get('/du-an', [Frontend\ProjectController::class, 'index'])->name('projects.index');
Route::get('/du-an/{slug}', [Frontend\ProjectController::class, 'resolve'])->name('projects.show');
Route::get('/khach-hang', [Frontend\ClientController::class, 'index'])->name('clients.index');
Route::get('/bang-gia', [Frontend\PricingController::class, 'index'])->name('pricing');
Route::get('/tin-tuc', [Frontend\PostController::class, 'index'])->name('news.index');
Route::get('/tin-tuc/{slug}', [Frontend\SlugController::class, 'show'])->name('news.show');
Route::get('/lien-he', [Frontend\ContactController::class, 'index'])->name('contact');
Route::view('/chinh-sach-bao-mat', 'frontend.policies.privacy')->name('policies.privacy');
Route::post('/lien-he', [Frontend\ContactController::class, 'submit'])->middleware('throttle:frontend-forms')->name('contact.submit');
Route::post('/dang-ky-nhan-tin', [Frontend\NewsletterController::class, 'store'])->middleware('throttle:frontend-forms')->name('newsletter.store');
Route::post('/binh-luan', [Frontend\CommentController::class, 'store'])->middleware('throttle:frontend-forms')->name('comments.store');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Slug động theo domain (dùng cho các route nội dung đa hình phía ngoài)
Route::get('/{domain}/{slug}', [Frontend\SlugController::class, 'showByDomain'])->name('content.show');
