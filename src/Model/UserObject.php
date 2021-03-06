<?php

namespace Simply\Core\Model;

use Simply\Core\Contract\ModelInterface;
use Simply\Core\Repository\UserRepository;

class UserObject implements ModelInterface {
    public $user;

    public function __construct($user) {
        $this->user = $user;
    }

    static function getRepository() {
        return \Simply::get(UserRepository::class);
    }

    static function getType() {
        return 'user';
    }
}
