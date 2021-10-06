<?php
namespace amoCrm\actions\companies;

final class CompaniesActions extends \amoCrm\actions\ObjectAction
{
    protected function getEmbeddedSelector(): string
    {
       return 'companies';
    }
}
