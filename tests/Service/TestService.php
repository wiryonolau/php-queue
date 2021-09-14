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

    public function testThrowable(int $text) : string
    {
        echo $text;
        return $text;
    }
}
