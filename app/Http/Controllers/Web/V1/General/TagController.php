<?php

namespace App\Http\Controllers\Web\V1\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\General\TagRequest;
use App\Http\Resources\DashUser\TagResource;
use App\Models\Tag;
use App\Services\DashUser\TagService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TagController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_tags', only: ['index']),
            new Middleware('permission:view_tag_by_id', only: ['show']),
            new Middleware('permission:create_tag', only: ['store']),
            new Middleware('permission:update_tag', only: ['update']),
            new Middleware('permission:delete_tag', only: ['destroy']),

        ];
    }

    public function __construct(private TagService $tagService) {}

    public function index(Request $request)
    {
        $tags = $this->tagService->list($request);
        return response()->format($this->returnPaginatedResponse($tags, TagResource::collection($tags)), 'messages.success', 200);
    }

    public function store(TagRequest $request)
    {
        $tag = $this->tagService->create($request->validated());
        return response()->format(new TagResource($tag),  __('messages.created_successfully',  ['item' => __('constants.tag')]), 201);
    }

    public function update(TagRequest $request, Tag $tag)
    {
        $tag = $this->tagService->update($tag, $request->validated());
        return response()->format(new TagResource($tag),  __('messages.updated_successfully',  ['item' => __('constants.tag')]), 200);
    }
    public function show(Tag $tag)
    {
        $tag = $this->tagService->show($tag);
        return response()->format(new TagResource($tag), 'messages.success', 200);
    }
    public function destroy(Tag $tag)
    {
        $returned = $this->tagService->delete($tag);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.tag')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.tag')]), 200);
    }

    public function selectAvailable()
    {
        $tags = $this->tagService->selectAvailable();

        $returnedData = $tags->map(fn($tag) => [
            'key' => $tag?->id,
            'value' => $tag?->name
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
