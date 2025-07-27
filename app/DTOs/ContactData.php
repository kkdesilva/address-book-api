<?php
declare(strict_types=1);

namespace App\DTOs;

final readonly class ContactData
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $email,
        public string $phone,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            str_replace(' ', '', $data['phone'])
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
