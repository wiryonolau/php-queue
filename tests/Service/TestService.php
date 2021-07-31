<?php

namespace Itseasy\Queue\Test\Service;

class TestService 
{
    public function test(string $text) : string
    {
        echo $text;
        return $text;
    }
}
