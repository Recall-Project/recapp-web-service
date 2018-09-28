<?php

class XprHTTPResponse extends Base
{
    public $code;
    public $message;

    public function __construct()
    {
        parent::__construct('xprhttpresponse');
    }
}