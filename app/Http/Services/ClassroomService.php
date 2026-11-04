<?php

namespace App\Http\Services;

use App\Http\Repositories\ClassroomRepository;

class ClassroomService extends BaseService
{
    public function __construct(private ClassroomRepository $classroomRepository)
    {
        parent::__construct($classroomRepository);
    }

    public function getWithFilters(array $data)
    {
        return $this->classroomRepository->getWithFilters($data);
    }

    public function addUser(string $classroomId, int $userId)
    {
        return $this->classroomRepository->addUser($classroomId, $userId);
    }

    public function removeUser(string $classroomId, int $userId)
    {
        return $this->classroomRepository->removeUser($classroomId, $userId);
    }
}
