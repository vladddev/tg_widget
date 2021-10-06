<?php
namespace amoCrm\actions\catalogs;

final class Catalog
{
    public $name;
    public $type;

    public $canAddElements;
    public $canLinkMultiple;
}

final class CatalogsActions extends \amoCrm\actions\ObjectAction
{
    protected function getEmbeddedSelector(): string
    {
        return 'catalogs';
    }
}
