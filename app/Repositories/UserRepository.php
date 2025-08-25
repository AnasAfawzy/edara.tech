<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    /**
     * The model class that the repository will use.
     *
     * @var string
     */
    protected $modelClass = User::class;

    public function __construct()
    {
        parent::__construct($this->modelClass);
    }

    // Additional methods specific to UserRepository can be added here
}
