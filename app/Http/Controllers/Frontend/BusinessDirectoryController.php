<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BusinessDirectoryController extends Controller
{
    /** @var array<string, string> */
    private const BUSINESS_SIZES = [
        'small' => 'Quy mô nhỏ',
        'medium' => 'Quy mô vừa',
        'large' => 'Quy mô lớn',
    ];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'industry' => ['nullable', 'string', 'max:150'],
            'size' => ['nullable', 'in:'.implode(',', array_keys(self::BUSINESS_SIZES))],
            'chapter' => ['nullable', 'string', 'max:150'],
        ]);

        $industries = Schema::hasTable('industries')
            ? DB::table('industries')->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['name', 'slug'])
            : collect();
        $chapters = Schema::hasTable('business_chapters')
            ? DB::table('business_chapters')->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['name', 'slug'])
            : collect();

        return view('frontend.directory.index', [
            'businesses' => $this->businesses($filters),
            'industries' => $industries,
            'chapters' => $chapters,
            'sizeOptions' => self::BUSINESS_SIZES,
            'filters' => $filters,
        ]);
    }

    /** @param array<string, mixed> $filters */
    private function businesses(array $filters): LengthAwarePaginator
    {
        if (! Schema::hasTable('businesses')) {
            return new LengthAwarePaginator([], 0, 12);
        }

        $query = DB::table('businesses')
            ->leftJoin('business_categories', 'businesses.business_category_id', '=', 'business_categories.id')
            ->leftJoin('business_chapters', 'businesses.business_chapter_id', '=', 'business_chapters.id')
            ->leftJoin('media as business_logos', function ($join): void {
                $join->on('business_logos.model_id', '=', 'businesses.id')
                    ->where('business_logos.model_type', '=', 'App\\Models\\Business')
                    ->where('business_logos.collection_name', '=', 'logo');
            })
            ->where('businesses.status', 'approved')
            ->select([
                'businesses.id',
                'businesses.name',
                'businesses.slug',
                'businesses.summary',
                'businesses.phone',
                'businesses.email',
                'businesses.website',
                'businesses.province',
                'businesses.district',
                'businesses.business_size',
                'business_categories.name as category_name',
                'business_chapters.name as chapter_name',
                'business_logos.id as logo_media_id',
                'business_logos.file_name as logo_file_name',
                'business_logos.disk as logo_disk',
            ]);

        $this->applyFilters($query, $filters);

        $paginator = $query
            ->orderBy('businesses.name')
            ->paginate(12)
            ->withQueryString();

        $paginator->getCollection()->transform(function (object $business): object {
            $business->logo_url = filled($business->logo_media_id) && filled($business->logo_file_name)
                ? Storage::disk(filled($business->logo_disk) ? (string) $business->logo_disk : 'public_media')
                    ->url($business->logo_media_id.'/'.$business->logo_file_name)
                : null;

            return $business;
        });

        return $paginator;
    }

    /** @param array<string, mixed> $filters */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (filled($filters['q'] ?? null)) {
            $keyword = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim((string) $filters['q'])).'%';

            $query->where(function (Builder $search) use ($keyword): void {
                $search->where('businesses.name', 'like', $keyword)
                    ->orWhere('businesses.legal_name', 'like', $keyword)
                    ->orWhere('businesses.summary', 'like', $keyword)
                    ->orWhereExists(function (Builder $industries) use ($keyword): void {
                        $industries->selectRaw('1')
                            ->from('business_industries')
                            ->join('industries', 'business_industries.industry_id', '=', 'industries.id')
                            ->whereColumn('business_industries.business_id', 'businesses.id')
                            ->where('industries.name', 'like', $keyword);
                    });
            });
        }

        if (filled($filters['industry'] ?? null)) {
            $query->whereExists(function (Builder $industries) use ($filters): void {
                $industries->selectRaw('1')
                    ->from('business_industries')
                    ->join('industries', 'business_industries.industry_id', '=', 'industries.id')
                    ->whereColumn('business_industries.business_id', 'businesses.id')
                    ->where('industries.slug', $filters['industry']);
            });
        }

        if (filled($filters['size'] ?? null)) {
            $query->where('businesses.business_size', $filters['size']);
        }

        if (filled($filters['chapter'] ?? null)) {
            $query->where('business_chapters.slug', $filters['chapter']);
        }
    }
}
