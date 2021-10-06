<?php
namespace amoCrm\actions\users;

final class UsersActions extends \amoCrm\actions\ObjectAction
{
    protected function getEmbeddedSelector(): string
    {
        return 'users';
    }
}
