<?php

namespace Amerald\LaravelValidationTestkit;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Expectations
{
    /**
     * Mutable input.
     *
     * @var array $input
     */
    private $input;

    /**
     * Original (immutable) input.
     *
     * @var array $originalInput
     */
    private $originalInput;

    /**
     * @var string $expectationName
     */
    private $expectationName;

    /**
     * Expectations constructor.
     *
     * @param  array  $input
     */
    public function __construct(array $input)
    {
        $this->originalInput = $input;
        $this->input = $input;
    }

    /**
     * Automatically build expectation name based on fluent method names.
     *
     * Examples:
     *
     * $request->with('name', 'John')->shouldPass() // will turn into
     * Request with 'name' field equal to 'John' should pass
     *
     * $request->without('last_name')->shouldFail() // will turn into
     * Request without 'last_name' field should fail
     *
     * $request->with('fist_name', 'John')->without('last_name')->shouldPass() // will turn into
     * Request with 'first_name' field equal to 'John' and without 'last_name' field should pass
     *
     * @param  string  $modifier
     * @param  string|null  $field
     * @param  mixed|null  $value
     */
    private function buildExpectationName(string $modifier, string $field = null, $value = null)
    {
        if (!Str::startsWith($this->expectationName, 'Request')) {
            $this->expectationName = 'Request';
        }

        if (in_array($modifier, ['with', 'without'])) {
            if (Str::contains($this->expectationName, $modifier)) {
                $this->expectationName .= ' and';
            }
        }

        $this->expectationName .= ' ' . $modifier;

        if ($field) {
            $this->expectationName .= " '{$field}' field";
        }

        if ($value) {
            if (
                (is_object($value) && !method_exists($value, '__toString'))
                || is_array($value)
            ) {
                $value = json_encode($value);
            }

            $this->expectationName .= " equal to '{$value}'";
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
        if (is_callable($value)) {
            $value = $value($this);
        }

        /*
         * Very convenient for failure tests.
         * $payload->with('name', 'not:string')->shouldFail()
         */
        if (Str::startsWith($value, 'not:')) {
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

        Arr::set($this->input, $field, $value);

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

        Arr::forget($this->input, $field);
        $this->buildExpectationName('without', $field);

        return $this;
    }

    /**
     * @param  bool  $shouldPass
     * @return array
     */
    private function setExpectation(bool $shouldPass): array
    {
        $this->buildExpectationName($shouldPass ? 'should pass'
            : 'should fail');

        $dataSet = [
            $this->expectationName => new Expectation($this->input, $shouldPass),
        ];
        $this->expectationName = null;
        $this->input = $this->originalInput;

        return $dataSet;
    }

    /**
     * Indicate that the validation should pass.
     *
     * @return array
     */
    public function shouldPass(): array
    {
        return $this->setExpectation(true);
    }

    /**
     * Indicate that the validation should fail.
     *
     * @return array
     */
    public function shouldFail(): array
    {
        return $this->setExpectation(false);
    }

    /**
     * Get original input.
     *
     * @param  string|null  $field
     * @return mixed
     */
    public function originalInput(string $field = null)
    {
        if ($field) {
            return Arr::get($this->originalInput, $field);
        }

        return $this->originalInput;
    }

    /**
     * Get mutated input.
     *
     * @param  string|null  $field
     * @return mixed
     */
    public function input(string $field = null)
    {
        if ($field) {
            return Arr::get($this->input, $field);
        }

        return $this->input;
    }
}
