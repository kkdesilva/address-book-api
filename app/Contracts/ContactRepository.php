<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\ContactData;
use App\Models\Contact;

interface ContactRepository
{
    public function find(string $id): ?Contact;
    public function create(ContactData $data): Contact;
    public function update(string $id, ContactData $data): ?Contact;
    public function delete(string $id): bool;

    /**
     * @param array<string, string|null> $filters
     * @return array<Contact>
     */
    public function filter(array $filters): array;
}
