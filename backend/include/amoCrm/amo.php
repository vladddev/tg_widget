<?php
namespace amoCrm;

final class AmoCrmWebClient extends \web\WebClient
{
    public function getResponse($link, $method, $data, $headers = ['Content-Type:application/json'], $isDecode = true)
    {
        $response = parent::getResponse("amoCRM-oAuth-client/1.0", $link, $method, json_encode($data), $headers);
        return $isDecode ? json_decode($response, true) : $response;
    }

    protected function getIsError($code)
    {
        return $code < 200 || $code > 204;
    }
}

