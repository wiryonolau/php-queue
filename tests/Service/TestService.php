<?php
declare(strict_types=1);

namespace Itseasy\Queue\Test\Service;

class TestService
{
    public function test(string $text) : string
    {
        echo $text;
        return $text;
    }

    public function testThrowable() : string
    {
        echo $text;
        return $text;
    }

    public function testNotDefinedArray() : void
    {
        foreach($not_defined_array as $key => $value) {
            echo "$key:$value";
        }
    }

    public function testNotArray() : void
    {
        $not_array = true;
        foreach($not_array as $key => $value) {
            echo "$key:$value";
        }
    }
}
