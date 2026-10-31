<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Services\TagService;
use App\Http\Resources\TagResource;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;

class TagController extends Controller
{
    protected $tagService;

    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    public function index()
    {
        return TagResource::collection($this->tagService->getAllTags());
    }

    public function store(StoreTagRequest $request)
    {
        $tag = $this->tagService->createTag($request->validated());
        return response()->json([
            'message' => 'Tag berhasil dibuat',
            'data' => new TagResource($tag)
        ], 201);
    }

    public function show(Tag $tag)
    {
        return new TagResource($tag);
    }

    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $updatedTag = $this->tagService->updateTag($tag, $request->validated());
        return response()->json([
            'message' => 'Tag berhasil diperbarui',
            'data' => new TagResource($updatedTag)
        ]);
    }

    public function destroy(Tag $tag)
    {
        $this->tagService->deleteTag($tag);
        return response()->json([
            'message' => 'Tag berhasil dihapus'
        ]);
    }
}
