<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\ContactRepository;
use App\DTOs\ContactData;
use App\Models\Contact;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class JsonFileContactRepository implements ContactRepository
{
    /*
     * the file will be located in the storage/app/private directory.
     * location can be changed in config/filesystems.php
     */
    private string $file;

    public function __construct(string $file = 'address-book.json')
    {
        $this->file = $file;
        // check the file exists, if not create it with an empty array
        if (!Storage::exists($this->file)) {
            Storage::put($this->file, json_encode([], JSON_PRETTY_PRINT));
        }
    }

    public function find(string $id): ?Contact
    {
        return collect($this->all())->firstWhere('id', $id);
    }

    /**
     * @throws ValidationException
     */
    public function create(ContactData $data): Contact
    {
        $contacts = $this->all();

        $index = collect($contacts)->search(
            fn(Contact $contact) => $contact->email === $data->email || $contact->phone === $data->phone
        );

        if ($index !== false) {
            throw ValidationException::withMessages([
                'email' => ['The email or phone number has already been taken by another contact.'],
            ]);
        }

        $id = (string) Str::uuid();
        $newContact = new Contact($id, ...$data->toArray());
        $contacts[] = $newContact;
        $this->write($contacts);

        return $newContact;
    }

    /**
     * @throws ValidationException
     */
    public function update(string $id, ContactData $data): ?Contact
    {
        $contacts = $this->all();

        $index = collect($contacts)->search(fn(Contact $contact) => $contact->id === $id);

        if($index === false) {
            return null;
        }

        if ($this->hasDuplicateEmail($contacts, $data, $id)) {
            throw ValidationException::withMessages([
                'email' => ['The email has already been taken by another contact.'],
            ]);
        }

        $contacts[$index] = new Contact($id, ...$data->toArray());
        $this->write($contacts);

        return $contacts[$index];
    }

    public function delete(string $id): bool
    {
        // TODO: Implement delete() method.
    }

    public function filter(array $filters): array
    {
        // TODO: Implement filter() method.
    }

    private function all(): array
    {
        $contacts = json_decode(Storage::get($this->file), true);

        return array_map(fn($data) => new Contact(...$data), $contacts);
    }

    private function write(array $contacts): void
    {
        $array = collect($contacts)->map(fn($contact) => $contact->toArray())->all();

        Storage::put($this->file, json_encode($array, JSON_PRETTY_PRINT));
    }

    private function hasDuplicateEmail(array $existingContacts, ContactData $data, $id): bool
    {
        $index = collect($existingContacts)->search(
            fn(Contact $contact) =>
                $contact->id !== $id &&
                ($contact->email === $data->email || $contact->phone === $data->phone)
        );

        return !empty($index);
    }
}
