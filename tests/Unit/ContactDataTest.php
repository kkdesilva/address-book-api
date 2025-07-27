<?php

use App\DTOs\ContactData;

it('replace spaces in phone number', function () {
    $data = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'phone' => '0123 456 7890'
    ];

    $contactData = ContactData::fromArray($data);
    expect($contactData->phone)->toBe('01234567890');
});
