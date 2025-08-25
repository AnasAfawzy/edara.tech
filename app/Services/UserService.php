<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService extends BaseService
{
    /**
     * The repository instance that the service will use.
     *
     * @var \App\Repositories\UserRepository
     */
    protected $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
        parent::__construct($this->repository);
    }

    // Additional methods specific to UserService can be added here
}
