<?php

abstract class ExperienceCaptureHandler
{
    public $identifier;
    public $ordinal;
    public $question;

    abstract protected function process();

    public function __construct()
    {

    }
}