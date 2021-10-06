<?php
namespace amoCrm\actions\contacts;

final class Contact
{
    public $id;
    public $custom_fields_values;
}

final class ContactsActions extends \amoCrm\actions\ObjectAction
{
    protected function getEmbeddedSelector(): string
    {
        return 'contacts';
    }
}
