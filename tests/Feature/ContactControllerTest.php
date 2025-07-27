<?php
declare(strict_types=1);

use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    setupTestAddressBook($this);
});

afterEach(function () {
    cleanupTestAddressBook();
});

it('can fetch all contacts', function () {
    $response = $this->getJson('/api/v1/contacts');
    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->has('data', 10)
                ->has('data.0', fn (AssertableJson $json) => $json
                    ->where('first_name', 'Alex')
                    ->where('last_name', 'Johnson')
                    ->where('email', 'alex.johnson@example.com')
                    ->etc()
                )
        );

});
