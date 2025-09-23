<?php

namespace Models\User;

class User
{

    function login($username, $password): ?UserDTO
    {
        return new UserDTO($username, $password);
    }
}