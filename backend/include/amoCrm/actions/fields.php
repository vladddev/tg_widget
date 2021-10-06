<?php
namespace amoCrm\actions\fields;

class NewField
{
    public $code;
    public $type;
    public $name;
    public $is_api_only;

    public static function CreateSimpleCustomField($fieldId, $value, string $fieldCode = null)
    {
        $rv = [
            'values' => [
                [
                    'value' => $value
                ]
            ]
        ];
        if ($fieldId) {
            $rv['field_id'] = intval($fieldId);
        }

        if ($fieldCode) {
            $rv['field_code'] = $fieldCode;
        }

        return $rv;
    }
}

final class FieldsActions extends \amoCrm\actions\ObjectAction
{
    public function getObjects(array $filters = null, $onEachPageAction = null, string $objectName = 'leads'): array
    {
        $baseUrl = "/api/v4/${objectName}/custom_fields" . ($filters ? ('?' . parent::getFilterString($filters)) : '');
        return parent::getFullData($baseUrl, $onEachPageAction);
    }

    public function create(array $objectTypes, array $newFields = []): array
    {
        $rv = [];
        foreach ($objectTypes as $objectType) {
            array_merge($rv, parent::getResponse("/api/v4/${objectType}/custom_fields", 'POST', $newFields)['_embedded'][$this->getEmbeddedSelector()]);
        }
        return $rv;
    }

    protected function getEmbeddedSelector(): string
    {
        return 'custom_fields';
    }
}
