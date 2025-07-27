<?php

use App\Contracts\ContactRepository;
use App\DTOs\ContactData;
use App\Repositories\JsonFileContactRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

const TEST_ADDRESS_BOOK_FILENAME = 'test-address-book.json';
const TEST_ADDRESS_BOOK_SOURCE_PATH = __DIR__ . '/' . TEST_ADDRESS_BOOK_FILENAME;
const TEST_ADDRESS_BOOK_STORAGE_PATH = '/storage/app/private/' . TEST_ADDRESS_BOOK_FILENAME;
const TEST_ADDRESS_BOOK_DISK = 'local';

function ensureTestAddressBookExists(): void
{
    if (!file_exists(TEST_ADDRESS_BOOK_SOURCE_PATH)) {
        expect(
            "Required file not found: " . TEST_ADDRESS_BOOK_SOURCE_PATH . ". Please create the file with some sample json data."
        )->dd();
    }
}

function copyTestAddressBookToStorage(): void
{
    copy(
        TEST_ADDRESS_BOOK_SOURCE_PATH,
        dirname(__DIR__, 2) . TEST_ADDRESS_BOOK_STORAGE_PATH
    );
}

beforeEach(function () {
    ensureTestAddressBookExists();
    copyTestAddressBookToStorage();
    app()->bind(ContactRepository::class, fn () => new JsonFileContactRepository(TEST_ADDRESS_BOOK_FILENAME));
    $this->repository = app(ContactRepository::class);
});

afterEach(function () {
    if (Storage::disk(TEST_ADDRESS_BOOK_DISK)->exists(TEST_ADDRESS_BOOK_FILENAME)) {
        Storage::disk(TEST_ADDRESS_BOOK_DISK)->delete(TEST_ADDRESS_BOOK_FILENAME);
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

it('cannot create contact with duplicate email', function () {
    $contactData = new ContactData(
        first_name: 'Jim',
        last_name: 'Cooper',
        email: 'jamie.harper@example.com', // email of existing contact
        phone: '08800555444'
    );

    expect(fn () => $this->repository->create($contactData))
        ->toThrow(
            ValidationException::class,
            'The email or phone number has already been taken by another contact'
        );
});

it('cannot create contact with duplicate phone number', function () {
    $contactData = new ContactData(
        first_name: 'Jim',
        last_name: 'Cooper',
        email: 'jim.cooper@example.com',
        phone: '01700888444' // phone of existing contact jamie.harper
    );

    expect(fn () => $this->repository->create($contactData))
        ->toThrow(
            ValidationException::class,
            'The email or phone number has already been taken by another contact'
        );
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

it('cannot delete with empty id', function () {
    $deleted = $this->repository->delete('');
    expect($deleted)->toBeFalse();

    // reconfirm that all contacts still exist
    $contacts = $this->repository->filter([]);

    expect($contacts)->toBeArray()
        ->and($contacts)->toHaveCount(10);
});
