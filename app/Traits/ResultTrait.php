<?php

namespace App\Traits;


trait ResultTrait
{
    public function returnPaginatedResponse($data, $resourceCollection, $extra = [] )
    {
        $pagination = [
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'per_page' => $data->perPage(),
            'total' => $data->total(),
            'has_more_pages' => $data->hasMorePages(),
        ];

        return array_merge($extra, [
            'items' => $resourceCollection,
            'pagination' => $pagination,
        ]);
    }
}
