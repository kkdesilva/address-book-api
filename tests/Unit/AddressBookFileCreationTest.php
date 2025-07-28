<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Contracts\ContactRepository;
use App\Repositories\JsonFileContactRepository;
use Illuminate\Support\Facades\Storage;

it('creates a address book file', function () {

    $file = 'sample-address-book.json';

    // bind the repo to the container with a specific file
    app()->bind(ContactRepository::class, fn () => new JsonFileContactRepository($file));

    // resolve the repo out of the container
    $repository = app(ContactRepository::class);

    expect(Storage::exists($file))->toBeTrue();

    // clean up the file after the test
    Storage::delete($file);
    expect(Storage::exists($file))->toBeFalse();
});
