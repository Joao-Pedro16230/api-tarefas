<?php

namespace App\Http\Services;

use App\Http\Repositories\TagRepository;

class TagService extends BaseService
{
    public function __construct(
        private TagRepository $tagRepository
    ) {
        parent::__construct($tagRepository);
    }

    public function getWithFilters(array $filters = [])
    {
        return $this->tagRepository->getWithFilters($filters);
    }
}
