<?php

namespace Amerald\LaravelValidationTestkit;

use Closure;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;

trait TestsRequests
{
    /**
     * Request class to test.
     *
     * @return string
     */
    abstract protected static function request(): string;

    /**
     * Input to be validated.
     *
     * Should include all expected fields, including optional ones.
     *
     * @return array
     */
    abstract protected static function input(): array;

    /**
     * Define validation expectations.
     *
     * Examples:
     * $request->without('required_field')->shouldFail();
     * $request->without('optional_field')->shouldPass();
     * $request->with('some_field', 'wrongValue')->shouldFail();
     * $request->with('string_field', 'not:string')->shouldFail();
     * $request->without('array.*.field')->shouldFail();
     *
     * @param  Expectations  $request
     * @return Expectation[]
     */
    abstract protected static function expectations(Expectations $request): array;

    /**
     * Provide test with the expectations.
     *
     * @return array
     */
    public static function provideExpectations(): array
    {
        $request = new Expectations(static::input());
        $expectations = [];

        /** @var array $expectation */
        foreach (static::expectations($request) as $expectation) {
            $name = array_keys($expectation)[0];
            $expectation = array_values($expectation)[0];
            $expectations[$name] = [fn() => $expectation];
        }

        return $expectations;
    }

    /**
     * Test request rules.
     *
     * @param  Closure  $expectation
     */
    #[DataProvider('provideExpectations')]
    public function testRequest(Closure $expectation)
    {
        try {
            $expectation = $expectation();
            $input = $expectation->input();

            array_walk($input, function (&$value) {
                $value = $value instanceof Closure ? $value($this) : $value;
            });

            $request = static::request();
            $request = new $request($input, $input);

            $request->validate($request->rules());
            $passed = true;
        } catch (ValidationException $exception) {
            if ($expectation->shouldPass() !== false) {
                $message = implode("\n", $exception->validator->errors()->all());
            }
        }

        $this->assertEquals($expectation->shouldPass(), $passed ?? false, $message ?? '');
    }
}
