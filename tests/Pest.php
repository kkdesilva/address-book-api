<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use App\Contracts\ContactRepository;
use App\Repositories\JsonFileContactRepository;
use Illuminate\Support\Facades\Storage;

pest()->extend(Tests\TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', fn () => $this->toBe(1));

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/
const TEST_ADDRESS_BOOK_FILENAME = 'test-address-book.json';
const TEST_ADDRESS_BOOK_SOURCE_PATH = __DIR__ . '/Unit/' . TEST_ADDRESS_BOOK_FILENAME;
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
        dirname(__DIR__) . TEST_ADDRESS_BOOK_STORAGE_PATH
    );
}

function setupTestAddressBookRepositoryBinding($testInstance): void
{
    app()->bind(ContactRepository::class, fn (): JsonFileContactRepository => new JsonFileContactRepository(TEST_ADDRESS_BOOK_FILENAME));
    $testInstance->repository = app(ContactRepository::class);
}

function setupTestAddressBook($testInstance): void
{
    ensureTestAddressBookExists();
    copyTestAddressBookToStorage();
    setupTestAddressBookRepositoryBinding($testInstance);
}

function cleanupTestAddressBook(): void
{
    if (Storage::disk(TEST_ADDRESS_BOOK_DISK)->exists(TEST_ADDRESS_BOOK_FILENAME)) {
        Storage::disk(TEST_ADDRESS_BOOK_DISK)->delete(TEST_ADDRESS_BOOK_FILENAME);
    }
}
