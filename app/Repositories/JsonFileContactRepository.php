<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\ContactRepository;
use App\DTOs\ContactData;
use App\Models\Contact;
use Illuminate\Support\Facades\Storage;

class JsonFileContactRepository implements ContactRepository
{
    /*
     * the file will be located in the storage/app/private directory.
     * location can be changed in config/filesystems.php
     */
    private string $file = 'address-book.json';

    public function find(string $id): ?Contact
    {
        $contacts = json_decode(Storage::get($this->file), true);

        return collect($contacts)
            ->map(fn($data) => new Contact(...$data))
            ->firstWhere('id', $id);
    }

    public function create(ContactData $data): Contact
    {
        // TODO: Implement create() method.
    }

    public function update(string $id, ContactData $data): ?Contact
    {
        // TODO: Implement update() method.
    }

    public function delete(string $id): bool
    {
        // TODO: Implement delete() method.
    }

    public function filter(array $filters): array
    {
        // TODO: Implement filter() method.
    }
}
