<?php
namespace amoCrm\actions\tags;

final class TagsActions extends \amoCrm\actions\ObjectAction
{
    public function update(array $data, string $forType = null): array
    {
        return parent::getResponse("/api/v4/${forType}", 'PATCH', $data)['_embedded'][$this->getEmbeddedSelector()];
    }

    protected function getEmbeddedSelector(): string
    {
        return 'tags';
    }
}
