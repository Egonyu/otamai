<?php

namespace Modules\Ebook\Filters;

class QueryStringFilter
{
    private $sorts = [
        'relevance',
        'alphabetic',
        'toprated',
        'latest',
    ];

    public function sort($query, $sortType)
    {
        if ($this->sortTypeExists($sortType)) {
            return $this->{$sortType}($query);
        }
    }

    private function sortTypeExists($sortType)
    {
        return in_array(strtolower($sortType), $this->sorts);
    }

    public function relevance()
    {
        // Ebook are searched by relevant order by default.
    }

    public function alphabetic($query)
    {
        $query->join('ebook_translations', 'ebooks.id', '=', 'ebook_translations.ebook_id')
            ->orderBy('ebook_translations.title');
    }

    public function topRated($query)
    {
         $query->leftJoin('reviews', 'ebooks.id', '=', 'reviews.ebook_id')
            ->selectRaw('AVG(reviews.rating) as avg_rating')
            ->groupBy([
                'ebooks.id',
                'slug',
                'user_id',
                'file_type',
                'file_url',
                'embed_code',
                'isbn',
                'price',
                'buy_url',
                'publication_year',
                'viewed',
                'password',
                'is_featured',
                'is_active',
                'is_private',
                'user_id',
                'ebooks.created_at',
            ])
            ->orderByDesc('avg_rating'); 
    }

    public function latest($query)
    {
        $query->latest();
    }
    
    public function category($query, $slug)
    {
        $query->whereHas('categories', function ($categoryQuery) use ($slug) {
            $categoryQuery->where('slug', $slug);
        });
    }

}
