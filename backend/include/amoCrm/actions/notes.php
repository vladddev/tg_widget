<?php
namespace amoCrm\actions\notes;

final class NotesActions extends \amoCrm\actions\ObjectAction
{
    public function getObjects(array $filters = null, $onEachPageAction = null, string $entityId = null, string $entityName = null): array
    {
        $baseUrl = "/api/v4/${entityName}/" .($entityId ? "${entityId}/" : '') . $this->getEmbeddedSelector() . ($filters ? ('?' . parent::getFilterString($filters)) : '');
        return parent::getFullData($baseUrl, $onEachPageAction);
    }

    public function create(array $data, string $entityId = null, string $entityName = null): array
    {
        $embeddedSelector = $this->getEmbeddedSelector();
        return parent::getResponse("/api/v4/${entityName}/${entityId}/${embeddedSelector}", 'POST', $data)['_embedded'][$embeddedSelector];
    }

    protected function getEmbeddedSelector(): string
    {
        return 'notes';
    }
}
