<?php

namespace Amerald\LaravelValidationTestkit;

class Expectation
{
    /**
     * @var array $input
     */
    private $input;

    /**
     * @var bool $shouldPass
     */
    private $shouldPass;

    /**
     * Expectation constructor.
     *
     * @param  array  $input
     * @param  bool  $shouldPass
     */
    public function __construct(array $input, bool $shouldPass)
    {
        $this->input = $input;
        $this->shouldPass = $shouldPass;
    }

    /**
     * @return array
     */
    public function input(): array
    {
        return $this->input;
    }

    /**
     * @return bool
     */
    public function shouldPass(): bool
    {
        return $this->shouldPass;
    }
}
