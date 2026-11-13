<?php

namespace App\Http\Services;

use App\Http\Repositories\CategoryRepository;

class CategoryService extends BaseService
{
    public function __construct(private CategoryRepository $categoryRepository)
    {
        parent::__construct($categoryRepository);
    }

    public function getWithFilters(array $filters = [])
    {
        return $this->categoryRepository->getWithFilters($filters);
    }
}
