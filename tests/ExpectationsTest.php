<?php

namespace Amerald\LaravelValidationTestkit\Tests;

use Amerald\LaravelValidationTestkit\Expectations;

class ExpectationsTest extends TestCase
{
    /**
     * @dataProvider input
     * @param  array  $input
     */
    public function testShouldRemoveFieldFromInput(array $input)
    {
        $expectations = new Expectations($input);

        $expectations->without('name');
        $this->assertArrayNotHasKey('name', $expectations->input());

        $expectations->without('addresses.*.country');
        $this->assertEmpty($expectations->input()['addresses'][0]);
    }

    /**
     * @dataProvider input
     * @param  array  $input
     */
    public function testShouldUpdateFieldValue(array $input)
    {
        $expectations = new Expectations($input);

        $expectations->with('name', 'newValue');
        $this->assertEquals('newValue', $expectations->input()['name']);

        $expectations->with('addresses.*.country', 'newValue');
        $this->assertEquals('newValue', $expectations->input()['addresses'][0]['country']);
    }

    /**
     * @dataProvider input
     * @param  array  $input
     */
    public function testShouldUpdateFieldValueUsingClosure(array $input)
    {
        $expectations = new Expectations($input);
        $expectations->with('name', function (Expectations $expectations) {
            return 'newValue';
        });

        $this->assertEquals('newValue', $expectations->input()['name']);
    }

    /**
     * @dataProvider input
     * @param  array  $input
     */
    public function testShouldUpdateFieldValueUsingNotPrefix(array $input)
    {
        $expectations = new Expectations($input);

        $expectations->with('name', 'not:string');
        $this->assertFalse(is_string($expectations->input()['name']));

        $expectations->with('name', 'not:array');
        $this->assertFalse(is_array($expectations->input()['name']));
    }

    /**
     * @dataProvider input
     * @param  array  $input
     */
    public function testShouldBuildExpectationName(array $input)
    {
        $expectations = new Expectations($input);

        $dataSet = $expectations->without('name')->shouldFail();
        $this->assertEquals("Request without 'name' field should fail", array_keys($dataSet)[0]);

        $dataSet = $expectations->with('name', 'John Doe')->shouldPass();
        $this->assertEquals(
            "Request with 'name' field equal to 'John Doe' should pass",
            array_keys($dataSet)[0]
        );

        $dataSet = $expectations->with('name', 'not:string')->shouldFail();
        $this->assertEquals(
            "Request with 'name' field equal to '[\"test-array\"]' should fail",
            array_keys($dataSet)[0]
        );
    }

    /**
     * @dataProvider input
     * @param  array  $input
     */
    public function testShouldSetAnExpectationToPass(array $input)
    {
        $expectations = new Expectations($input);

        $dataSet = $expectations->with('name', 'John Doe')->shouldPass();
        $this->assertTrue(array_values($dataSet)[0]->shouldPass());
    }

    /**
     * @dataProvider input
     * @param  array  $input
     */
    public function testShouldSetAnExpectationToFail(array $input)
    {
        $expectations = new Expectations($input);

        $dataSet = $expectations->without('name')->shouldFail();
        $this->assertFalse(array_values($dataSet)[0]->shouldPass());
    }

    /**
     * @dataProvider input
     * @param  array  $input
     */
    public function testShouldNotMutateOriginalInput(array $input)
    {
        $expectations = new Expectations($input);
        $expectations->without('name');

        $this->assertEquals($input, $expectations->originalInput());
    }

    public function input()
    {
        return [
            [
                [
                    'name' => 'John Doe',
                    'addresses' => [
                        [
                            'country' => 'Ukraine',
                        ],
                    ],
                ],
            ],
        ];
    }
}
