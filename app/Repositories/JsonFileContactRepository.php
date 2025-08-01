<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\ContactRepository;
use App\DTOs\ContactData;
use App\Models\Contact;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

readonly class JsonFileContactRepository implements ContactRepository
{
    public function __construct(private string $file = 'address-book.json')
    {
        // check the file exists, if not create it with an empty array
        if (!Storage::exists($this->file)) {
            $content = json_encode([], JSON_PRETTY_PRINT);
            if ($content === false) {
                throw new RuntimeException('Failed to encode data as JSON');
            }
            Storage::put($this->file, $content);
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
            fn (Contact $contact): bool => $contact->email === $data->email || $contact->phone === $data->phone
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

        $index = collect($contacts)->search(fn (Contact $contact): bool => $contact->id === $id);

        if ($index === false) {
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
        $contact = $this->find($id);

        if (!$contact instanceof Contact) {
            return false;
        }

        $contacts = collect($this->all())
            ->filter(fn ($contact): bool => $contact->id !== $id)
            ->values();

        $this->write($contacts->all());

        return true;
    }

    /**
     * @param array<string, string|null> $filters
     * @return array<Contact>
     */
    public function filter(array $filters): array
    {
        return collect($this->all())
            ->filter(
                fn ($contact) => collect($filters)->every(
                    fn ($value, $key): bool => empty($value) || stripos((string) $contact->$key, (string) $value) !== false
                )
            )
            ->values()
            ->all();
    }

    /**
     * @return Contact[]
     */
    private function all(): array
    {
        $contacts = json_decode((string) Storage::get($this->file), true);

        return array_map(fn ($data): Contact => new Contact(...$data), $contacts);
    }

    /**
     * @param Contact[] $contacts
     */
    private function write(array $contacts): void
    {
        $array = collect($contacts)->map(fn (Contact $contact): array => $contact->toArray())->all();

        $content = json_encode($array, JSON_PRETTY_PRINT);

        if ($content === false) {
            throw new RuntimeException('Failed to encode data as JSON');
        }

        Storage::put($this->file, $content);
    }

    /**
     * @param Contact[] $existingContacts
     */
    private function hasDuplicateEmail(array $existingContacts, ContactData $data, string $id): bool
    {
        $index = collect($existingContacts)->search(
            fn (Contact $contact): bool =>
                $contact->id !== $id &&
                ($contact->email === $data->email || $contact->phone === $data->phone)
        );

        return !empty($index);
    }
}
