<?php

namespace Scito\Keycloak\Admin\Resources;

use GuzzleHttp\ClientInterface;
use Scito\Keycloak\Admin\Exceptions\CannotDeleteRealmException;
use Scito\Keycloak\Admin\Exceptions\CannotRetrieveRealmRepresentationException;
use Scito\Keycloak\Admin\Hydrator\HydratorInterface;
use Scito\Keycloak\Admin\Representations\RealmRepresentation;
use Scito\Keycloak\Admin\Representations\RealmRepresentationInterface;
use function json_decode;

class RealmResource implements RealmResourceInterface
{
    private $resourceFactory;
    /**
     * @var ClientInterface
     */
    private $client;
    /**
     * @var string
     */
    private $realm;
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    public function __construct(
        ClientInterface $client,
        ResourceFactoryInterface $resourceFactory,
        HydratorInterface $hydrator,
        string $realm
    ) {
        $this->resourceFactory = $resourceFactory;
        $this->client = $client;
        $this->realm = $realm;
        $this->hydrator = $hydrator;
    }

    public function clients(): ClientsResourceInterface
    {
        return $this->resourceFactory
            ->createClientsResource($this->realm);
    }

    public function toRepresentation(): RealmRepresentationInterface
    {
        $response = $this->client->get("/auth/admin/realms/{$this->realm}");

        if (200 !== $response->getStatusCode()) {
            throw new CannotRetrieveRealmRepresentationException("Cannot retrieve details of realm $this->realm");
        }

        $json = (string)$response->getBody();

        $data = json_decode($json, true);

        return $this->hydrator->hydrate($data, RealmRepresentation::class);
    }

    public function update(?array $options = null): RealmUpdateResourceInterface
    {
        // TODO: Implement update() method.
    }

    public function users(): UsersResourceInterface
    {
        return $this->resourceFactory
            ->createUsersResource($this->realm);
    }

    public function roles(): RolesResourceInterface
    {
        return $this->resourceFactory
            ->createRolesResource($this->realm);
    }

    public function groups(): GroupsResourceInterface
    {
        // TODO: Implement groups() method.
    }

    public function delete(): void
    {
        $response = $this->client->delete("/auth/admin/realms/{$this->realm}");

        if (204 !== $response->getStatusCode()) {
            throw new CannotDeleteRealmException("The realm [$this->realm] cannot be deleted");
        }
    }
}
