<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Event;
use App\Models\Post;
use App\Models\PostCategory;
use App\Support\ArticleContent;
use App\Support\SchemaMarkup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends FrontendController
{
    public function index(?Request $request = null, ?string $category = null): View
    {
        $filters = ($request ?? request())->validate(['q' => ['nullable', 'string', 'max:100']]);
        $search = trim($filters['q'] ?? '');
        $categorySlug = trim((string) ($category ?? ''));
        $currentCategory = $categorySlug === '' ? null : PostCategory::query()
            ->where('is_active', true)
            ->whereHas('slugs', fn (Builder $query) => $query->where('slug', $categorySlug)->where('locale', app()->getLocale()))
            ->firstOrFail();

        $newsItems = $this->publishedPosts()
            ->when($currentCategory, fn (Builder $query) => $query->where('post_category_id', $currentCategory->id))
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $posts) => $posts
                ->where('name->'.app()->getLocale(), 'like', '%'.$search.'%')
                ->orWhere('summary->'.app()->getLocale(), 'like', '%'.$search.'%')))
            ->orderByRaw('COALESCE(published_at, created_at) DESC')->orderByDesc('id')
            ->with(['category.slugs', 'image', 'slugs', 'media'])
            ->paginate(10)->withQueryString()
            ->through(fn (Post $post) => $this->presentPost($post));

        $pageTitle = $currentCategory?->getTranslation('name', app()->getLocale()) ?: 'Tin tức & hoạt động';
        $pageLead = $currentCategory?->getTranslation('description', app()->getLocale()) ?: 'Thông tin từ Hội, hoạt động chi hội và câu chuyện của doanh nghiệp hội viên.';
        $breadcrumbs = [
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Tin tức', 'url' => $currentCategory ? route('news.index') : null],
        ];
        if ($currentCategory) {
            $breadcrumbs[] = ['label' => $pageTitle];
        }

        return view('frontend.posts.index', array_merge($this->sidebarData(), compact('newsItems', 'search', 'currentCategory', 'pageTitle', 'pageLead', 'breadcrumbs')));
    }

    public function show(string $slug): View
    {
        $articleModel = Post::query()
            ->with(['slugs', 'category.slugs', 'image', 'media'])
            ->where('is_active', true)
            ->visibleOnSite()
            ->whereHas('slugs', fn (Builder $query) => $query->where('slug', $slug)->where('locale', app()->getLocale()))
            ->first();

        abort_if(! $articleModel, 404);

        $relatedNews = $this->publishedPosts()
            ->whereKeyNot($articleModel->id)
            ->with(['slugs', 'category.slugs', 'image', 'media'])
            ->latest('published_at')
            ->take(5)
            ->get()
            ->map(fn (Post $post) => $this->presentPost($post));

        $article = $this->presentPost($articleModel);
        $preparedContent = ArticleContent::prepare($article['content']);
        $article['content'] = $preparedContent['content'];
        $article['toc'] = $preparedContent['toc'];
        $breadcrumbs = array_values(array_filter([
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Tin tức', 'url' => route('news.index')],
            $article['category_url'] ? ['label' => $article['category_name'], 'url' => $article['category_url']] : null,
            ['label' => $article['title']],
        ]));

        return view('frontend.posts.detail', [
            'article' => $article,
            'breadcrumbs' => $breadcrumbs,
            'relatedNews' => $relatedNews,
            'commentable' => $articleModel,
            'comments' => $articleModel->comments()->with('replies')->get(),
            'articleSchema' => SchemaMarkup::article([
                'headline' => $article['title'],
                'description' => $article['seo_description'] ?: $article['excerpt'],
                'url' => $article['url'],
                'image' => [$article['image']],
                'datePublished' => $article['published_at'],
                'dateModified' => $article['modified_at'],
                'publisher' => config('app.name', 'THT Media VN'),
            ]),
        ]);
    }

    private function presentPost(Post $post): array
    {
        $domain = $post->category?->slug ?: 'tin-tuc';
        $slug = $post->slug ?: 'bai-viet-'.$post->id;
        $image = $post->image?->url ?: $post->getFirstMediaUrl('post_image');

        return [
            'domain' => $domain,
            'url' => route('content.show', compact('domain', 'slug')),
            'category_name' => $post->category?->getTranslation('name', 'vi'),
            'category_slug' => $post->category?->slug,
            'category_url' => $post->category?->is_active && $post->category?->slug ? route('news.show', $post->category->slug) : null,
            'slug' => $slug,
            'title' => $post->getTranslation('name', 'vi'),
            'date' => ($post->published_at ?: $post->created_at)->format('d.m.Y'),
            'image' => $image ?: asset('assets/images/no-image.svg'),
            'has_image' => filled($image),
            'excerpt' => $post->getTranslation('summary', 'vi'),
            'content' => $post->getTranslation('content', 'vi'),
            'seo_title' => $post->getTranslation('seo_title', 'vi'),
            'seo_description' => $post->getTranslation('seo_description', 'vi'),
            'seo_keywords' => $post->getTranslation('seo_keywords', 'vi'),
            'published_at' => ($post->published_at ?: $post->created_at)?->toIso8601String(),
            'modified_at' => $post->updated_at?->toIso8601String(),
        ];
    }

    private function publishedPosts(): Builder
    {
        return Post::query()->visibleOnSite()
            ->whereHas('slugs', fn (Builder $query) => $query->where('locale', app()->getLocale()));
    }

    private function sidebarData(): array
    {
        $categories = PostCategory::query()->where('is_active', true)
            ->whereHas('slugs', fn (Builder $query) => $query->where('locale', app()->getLocale()))
            ->with('slugs')
            ->withCount(['posts' => fn (Builder $query) => $query->visibleOnSite()
                ->whereHas('slugs', fn (Builder $slugs) => $slugs->where('locale', app()->getLocale()))])
            ->orderBy('sort_order')->orderBy('id')->get();

        return [
            'categories' => $categories,
            'totalNews' => $this->publishedPosts()->count(),
            'upcomingEvents' => Event::query()->publiclyVisible()->ongoingOrUpcoming()->orderBy('starts_at')->limit(3)->get(),
        ];
    }
}
