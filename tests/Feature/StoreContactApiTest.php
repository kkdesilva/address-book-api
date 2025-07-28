<?php
declare(strict_types=1);

use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    setupTestAddressBook($this);
});

afterEach(function () {
    cleanupTestAddressBook();
});

it('can store a contact', function () {
    $data = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'phone' => '1234567890',
    ];
    $response = $this->postJson(route('api.v1.contacts.index'), $data);
    $response->assertCreated()
        ->assertJson(
            fn (AssertableJson $json) => $json->where('first_name', 'John')
                ->where('last_name', 'Doe')
                ->where('email', 'john.doe@example.com')
                ->where('phone', '1234567890')
                ->etc()
        );
});

it('returns validation error when storing a contact with existing email', function () {
    $data = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'morgan.reed@example.com', // Existing email in the test data
        'phone' => '09900111999',
    ];

    $response = $this->postJson(route('api.v1.contacts.index'), $data);
    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});
