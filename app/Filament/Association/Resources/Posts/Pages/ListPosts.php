<?php

namespace App\Filament\Association\Resources\Posts\Pages;

use App\Filament\Association\Resources\Posts\PostResource;

class ListPosts extends \App\Filament\Resources\Posts\Pages\ListPosts
{
    protected static string $resource = PostResource::class;
}
