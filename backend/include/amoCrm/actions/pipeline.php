<?php
namespace amoCrm\actions\pipeline;

final class PipelineActions extends \amoCrm\actions\ObjectAction
{
    public function getObjects(array $filters = null, $onEachPageAction = null): array
    {
        $baseUrl = "/api/v4/leads/pipelines" . ($filters ? ('?' . parent::getFilterString($filters)) : '');
        return parent::getResponse($baseUrl, 'GET', null);
    }

    protected function getEmbeddedSelector(): string
    {
        return "pipelines";
    }

    public function getStatuses(int $pipelineId, array $filters = null, $onEachPageAction = null): array
    {
        $baseUrl = "/api/v4/leads/pipelines${pipelineId}/statuses" . ($filters ? ('?' . parent::getFilterString($filters)) : '');
        return parent::getResponse($baseUrl, 'GET', null);
    }

    public function getStatus(int $pipelineId, int $status_id, array $filters = null, $onEachPageAction = null): array
    {
        $baseUrl = "/api/v4/leads/pipelines/${pipelineId}/statuses/${status_id}" . ($filters ? ('?' . parent::getFilterString($filters)) : '');
        return parent::getResponse($baseUrl, 'GET', null);
    }
}
