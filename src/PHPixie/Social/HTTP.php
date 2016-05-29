<?php

namespace PHPixie\Social;

class HTTP
{
    public function call($method, $url, $query = array(), $data = null, $headers = array())
    {
        $handle = curl_init();
        if(!empty($query)){
            $url = $this->buildUrl($url, $query);
        }

        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
        );

        foreach($headers as $key => $value) {
            $headers[$key] = $key.': '.$value;
        }

        $options[CURLOPT_HTTPHEADER] = $headers;

        if($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $data;
        }

        curl_setopt_array($handle, $options);

        $result = curl_exec($handle);

        if($result === false) {
            $error = curl_error($handle);
            curl_close($handle);
            throw new \PHPixie\Social\Exception($error);
        }

        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if($httpCode > 299) {
            throw new \PHPixie\Social\Exception("API call resulted in error: $result");
        }

        curl_close($handle);
        return $result;
    }

    public function buildUrl($base, $query = array())
    {
        return $base . '?' . http_build_query($query);
    }
}
