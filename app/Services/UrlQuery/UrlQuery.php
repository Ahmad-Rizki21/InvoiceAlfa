<?php

namespace App\Services\UrlQuery;

use Illuminate\Http\Request;
use App\Services\UrlQuery\Search\SearchBuilder;
use App\Services\UrlQuery\Sorts\SortBuilder;
use App\Services\UrlQuery\Relation\RelationBuilder;

class UrlQuery
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Instantiate class
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get query used for filtering/searching.
     *
     * @return \App\Services\UrlQuery\Search\SearchBuilder
     */
    public function searches(): SearchBuilder
    {
        return new SearchBuilder($this->request->except([
            'limit', 'trashed', 'includes', 'excludes', 'page',
            'sorts', 'itemsPerPage', 'mustSort', 'multiSort',
            'search', 'sortDesc', 'sortBy',
        ]));
    }

    /**
     * Get query used to limit the result.
     *
     * @return int
     */
    public function limit(): int
    {
        $limit = $this->request->query('limit') ?: $this->request->query('itemsPerPage');

        if (is_numeric($limit)) {
            return (int) $limit;
        }

        return 0;
        // return (int) ($this->request->query('limit') ?: $this->request->query('itemsPerPage') ?: 9999999999999);
    }

    /**
     * Determine wether the request should also show soft deleted items.
     *
     * @return bool
     */
    public function trashed(): bool
    {
        $trashed = $this->request->query('trashed');

        return $trashed == 1 || $trashed == 'true';
    }

    /**
     * Get query used for sorting.
     *
     * @return \App\Services\UrlQuery\Sorts\SortBuilder
     */
    public function sorts(): SortBuilder
    {
        return new SortBuilder($this->request->query('sorts') ?: '');
    }

    /**
     * Include relationships to the response.
     *
     * @return \App\Services\UrlQuery\Relation\RelationBuilder
     */
    public function includes()
    {
        return new RelationBuilder($this->request->query('includes') ?: '');
    }

    /**
     * Exclude relationships to the response.
     *
     * @return \App\Services\UrlQuery\Relation\RelationBuilder
     */
    public function excludes()
    {
        return new RelationBuilder($this->request->query('excludes') ?: '');
    }

    /**
     * Get the page of paginated result.
     *
     * @return int
     */
    public function page(): int
    {
        return (int) ($this->request->query('page') ?: 1);
    }
}
