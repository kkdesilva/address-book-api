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
    $response = $this->getJson(route('api.v1.contacts.index'));
    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->has(10)
                ->has(
                    0,
                    fn (AssertableJson $json) => $json
                    ->where('first_name', 'Alex')
                    ->where('last_name', 'Johnson')
                    ->where('email', 'alex.johnson@example.com')
                    ->etc()
                )
        );
});

it('can filter contacts by query parma', function () {
    $response = $this->getJson(route('api.v1.contacts.index', ['first_name' => 'Alex']));
    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->has(1)
                ->has(
                    0,
                    fn (AssertableJson $json) => $json
                    ->where('first_name', 'Alex')
                    ->where('last_name', 'Johnson')
                    ->where('email', 'alex.johnson@example.com')
                    ->where('phone', '01700111222')
                    ->etc()
                )
        );
});

it('can filter contacts by multiple query parma', function () {
    $response = $this->getJson(
        route(
            'api.v1.contacts.index',
            ['first_name' => 'Alex', 'phone' => '01700111222']
        )
    );
    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->has(1)
                ->has(
                    0,
                    fn (AssertableJson $json) => $json
                    ->where('first_name', 'Alex')
                    ->where('last_name', 'Johnson')
                    ->where('email', 'alex.johnson@example.com')
                    ->where('phone', '01700111222')
                    ->etc()
                )
        );
});

it('returns empty array when no matching records found', function () {
    $response = $this->getJson(
        route(
            'api.v1.contacts.index',
            ['first_name' => 'new-contact', 'phone' => '01700111222']
        )
    );
    $response->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has(0));
});
