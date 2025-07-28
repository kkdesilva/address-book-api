<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function (): void {
    setupTestAddressBook($this);
});

afterEach(function (): void {
    cleanupTestAddressBook();
});

it('can fetch all contacts', function (): void {
    $response = $this->getJson(route('api.v1.contacts.index'));
    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->has(10)
                ->has(
                    0,
                    fn (AssertableJson $json): AssertableJson => $json
                    ->where('first_name', 'Alex')
                    ->where('last_name', 'Johnson')
                    ->where('email', 'alex.johnson@example.com')
                    ->etc()
                )
        );
});

it('can filter contacts by query parma', function (): void {
    $response = $this->getJson(route('api.v1.contacts.index', ['first_name' => 'Alex']));
    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->has(1)
                ->has(
                    0,
                    fn (AssertableJson $json): AssertableJson => $json
                    ->where('first_name', 'Alex')
                    ->where('last_name', 'Johnson')
                    ->where('email', 'alex.johnson@example.com')
                    ->where('phone', '01700111222')
                    ->etc()
                )
        );
});

it('can filter contacts by multiple query parma', function (): void {
    $response = $this->getJson(
        route(
            'api.v1.contacts.index',
            ['first_name' => 'Alex', 'phone' => '01700111222']
        )
    );
    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->has(1)
                ->has(
                    0,
                    fn (AssertableJson $json): AssertableJson => $json
                    ->where('first_name', 'Alex')
                    ->where('last_name', 'Johnson')
                    ->where('email', 'alex.johnson@example.com')
                    ->where('phone', '01700111222')
                    ->etc()
                )
        );
});

it('returns empty array when no matching records found', function (): void {
    $response = $this->getJson(
        route(
            'api.v1.contacts.index',
            ['first_name' => 'new-contact', 'phone' => '01700111222']
        )
    );
    $response->assertOk()
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json->has(0));
});
