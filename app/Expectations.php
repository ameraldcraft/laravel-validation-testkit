<?php

namespace Amerald\LaravelValidationTestkit;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Tests\TestCase;

class Expectations
{
    private const string EXPECTATION_SHOULD_PASS = 'should pass';
    private const string EXPECTATION_SHOULD_FAIL = 'should fail';

    private Closure|null $setup = null;

    private array $with = [];

    private array $without = [];

    private string $expectationName = '';

    /**
     * Perform any necessary setup actions before request is resolved,
     * e.g., set route resolver for route parameters.
     *
     * @param Closure $setup
     * @return self
     */
    public function setup(Closure $setup): self
    {
        $this->setup = $setup;

        return $this;
    }

    /**
     * Automatically build expectation name based on fluent method names.
     *
     * Examples:
     *
     * $request->with('name', 'John')->shouldPass() // will turn into
     * request with data set "with 'name' field equal to 'John' should pass"
     *
     * $request->without('last_name')->shouldFail() // will turn into
     * request with data set "without 'last_name' field should fail"
     *
     * $request->with('fist_name', 'John')->without('last_name')->shouldPass() // will turn into
     * request with data set "with first_name' field equal to 'John' and without 'last_name' field should pass"
     *
     * @param  string  $modifier
     * @param  string|null  $field
     * @param  mixed|null  $value
     */
    private function buildExpectationName(string $modifier, ?string $field = null, mixed $value = null)
    {
        if (in_array($modifier, ['with', 'without'])) {
            if (Str::contains($this->expectationName ?? '', $modifier)) {
                $this->expectationName .= ',';
            }
        }

        $this->expectationName .= ' ' . $modifier;

        if ($field) {
            $this->expectationName .= " '{$field}'";
        }

        if ($value && !is_callable($value)) {
            if (
                (is_object($value) && !method_exists($value, '__toString'))
                || is_array($value)
            ) {
                $value = json_encode($value);
            }

            $this->expectationName .= " equal to '" . Str::lower($value) . "'";
        }

        $isInputUnchanged = in_array(trim($this->expectationName), [
            self::EXPECTATION_SHOULD_PASS,
            self::EXPECTATION_SHOULD_FAIL
        ]);

        if ($isInputUnchanged) {
            $this->expectationName = 'entire input' . $this->expectationName;
        }
    }

    /**
     * Assign a value to a field.
     * Use 'not:' prefix to assign any value except the specified.
     *
     * @param  string  $field
     * @param  mixed|callable  $value
     * @return $this
     */
    public function with(string $field, $value): self
    {
        /*
         * Very convenient for failure tests.
         * $payload->with('name', 'not:string')->shouldFail()
         */
        if (is_string($value) && Str::startsWith($value, 'not:')) {
            $type = last(explode('not:', $value));

            if ($type === 'string') {
                $value = ['test-array'];
            } else {
                $value = 'test-string';
            }
        }

        $this->buildExpectationName('with', $field, $value);

        /*
         * Parse array fields.
         * $request->with('array.*.field.*.another_field', 'not:string')->shouldFail()
         *
         * It might seem confusing that we only replace the first array element and not all of them,
         * but a single element is enough to check validation.
         */
        if (Str::contains($field, '.*')) {
            $field = preg_replace('/\.\*/', '.0', $field);
        }
        $field = preg_replace('/\.\*/', 0, $field);

        Arr::set($this->with, $field, $value);

        return $this;
    }

    /**
     * Remove a field from input.
     *
     * @param  string  $field
     * @return $this
     */
    public function without(string $field): self
    {
        /*
         * Parse array fields.
         * $request->with('array.*.field.*.another_field', 'not:string')->shouldFail()
         *
         * It might seem confusing that we only replace the first array element and not all of them,
         * but a single element is enough to check validation.
         */
        if (Str::contains($field, '.*')) {
            $field = preg_replace('/\.\*/', '.0', $field);
        }

        $this->without[] = $field;
        $this->buildExpectationName('without', $field);

        return $this;
    }

    /**
     * @param  bool  $shouldPass
     * @param  string|null  $reason
     * @return array
     */
    private function setExpectation(bool $shouldPass, ?string $reason = null): array
    {
        $this->buildExpectationName($shouldPass ? self::EXPECTATION_SHOULD_PASS : self::EXPECTATION_SHOULD_FAIL);

        $setup = $this->setup;
        $with = $this->with;
        $without = $this->without;

        $expectation = function (array $input, TestCase $test) use ($shouldPass, $setup, $with, $without) {

            $setup !== null ? $setup($test) : null;

            foreach ($without as $field) {
                Arr::forget($input, $field);
            }

            foreach ($with as $fied => $value) {
                $input[$fied] = $value instanceof Closure ? $value($test) : $value;
            }

            return new Expectation($input, $shouldPass);
        };

        if ($reason) {
            $this->expectationName .= Str::lower(" ($reason)");
        }

        $dataSet = [
            trim($this->expectationName) => [$expectation],
        ];

        $this->expectationName = '';
        $this->with = [];
        $this->without = [];

        return $dataSet;
    }

    /**
     * Indicate that the validation should pass.
     *
     * @param  string|null  $reason
     * @return array
     */
    public function shouldPass(?string $reason = null): array
    {
        return $this->setExpectation(true, $reason);
    }

    /**
     * Indicate that the validation should fail.
     *
     * @param  string|null  $reason
     * @return array
     */
    public function shouldFail(?string $reason = null): array
    {
        return $this->setExpectation(false, $reason);
    }
}
