<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    setupTestAddressBook($this);
});

afterEach(function () {
    cleanupTestAddressBook();
});

it('can show a contact', function () {

    $id = '216049ae-ab21-4672-bc06-3eb370d3fb77';
    $response = $this->getJson(route('api.v1.contacts.show', ['contact' => $id]));
    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->where('first_name', 'Morgan')
                ->where('last_name', 'Reed')
                ->where('email', 'morgan.reed@example.com')
                ->where('phone', '01700999888')
                ->etc()
        );
});

it('returns 404 when contact not found', function () {
    $id = '00000000-0000-0000-0000-000000000000';
    $response = $this->getJson(route('api.v1.contacts.show', ['contact' => $id]));
    $response->assertNotFound()
        ->assertJson(['message' => 'Contact not found.']);
});
