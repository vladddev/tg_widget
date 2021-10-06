<?php
namespace amoCrm\actions;
use Throwable;

class UrlLengthException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

abstract class Action
{
    private $auth;
    private $amoCrmWebClient;

    public function __construct(\amoCrm\actions\auth\AmoCrmAuth $auth, \amoCrm\AmoCrmWebClient $amoCrmWebClient)
    {
        $this->auth=$auth;
        $this->amoCrmWebClient = $amoCrmWebClient;
    }

    final protected function getResponse($link, $method, $data, $headers = [], $isDecode = true)
    {
        if (strlen($link) > 2000) {
            throw new UrlLengthException('MAX URL LENGTH ERROR');
        }

        array_push($headers, 'Content-Type:application/json', 'Authorization:' . $this->auth->getAuthString());
        return $this->amoCrmWebClient->getResponse((mb_substr($link, 0, 4) !== 'http' ? ($this->auth->getRootUrl() . $link) : $link) , $method, $data, $headers, $isDecode);
    }

    protected function getFilterString(array $filters): string
    {
        $filterString = '';

        foreach ($filters as $filter) {
            if (!isset($filter)) {
                continue;
            }
            $filterString = $filterString . $filter->getQueryString() . '&';
        }

        return mb_substr($filterString, 0, -1);
    }
}

abstract class ObjectAction extends Action
{
    public function __construct(\amoCrm\actions\auth\AmoCrmAuth $auth, \amoCrm\AmoCrmWebClient $amoCrmWebClient)
    {
        parent::__construct($auth, $amoCrmWebClient);
    }

    final protected function getFullData(string $baseUrl, $onEachPageAction = null): array
    {
        $url = $baseUrl . (strpos($baseUrl, '?') === false ? '?' : '&') . "limit=" . MAX_ONE_QUERY_LIMIT;
        $rv = [];
        $embeddedSelector = $this->getEmbeddedSelector();
        do {
            $response = parent::getResponse($url, 'GET', null);
            if ($response != null) {
                if ($onEachPageAction != null) {
                    $onEachPageAction($response['_embedded'][$embeddedSelector]);
                } else {
                    $rv = array_merge($rv, $response['_embedded'][$embeddedSelector]);
                }
            }

            $url = isset($response['_links']['next']) ? $response['_links']['next']['href'] : null;

        } while (isset($response['_links']['next']));

        return $rv;
    }

    public function update(array $data): array
    {
        $embeddedSelector = $this->getEmbeddedSelector();
        return parent::getResponse("/api/v4/${embeddedSelector}", 'PATCH', $data)['_embedded'][$embeddedSelector];
    }

    public function create(array $data): array
    {
        $embeddedSelector = $this->getEmbeddedSelector();
        return parent::getResponse("/api/v4/${embeddedSelector}", 'POST', $data)['_embedded'][$embeddedSelector];
    }

    public function getObjects(array $filters = null, $onEachPageAction = null): array
    {
        $baseUrl = "/api/v4/" . $this->getEmbeddedSelector() . ($filters ? ('?' . parent::getFilterString($filters)) : '');
        return $this->getFullData($baseUrl, $onEachPageAction);
    }

    public function getObject(int $id): array
    {
        return parent::getResponse("/api/v4/" . $this->getEmbeddedSelector() . "/${id}", 'GET', null);
    }

    public function getObjectsByQuery(string $query, array $filters = null, $onEachPageAction = null): array
    {
        $baseUrl = "/api/v4/" . $this->getEmbeddedSelector() ."?query=${query}" . ($filters ? ('&' . parent::getFilterString($filters)) : '');
        return $this->getFullData($baseUrl, $onEachPageAction);
    }

    abstract protected function getEmbeddedSelector(): string;
}