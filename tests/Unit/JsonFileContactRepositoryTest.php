<?php

use App\Contracts\ContactRepository;
use App\Models\Contact;

it('can find contact by id', function () {
    $repository = app(ContactRepository::class);

    $contact = $repository->find('687b4cbd-b175-416f-b9d1-13ff81c81775');

    expect($contact)->not->toBeNull()
        ->and($contact->first_name)->toBe('Ken')
        ->and($contact->last_name)->toBe('Barlow')
        ->and($contact->email)->toBe('ken.barlow@corrie.co.uk')
        ->and($contact->phone)->toBe('019134784929');
});
