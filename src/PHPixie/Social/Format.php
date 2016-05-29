<?php

namespace PHPixie\Social;

class Format
{
    public function jsonDecode($string)
    {
        $result = json_decode($string);
        if(json_last_error() !== JSON_ERROR_NONE) {
            throw new \PHPixie\Social\Exception("Could not parse JSON data");
        }

        return $result;
    }

    public function queryDecode($string)
    {
        parse_str($string, $data);
        return (object)$data;
    }
}
