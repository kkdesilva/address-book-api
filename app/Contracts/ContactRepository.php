<?php
declare(strict_types=1);

namespace App\Contracts;

use App\Models\Contact;

interface ContactRepository
{
    public function find(string $id): ?Contact;
    public function create(array $data): Contact;
    public function update(string $id, array $data): ?Contact;
    public function delete(string $id): bool;
}
