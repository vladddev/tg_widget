<?php
namespace amoCrm\actions\links;

final class LinksActions extends \amoCrm\actions\ObjectAction
{
    public function getObjects(array $filters = null, $onEachPageAction = null, string $objectId = null, string $objectType = 'leads'): array
    {
        $baseUrl = "/api/v4/${objectType}/${objectId}/links" . ($filters ? ('?' . parent::getFilterString($filters)) : '');
        return parent::getFullData($baseUrl, $onEachPageAction);
    }

    public function create(array $data, string $objectId = null, string $objectType = 'leads'): array
    {
        return parent::getResponse("/api/v4/${objectType}/${objectId}/link", 'POST', $data)['_embedded'][$this->getEmbeddedSelector()];
    }

    protected function getEmbeddedSelector(): string
    {
        return 'links';
    }
}
