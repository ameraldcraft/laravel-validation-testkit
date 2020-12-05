# Laravel Validation Testkit
Test form requests with ease and in no time

## Installation
`composer require amerald/laravel-validation-testkit`

## Usage
```php
<?php

use Illuminate\Foundation\Http\FormRequest;

class StoreUser extends FormRequest
{
    protected $rules = [
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'email' => 'required|email',
        'password' => 'required|string',
        'password_confirmation' => 'required|same:password',
        'addresses' => 'array',
        'addresses.*.country' => 'required_with:addresses|string',
    ];
}
```

```php
<?php

use Amerald\LaravelValidationTestkit\Expectation;
use Amerald\LaravelValidationTestkit\Expectations;
use Amerald\LaravelValidationTestkit\TestsRequests;

class StoreUserTest extends TestCase
{
    use TestsRequests;
    
    /**
     * Request class to test.
     *
     * @return string
     */
    protected function request(): string
    {
        return StoreUser::class;
    }
    
   /**
    * Input to be validated.
    *
    * Should include all expected fields, including optional ones.
    *
    * @return array
    */
    protected function input(): array
    {
        return [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@example.com',
            'password' => '1234',
            'password_confirmation' => '1234',
            'addresses' => [
                [
                   'country' => 'Ukraine',
                ],
            ],
        ];
    }
    
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
    protected function expectations(Expectations $request): array
    {
        return [
            // Check if validation passes when all fields are present  
            $request->shouldPass(),
            
            // Required/optional
            $request->without('first_name')->shouldFail(),
            $request->without('last_name')->shouldFail(),
            $request->without('email')->shouldFail(),
            $request->without('password')->shouldFail(),
            $request->without('password_confirmation')->shouldFail(),
            $request->without('addresses')->shouldPass(),
            $request->without('addresses.*.country')->shouldFail(),
            
            // Types
            $request->with('first_name', 'not:string')->shouldFail(), // using not:string will set the field value to anything but a string 
            $request->with('last_name', 'not:string')->shouldFail(),
            $request->with('email', 'not:email')->shouldFail(),
            $request->with('password', 'not:string')->shouldFail(),
            $request->with('password_confirmation', 'not:string')->shouldFail(),
            $request->with('addresses', 'not:array')->shouldFail(),
            $request->with('addresses.*.country', 'not:string')->shouldFail(),

            // It is possible to use closure to set value.
            $request->with('password', function (Expectations $request) {
                return $request->originalInput('password_confirmation');
            })->shouldPass(),
                
            // Expectations are chainable
            $request->without('addresses')->without('addresses.*.country')->shouldPass(),
        ];
    }
}
```
