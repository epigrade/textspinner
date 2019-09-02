<?php

namespace Epigrade\TextSpinner\Test;

use PHPUnit\Framework\TestCase;
use Epigrade\TextSpinner\TextSpinner;

class ValidateTest extends TestCase
{
    public function validSpintax()
    {
        return [
            ['Hello, {Joe|Jane|~name~}! You are ~age~ years {old|of age}.'],
            ['Hello, {|mister |mr. }{Joe|Mike}! You are ~age~ years {old|of age}.'],
            ['Hello, {mister |mr. |}{Joe|Mike}! You are ~age~ years {old|of age}.'],
        ];
    }

    public function invalidSpintax()
    {
        return [
            ['{Hello, {Joe|Jane}!'],
            ['{Hello, {|mister |mr. }{Joe|Mike}! You are ~age~ years {old|of age}.'],
            ['{Hello, {mister |mr. |}{Joe|Mike}! You are ~age~ years {old|of age}.'],
        ];
    }

    /**
     * @dataProvider validSpintax
     */
    public function testValidatorValid($spintax)
    {
        $spinner = new TextSpinner($spintax);
        $valid = $spinner->validate(false);
        $this->assertTrue($valid);
    }

    /**
     * @dataProvider invalidSpintax
     */
    public function testValidatorInvalid($spintax)
    {
        $spinner = new TextSpinner($spintax);
        $valid = $spinner->validate(false);
        $this->assertFalse($valid);
    }

    /**
     * @dataProvider validSpintax
     */
    public function testValidatorPlaceholdersValid($spintax)
    {
        $spinner = new TextSpinner($spintax, ['name' => 'Andy', 'age' => '19']);
        $valid = $spinner->validate(true);
        $this->assertTrue($valid);
    }

    /**
     * @dataProvider validSpintax
     */
    public function testValidatorPlaceholdersInvalid($spintax)
    {
        $spinner = new TextSpinner($spintax, ['name' => 'Andy']);
        $valid = $spinner->validate(true);
        $this->assertFalse($valid);
    }
}
