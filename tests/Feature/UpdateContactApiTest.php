<?php

declare(strict_types=1);

use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    setupTestAddressBook($this);
});

afterEach(function () {
    cleanupTestAddressBook();
});

it('can update a contact', function () {
    $id = '216049ae-ab21-4672-bc06-3eb370d3fb77';
    $data = [
        'first_name' => 'Morgan van der',
        'last_name' => 'Reed',
        'email' => 'morgan.vand.reed@example.com',
        'phone' => '01700999888',
    ];
    $response = $this->putJson(route('api.v1.contacts.update', ['contact' => $id]), $data);
    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->where('first_name', 'Morgan van der')
                ->where('last_name', 'Reed')
                ->where('email', 'morgan.vand.reed@example.com')
                ->where('phone', '01700999888')
                ->etc()
        );
});

it('cannot update a contact when email is empty', function () {
    $id = '216049ae-ab21-4672-bc06-3eb370d3fb77';
    $data = [
        'first_name' => 'Morgan van der',
        'last_name' => 'Reed',
        'email' => '', // intentionally left empty to test validation
        'phone' => '01700999888',
    ];
    $response = $this->putJson(route('api.v1.contacts.update', ['contact' => $id]), $data);
    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('cannot update a contact when the id does not exist', function () {
    $id = '00000000-0000-0000-0000-000000000000'; // non-existent ID
    $data = [
        'first_name' => 'Morgan van der',
        'last_name' => 'Reed',
        'email' => 'morgan.reed@example.com',
        'phone' => '01700999888',
    ];
    $response = $this->putJson(route('api.v1.contacts.update', ['contact' => $id]), $data);
    $response->assertUnprocessable()
        ->assertJson(['message' => 'Contact cannot be updated or not found.']);
});

it('cannot update a contact when email or phone already exists', function () {
    $id = '216049ae-ab21-4672-bc06-3eb370d3fb77'; // existing contact ID
    $data = [
        'first_name' => 'Morgan van der',
        'last_name' => 'Reed',
        'email' => 'charlie.frost@example.com', // email already exists with another contact
        'phone' => '01700999888',
    ];
    $response = $this->putJson(route('api.v1.contacts.update', ['contact' => $id]), $data);
    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email' => 'The email has already been taken by another contact.']);
});
