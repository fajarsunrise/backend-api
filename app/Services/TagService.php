<?php

namespace App\Services;

use App\Models\Tag;

class TagService
{
    public function getAllTags()
    {
        return Tag::all();
    }

    public function createTag(array $data)
    {
        return Tag::create($data);
    }

    public function updateTag(Tag $tag, array $data)
    {
        $tag->update($data);
        return $tag;
    }

    public function deleteTag(Tag $tag)
    {
        $tag->delete();
        return true;
    }
}
