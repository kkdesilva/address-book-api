<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\ContactRepository;
use App\DTOs\ContactData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Http\Resources\ContactResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

final class ContactController extends Controller
{
    public function __construct(private readonly ContactRepository $repo)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResource
    {
        $filters = $request->only(['first_name', 'last_name', 'email', 'phone']);

        $contacts = $this->repo->filter($filters);

        return ContactResource::collection($contacts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContactRequest $request): JsonResponse
    {
        $data = ContactData::fromArray($request->validated());

        return ContactResource::make($this->repo->create($data))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): ContactResource|JsonResponse
    {
        $contact = $this->repo->find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found.'], Response::HTTP_NOT_FOUND);
        }

        return ContactResource::make($contact);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContactRequest $request, string $id): ContactResource|JsonResponse
    {
        $data = ContactData::fromArray($request->validated());

        $updatedContact = $this->repo->update($id, $data);

        if (!$updatedContact) {
            return response()->json(
                ['message' => 'Contact cannot be updated or not found.'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return ContactResource::make($updatedContact);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): \Illuminate\Http\Response|JsonResponse
    {
        $contact = $this->repo->find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found.'], Response::HTTP_NOT_FOUND);
        }

        $this->repo->delete($id);

        return response()->noContent();
    }
}
