<?php
namespace amoCrm\actions\account;

final class AccountActions extends \amoCrm\actions\ObjectAction
{
    public function getObjects(array $filters = null, $onEachPageAction = null): array
    {
        return parent::getResponse("/api/v4/account", 'GET', null);
    }

    protected function getEmbeddedSelector(): string
    {
        return 'account';
    }
}
