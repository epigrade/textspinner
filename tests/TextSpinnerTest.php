<?php

namespace Epigrade\TextSpinner\Test;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error\Error;
use Epigrade\TextSpinner\TextSpinner;
use InvalidArgumentException;

class TextSpinnerTest extends TestCase
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
            ['Hello}, {Joe|Jane}!'],
            ['Hello}, {|mister |mr. }{Joe|Mike}! You are ~age~ years {old|of age}.'],
            ['Hello}, {mister |mr. |}{Joe|Mike}! You are ~age~ years {old|of age}.'],
        ];
    }

    public function invalidPlaceholders()
    {
        return [
            [123],
            ['hello'],
        ];
    }

    public function invalidSyntaxMarkers()
    {
        return [
            [123],
            ['hello'],
            [['hello']],
            [['open' => '[']],
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

    /**
     * @dataProvider validSpintax
     */
    public function testInvalidPlaceholdersReturn($spintax)
    {
        $spinner = new TextSpinner($spintax, ['name' => 'Andy']);
        $invalidPlaceholders = $spinner->invalidPlaceholders();
        $this->assertEquals(['age'], $invalidPlaceholders);
    }

    public function testSpinValidSpintaxWithoutPlaceholders()
    {
        $spintax = '{this|that{| one}} jumps';
        $outputs = [
            'this jumps',
            'that jumps',
            'that one jumps',
        ];
        $spinner = new TextSpinner($spintax);
        $output = $spinner->spin();
        $this->assertContains($output, $outputs);
    }

    public function testSpinValidSpintaxWithPlaceholders()
    {
        $spintax = '{this|that{| one}} ~verb~';
        $outputs = [
            'this jumps',
            'that jumps',
            'that one jumps',
        ];
        $spinner = new TextSpinner($spintax, ['verb' => 'jumps']);
        $output = $spinner->spin();
        $this->assertContains($output, $outputs);
    }

    public function testConstructorNoArguments()
    {
        $spinner = new TextSpinner();
        $spintax = $spinner->getSpintax();
        $placeholders = $spinner->getPlaceholders();
        $this->assertEquals([$spintax, $placeholders], [null, []]);
    }

    public function testConstructorSpintaxArgument()
    {
        $spintaxArg = 'hello {there|world}';
        $spinner = new TextSpinner($spintaxArg);
        $spintax = $spinner->getSpintax();
        $placeholders = $spinner->getPlaceholders();
        $this->assertEquals([$spintax, $placeholders], [$spintaxArg, []]);
    }

    public function testConstructorSpintaxPlaceholdersArguments()
    {
        $spintaxArg = 'hello {there|world}';
        $placeholdersArg = ['name' => 'Joe'];
        $spinner = new TextSpinner($spintaxArg, $placeholdersArg);
        $spintax = $spinner->getSpintax();
        $placeholders = $spinner->getPlaceholders();
        $this->assertEquals([$spintax, $placeholders], [$spintaxArg, $placeholdersArg]);
    }

    public function testConstructorSpintaxPlaceholdersSyntaxMarkersArguments()
    {
        $spintaxArg = 'hello {there|world}';
        $placeholdersArg = ['name' => 'Joe'];
        $syntaxMarkersArg = [
            'open' => '[',
            'close' => ']',
            'separator' => '#',
            'placeholder' => '$',
        ];
        $spinner = new TextSpinner($spintaxArg, $placeholdersArg, $syntaxMarkersArg);
        $spintax = $spinner->getSpintax();
        $placeholders = $spinner->getPlaceholders();
        $syntaxMarkers = $spinner->getSyntaxMarkers();
        $this->assertEquals([$spintax, $placeholders, $syntaxMarkers], [$spintaxArg, $placeholdersArg, $syntaxMarkersArg]);
    }

    public function testSetValidSpintax()
    {
        $newSpintaxArg = 'howdy {there|world}';
        $spinner = new TextSpinner();
        $spinner->setSpintax($newSpintaxArg);
        $spintax = $spinner->getSpintax();
        $this->assertEquals($spintax, $newSpintaxArg);
    }

    public function testSetInvalidSpintax()
    {
        $this->expectException(InvalidArgumentException::class);
        $newSpintaxArg = [];
        $spinner = new TextSpinner();
        $spinner->setSpintax($newSpintaxArg);
    }

    public function testSetValidPlaceholders()
    {
        $placeholdersArg = ['name' => 'Joe'];
        $newPlaceholdersArg = ['age' => '12'];
        $spinner = new TextSpinner('', $placeholdersArg);
        $spinner->setPlaceholders($newPlaceholdersArg);
        $placeholders = $spinner->getPlaceholders();
        $this->assertEquals($placeholders, $newPlaceholdersArg);
    }

    /**
     * @dataProvider invalidPlaceholders
     */
    public function testSetInvalidPlaceholders($placeholders)
    {
        $this->expectException(InvalidArgumentException::class);
        $spinner = new TextSpinner();
        $spinner->setPlaceholders($placeholders);
    }

    public function testSetValidSyntaxMarkers()
    {;
        $newSyntaxMarkersArg = [
            'open' => '[',
            'close' => ']',
            'separator' => '#',
            'placeholder' => '$',
        ];
        $spinner = new TextSpinner();
        $spinner->setSyntaxMarkers($newSyntaxMarkersArg);
        $syntaxMarkers = $spinner->getSyntaxMarkers();
        $this->assertEquals($syntaxMarkers, $newSyntaxMarkersArg);
    }

    /**
     * @dataProvider invalidSyntaxMarkers
     */
    public function testSetInvalidSyntaxMarkers($syntaxMarkers)
    {
        $this->expectException(InvalidArgumentException::class);
        $spinner = new TextSpinner();
        $spinner->setSyntaxMarkers($syntaxMarkers);
    }
}
