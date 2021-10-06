<?php
namespace web;

abstract class WebClient
{
    public function getResponse($userAgent, $link, $method, $data, $headers)
    {
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl,CURLOPT_USERAGENT, $userAgent);
        curl_setopt($curl,CURLOPT_URL, $link);

        if (sizeof($headers) > 0) {
            curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($curl,CURLOPT_HEADER, false);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST, $method);
        if(isset($data)) {
            curl_setopt($curl,CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $this->getIsError($code) ? $this->processingErrors((int)$code, $link, $data, $out) : null;
        return $out;
    }

    protected function processingErrors(int $code, $link, $data, $out) {
        throw new \Exception("Error Code = ${code}" . PHP_EOL . "Link = ${link}" . PHP_EOL . "Data = ${data}" . PHP_EOL . "Out = ${out}");
    }

    protected abstract function getIsError($code);
}
