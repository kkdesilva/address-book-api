# Propel Tech Test

## Description
- This project implements a simple RESTful Address Book API using Laravel.
- The API allows users to create, read, update, and delete contacts in an address book.
- Contacts are stored in a JSON file, as required.
### Example JSON schema:
```json
[
    {
    "first_name": "David",
    "last_name": "Platt",
    "phone": "01913478234",
    "email": "david.platt@corrie.co.uk"
    },
    {
    "first_name": "Jason",
    "last_name": "Grimshaw",
    "phone": "01913478123",
    "email": "jason.grimshaw@corrie.co.uk"
    },
    {
    "first_name": "Ken",
    "last_name": "Barlow",
    "phone": "019134784929",
    "email": "ken.barlow@corrie.co.uk"
    },
    {
    "first_name": "Rita",
    "last_name": "Sullivan",
    "phone": "01913478555",
    "email": "rita.sullivan@corrie.co.uk"
    },
    {
    "first_name": "Steve",
    "last_name": "McDonald",
    "phone": "01913478555",
    "email": "steve.mcdonald@corrie.co.uk"
    }
]
```

## Setup
 - Clone the repository
 - Run `composer install` to install dependencies
 - Run `php artisan serve` to start the server
 - The API will be available at `http://localhost:8000/api/v1/contacts`
 - To run the tests, run `./vendor/bin/pest`
## Dev notes
 - Plan to add id field JSON schema. It will be a UUID for immutability, scalability, lookup performance, etc.
 - I assume all fields are required, so I will not allow empty fields.
 - I will not allow duplicate emails or phone numbers.
 - Duplicate contact check can be done by email or phone number.
 - I use Pest for unit and feature testing.
 - I use Larastan for static analysis, Pint with PSR-12 for code formatting, and Rector.
 
