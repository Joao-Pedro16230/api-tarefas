<?php

namespace App\Http\Repositories;

use App\Models\Task;

class TaskRepository extends BaseRepository
{
    public function __construct(private Task $task)
    {
        parent::__construct($task);
    }

    public function getWithFilters(array $filters = [])
    {
        $query = $this->task->newQuery()
            ->with(['category', 'tags']);

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $this->index($query);
    }
}
