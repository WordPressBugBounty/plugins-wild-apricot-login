<?php

interface WA_Modules_Interfaces_IShortCode
{
    public function render($attributes, $content, $shortCodeName);
}