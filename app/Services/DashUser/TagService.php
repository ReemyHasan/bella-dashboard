<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class TagService
{
    public function list($request)
    {
        return Tag::filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $tag = Tag::create([
                'name' => $data['name']
            ]);
            return $tag;
        });
    }

    public function update(Tag $tag, array $data)
    {
        return DB::transaction(function () use ($tag, $data) {
            $tag->update([
                'name' => $data['name']
            ]);

            return $tag;
        });
    }
    public function show(Tag $tag)
    {
        return $tag;
    }

    public function delete(Tag $tag)
    {
        return $tag->delete();
    }


    public function selectAvailable()
    {

        $tags = Tag::orderBy('id')->get([
            'id',
            'name'
        ]);

        return $tags;
    }
}
