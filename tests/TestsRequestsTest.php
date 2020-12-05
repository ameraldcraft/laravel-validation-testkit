<?php

namespace Amerald\LaravelValidationTestkit\Tests;

use Amerald\LaravelValidationTestkit\Expectation;
use Amerald\LaravelValidationTestkit\Tests\Stubs\Request;
use Amerald\LaravelValidationTestkit\TestsRequests;

class TestsRequestsTest extends TestCase
{
    public function testShouldSucceedWhenValidationFailsAndExpectationIsSetToFail()
    {
        $expectation = new Expectation([], false);

        $testClass = $this->mockTestsRequests();
        $testClass->expects($this->once())->method('request')->willReturn(Request::class);
        $testClass->expects($this->once())->method('assertEquals')->with(false, false, '');

        $testClass->testRequest($expectation);

        $this->assertTrue(true);
    }

    public function testShouldSucceedWhenValidationPassesAndExpectationIsSetToPass()
    {
        $expectation = new Expectation([
            'name' => 'John Doe',
        ], true);

        $testClass = $this->mockTestsRequests();
        $testClass->expects($this->once())->method('request')->willReturn(Request::class);
        $testClass->expects($this->once())->method('assertEquals')->with(true, true, '');

        $testClass->testRequest($expectation);

        $this->assertTrue(true);
    }

    public function testShouldFailWhenValidationFailsAndExpectationIsSetToPass()
    {
        $expectation = new Expectation([
            'name' => 'John Doe',
        ], false);

        $testClass = $this->mockTestsRequests();
        $testClass->expects($this->once())->method('request')->willReturn(Request::class);
        $testClass->expects($this->once())->method('assertEquals')->with(false, true);

        $testClass->testRequest($expectation);

        $this->assertTrue(true);
    }

    public function testShouldFailWhenValidationPassesAndExpectationIsSetToFail()
    {
        $expectation = new Expectation([], true);

        $testClass = $this->mockTestsRequests();
        $testClass->expects($this->once())->method('request')->willReturn(Request::class);
        $testClass->expects($this->once())
            ->method('assertEquals')
            ->with(true, false, 'The name field is required.');

        $testClass->testRequest($expectation);

        $this->assertTrue(true);
    }

    private function mockTestsRequests(array $methods = ['request', 'assertEquals'])
    {
        return $this->getMockForTrait(
            TestsRequests::class,
            [],
            '',
            true,
            true,
            true,
            $methods
        );
    }
}
