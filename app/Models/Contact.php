<?php
declare(strict_types=1);

namespace App\Models;

class Contact
{
    public function __construct(
        public string $id,
        public string $first_name,
        public string $last_name,
        public string $email,
        public string $phone
    ) {}
}
