<?php

namespace Amerald\LaravelValidationTestkit\Tests;

use Amerald\LaravelValidationTestkit\Expectations;
use Amerald\LaravelValidationTestkit\Tests\Stubs\Request;
use Amerald\LaravelValidationTestkit\TestsRequests;

class SampleRequestTest extends TestCase
{
    use TestsRequests;

    /**
     * @inheritdoc
     */
    protected function request(): string
    {
        return Request::class;
    }

    /**
     * @inheritdoc
     */
    protected function input(): array
    {
        return [
            'name' => 'John Doe',
            'addresses' => [
                [
                    'country' => 'Ukraine',
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function expectations(Expectations $request): array
    {
        return [
            $request->shouldPass(),

            // Required/optional
            $request->without('name')->shouldFail(),
            $request->without('addresses')->shouldPass(),
            $request->without('addresses.*.country')->shouldFail(),
            $request->without('addresses')->without('addresses.*.country')->shouldPass(),

            // Types
            $request->with('name', 'not:string')->shouldFail(),
            $request->with('addresses', 'not:array')->shouldFail(),
            $request->with('addresses.*.country', 'not:string')->shouldFail(),
        ];
    }
}
