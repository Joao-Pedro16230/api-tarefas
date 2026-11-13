<?php

namespace App\Http\Services;

use App\Http\Repositories\TaskRepository;
use App\Models\Task;

class TaskService extends BaseService
{
    public function __construct(private TaskRepository $taskRepository)
    {
        parent::__construct($taskRepository);
    }

    public function getWithFilters(array $filters = [])
    {
        return $this->taskRepository->getWithFilters($filters);
    }

    public function storeWithTags(array $data): Task
    {
        $tagIds = $data['tags'] ?? [];

        unset($data['tags']);

        $task = $this->taskRepository->store($data);

        $task->tags()->sync($tagIds);

        return $task->load(['category', 'tags']);
    }

    public function updateWithTags(array $data, string $id): Task
    {
        $hasTags = array_key_exists('tags', $data);
        $tagIds = $data['tags'] ?? [];

        unset($data['tags']);

        $task = $this->taskRepository->update($data, $id);

        if ($hasTags) {
            $task->tags()->sync($tagIds);
        }

        return $task->load(['category', 'tags']);
    }
}
