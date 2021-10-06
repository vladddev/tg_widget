<?php
namespace amoCrm\actions\filters;

abstract class QueryFilter
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    protected function getName(): string
    {
        return $this->name;
    }

    public abstract function getQueryString(): string;
}

final class SimpleQueryFilter extends QueryFilter
{
    private $value;

    public function __construct(string $name, string $value)
    {
        parent::__construct($name);
        $this->value = $value;
    }

    public function getQueryString(): string
    {
        return "filter[" . parent::getName() . "][0]=" . $this->value;
    }
}

final class QueryManyFilter extends QueryFilter
{
    private $data;

    public function __construct(string $name, array $data)
    {
        parent::__construct($name);
        $this->data = $data;
    }

    public function getQueryString(): string
    {
        $queryString = '';

        foreach ($this->data as $index => $value) {
            if (empty($value)) {
                continue;
            }
            $queryString = $queryString . "filter[" . parent::getName() . "][${index}]=${value}&";
        }

        return mb_substr($queryString, 0, -1);
    }
}

final class BetweenFilter extends QueryFilter
{
    private $from;
    private $to;

    public function __construct(string $name, $from, $to)
    {
        parent::__construct($name);
        $this->from = $from;
        $this->to = $to;
    }

    public function getQueryString(): string
    {
        return "filter[". parent::getName() ."][from]=" . $this->from . "&filter[". parent::getName() ."][to]=" . $this->to;
    }
}

final class CustomFieldFilter extends QueryFilter
{
    private $values;

    public function __construct(array $values, string $id = null, string $code = null)
    {
        parent::__construct($id ? 'field_id' : 'field_code');
        $this->values = $values;
    }

    public function getQueryString(): string
    {
        $queryString = '';

        foreach ($this->values as $index => $value) {
            if (empty($value)) {
                continue;
            }
            $queryString = $queryString . "filter[custom_fields_values][" . parent::getName() . "][${index}]=${value}&";
        }

        return mb_substr($queryString, 0, -1);
    }
}