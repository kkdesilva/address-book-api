<?php

use App\Contracts\ContactRepository;
use App\DTOs\ContactData;
use App\Repositories\JsonFileContactRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

function setupTestAddressBook(): void
{
    $filePath = __DIR__ . '/test-address-book.json';
    if (file_exists($filePath)) {
        copy(
            __DIR__ . '/test-address-book.json',
            dirname(__DIR__, 2) . '/storage/app/private/test-address-book.json'
        );
    } else {
        expect(
            "Required file not found: {$filePath}. Please create the file with some sample json data."
        )->dd();
    }
}

beforeEach(function () {
    setupTestAddressBook();

    app()->bind(ContactRepository::class, function () {
        return new JsonFileContactRepository('test-address-book.json');
    });

    $this->repository = app(ContactRepository::class);
});

afterEach(function () {
    if (Storage::disk('local')->exists('test-address-book.json')) {
        Storage::disk('local')->delete('test-address-book.json');
    }
});

it('can find contact by id', function () {

    $contact = $this->repository->find('216049ae-ab21-4672-bc06-3eb370d3fb77');

    expect($contact)->not->toBeNull()
        ->and($contact->first_name)->toBe('Morgan')
        ->and($contact->last_name)->toBe('Reed')
        ->and($contact->email)->toBe('morgan.reed@example.com')
        ->and($contact->phone)->toBe('01700999888');
});

it('can create contact', function () {

    $newContact = $this->repository->create(
        new ContactData(
            first_name: 'Mark',
            last_name: 'Taylor',
            email: 'mark.taylor@example.com',
            phone: '01880999888'
        )
    );

    expect($newContact->id)->toBeUuid()->toBeString()
        ->and($newContact->first_name)->toBe('Mark')
        ->and($newContact->last_name)->toBe('Taylor')
        ->and($newContact->email)->toBe('mark.taylor@example.com');

    $contact = $this->repository->find($newContact->id);
    expect($contact)->not->toBeNull();
});

it('can update contact', function () {
    $contactData = new ContactData(
        first_name: 'Charlie',
        last_name: 'Taylor',
        email: 'charlie.tylor@example.com',
        phone: '01700777222'
    );

    $id = '33370eb9-be95-457b-949e-aa9abb9c6c46';

    $updatedContact = $this->repository->update($id, $contactData);

    expect($updatedContact)->not->toBeNull()
        ->and($updatedContact->first_name)->toBe('Charlie')
        ->and($updatedContact->last_name)->toBe('Taylor')
        ->and($updatedContact->email)->toBe('charlie.tylor@example.com')
        ->and($updatedContact->phone)->toBe('01700777222');
});

it('cannot update non-existing contact', function () {
    $contactData = new ContactData(
        first_name: 'Charlie',
        last_name: 'Taylor',
        email: 'charlie.taylor@example.com',
        phone: '08450555444'
    );

    $nonExistingId = '9990eb9-be95-457b-949e-aa9abb9c6999';
    $updatedContact = $this->repository->update($nonExistingId, $contactData);
    expect($updatedContact)->toBeNull();
});

it('cannot update contact with duplicate email', function () {
    $contactData = new ContactData(
        first_name: 'Charlie',
        last_name: 'Frost',
        email: 'jamie.harper@example.com',
        phone: '01700555444'
    );

    $id = '33370eb9-be95-457b-949e-aa9abb9c6c46';


    expect(fn () => $this->repository->update($id, $contactData))
        ->toThrow(
            ValidationException::class,
            'The email has already been taken by another contact.'
        );
});

it('cannot update contact with duplicate phone number', function () {
    $contactData = new ContactData(
        first_name: 'Charlie',
        last_name: 'Frost',
        email: 'charlie.frost@example.com',
        phone: '01700888444' // tel of jamie.harper
    );

    $id = '33370eb9-be95-457b-949e-aa9abb9c6c46';

    expect(fn () => $this->repository->update($id, $contactData))
        ->toThrow(
            ValidationException::class,
            'The email has already been taken by another contact.'
        );
});

it('can list all contacts', function () {

    $emptyFilters = [];
    $contacts = $this->repository->filter($emptyFilters);

    expect($contacts)->toBeArray()
        ->and($contacts)->toHaveCount(10);

    $firstContact = $contacts[0];
    expect($firstContact->first_name)->toBe('Alex')
        ->and($firstContact->last_name)->toBe('Johnson')
        ->and($firstContact->email)->toBe('alex.johnson@example.com');
});

it('can filter contacts by first name', function () {
    $filters = ['first_name' => 'Morgan'];
    $contacts = $this->repository->filter($filters);

    expect($contacts)->toBeArray()
        ->and($contacts)->toHaveCount(1)
        ->and($contacts[0])->toMatchArray([
            'first_name' => 'Morgan',
            'last_name' => 'Reed',
            'email' => 'morgan.reed@example.com',
            'phone' => '01700999888',
        ]);
});

it('can filter contacts by multiple fields', function () {
    $filters = [
        'last_name' => 'Harper',
        'phone' => '01700888444'
    ];
    $contacts = $this->repository->filter($filters);
    expect($contacts)->toBeArray()
        ->and($contacts)->toHaveCount(1)
        ->and($contacts[0])->toMatchArray([
            'first_name' => 'Jamie',
            'last_name' => 'Harper',
            'phone' => '01700888444',
            'email' => 'jamie.harper@example.com',
        ]);
});

it('can delete contact', function () {
    $id = '33370eb9-be95-457b-949e-aa9abb9c6c46';
    $deleted = $this->repository->delete($id);

    expect($deleted)->toBeTrue();

    $contact = $this->repository->find($id);
    expect($contact)->toBeNull();
});
