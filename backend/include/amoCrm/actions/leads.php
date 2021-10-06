<?php
namespace amoCrm\actions\leads;

final class LeadsActions extends \amoCrm\actions\ObjectAction
{
    public function getObjects(array $filters = null, $onEachPageAction = null,  $isUnsorted = false): array
    {
        $baseUrl = "/api/v4/leads" . ($isUnsorted ? '/unsorted' : '') . ($filters ? ('?' . parent::getFilterString($filters)) : '');
        return parent::getFullData($baseUrl, $onEachPageAction);
    }

    protected function getEmbeddedSelector(): string
    {
        return 'leads';
    }
}
