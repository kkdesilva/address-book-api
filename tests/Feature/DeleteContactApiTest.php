<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    setupTestAddressBook($this);
});

afterEach(function () {
    cleanupTestAddressBook();
});

it('deletes a contact', function () {

    $contact = $this->getJson(route('api.v1.contacts.index', ['first_name' => 'Morgan']))[0];

    $response = $this->deleteJson(route('api.v1.contacts.destroy', $contact['id']));

    $response->assertStatus(204);

    // Verify the contact is no longer in the address book
    $this->getJson(route('api.v1.contacts.show', $contact['id']))
        ->assertNotFound()
        ->assertJson(['message' => 'Contact not found.']);
});

it('cannot delete a contact that does not exist', function () {
    $response = $this->deleteJson(
        route('api.v1.contacts.destroy', '00000000-0000-0000-0000-000000000000')
    );
    $response->assertNotFound()
        ->assertJson(['message' => 'Contact not found.']);

    // verify the address book file still exists and it's not empty
    expect(Storage::exists(TEST_ADDRESS_BOOK_FILENAME))
        ->toBeTrue('Address book file should still exist after failed delete.')
        ->and(Storage::get(TEST_ADDRESS_BOOK_FILENAME))
        ->not->toBeEmpty('Address book file should not be empty after failed delete.');

    // verify the address book file contains the original contacts
    $contacts = json_decode(Storage::get('test-address-book.json'), true);
    expect($contacts)
        ->toHaveCount(10, 'Address book file should still contain 10 contacts after failed delete.')
        ->and($contacts[0]['first_name'])->toBe('Alex', 'First contact should still be Alex.')
        ->and($contacts[0]['last_name'])->toBe('Johnson', 'First contact last name should still be Johnson.')
        ->and($contacts[9]['first_name'])->toBe('Skyler', 'Last contact first name should still be Skyler.')
        ->and($contacts[9]['last_name'])->toBe('Hunt', 'Last contact last name should still be Hunt.');
});
