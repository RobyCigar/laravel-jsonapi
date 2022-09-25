<?php

namespace App\JsonApi\V1\Posts;

 use App\Models\Post;
 use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Http\Request;
 use LaravelJsonApi\Eloquent\Contracts\Paginator;
 use LaravelJsonApi\Eloquent\Fields\DateTime;
 use LaravelJsonApi\Eloquent\Fields\ID;
 use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
 use LaravelJsonApi\Eloquent\Fields\Relations\BelongsToMany;
 use LaravelJsonApi\Eloquent\Fields\Relations\HasMany;
 use LaravelJsonApi\Eloquent\Fields\Str;
 use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
 use LaravelJsonApi\Eloquent\Pagination\PagePagination;
 use LaravelJsonApi\Eloquent\Schema;
 use LaravelJsonApi\Eloquent\Filters\WhereIn;

class PostSchema extends Schema
{

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Post::class;

    /**
     * The maximum include path depth.
     *
     * @var int
     */
    protected int $maxDepth = 3;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make(),
            Str::make('slug'),
            Str::make('content'),
            Str::make('title')->sortable(),
            DateTime::make('publishedAt')->sortable(),
            DateTime::make('createdAt')->sortable()->readOnly(),
            DateTime::make('updatedAt')->sortable()->readOnly(),
            BelongsTo::make('author')->type('users')->readOnly(),
            HasMany::make('comments')->readOnly(),
            BelongsToMany::make('tags'),
        ];
    }

    /**
     * Get the resource filters.
     *
     * @return array
     */
    public function filters(): array
    {
        return [
            WhereIdIn::make($this),
            WhereIn::make('author', 'author_id')
        ];
    }

    /**
     * Get the resource paginator.
     *
     * @return Paginator|null
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make();
    }

    /**
     * Build an index query for this resource.
     *
     * @param Request|null $request
     * @param Builder $query
     * @return Builder
     */
    public function indexQuery(?Request $request, Builder $query): Builder
    {
        if ($user = optional($request)->user()) {
            return $query->where(function (Builder $q) use ($user) {
                return $q->whereNotNull('published_at')->orWhere('author_id', $user->getKey());
            });
        }

        return $query->whereNotNull('published_at');
    }

}
