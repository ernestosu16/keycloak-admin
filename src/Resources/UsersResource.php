<?php

namespace Keycloak\Admin\Resources;

use function array_filter;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use function http_build_query;
use function iterator_to_array;
use function json_decode;
use Keycloak\Admin\Exceptions\CannotCreateUserException;
use Keycloak\Admin\Exceptions\CannotDeleteUserException;
use Keycloak\Admin\Exceptions\CannotUpdateUserException;
use Keycloak\Admin\Exceptions\UnknownUserException;
use Keycloak\Admin\Exceptions\CannotRetrieveUsersException;
use Keycloak\Admin\Hydrator\HydratorInterface;
use Keycloak\Admin\Representations\UserRepresentation;
use Keycloak\Admin\Representations\UserRepresentationInterface;
use function var_dump;

class UsersResource implements UsersResourceInterface
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var string
     */
    private $realm;

    private $resourceFactory;

    private $hydrator;

    public function __construct(ClientInterface $client, ResourceFactoryInterface $resourceFactory, HydratorInterface $hydrator, string $realm)
    {
        $this->client = $client;
        $this->realm = $realm;
        $this->hydrator = $hydrator;
        $this->resourceFactory = $resourceFactory;
    }

    public function count()
    {
        $response = $this->client->post("/auth/admin/realms/{$this->realm}/users/count");
    }

    /**
     * @param $username
     * @return UserRepresentationInterface|null
     */
    public function getByUsername(string $username): ?UserRepresentationInterface
    {
        return $this
            ->search()
            ->username($username)
            ->first();
    }

    public function update(UserRepresentationInterface $user): void
    {
        $id = $user->getId();

        if (null == $id) {
            throw new CannotUpdateUserException("User id missing");
        }

        $data = $this->hydrator->extract($user);
        unset($data['created'], $data['username']);

        $response = $this->client->put("/auth/admin/realms/{$this->realm}/users/{$id}", [
            'body' => json_encode($data)
        ]);

        if (204 !== $response->getStatusCode()) {
            throw new CannotUpdateUserException("User [$id] cannot be updated");
        }
    }

    /**
     * @param $email
     * @return UserRepresentationInterface|null
     */
    public function getByEmail(string $email): ?UserRepresentationInterface
    {
        return $this->search()->email($email)->first();
    }

    public function add(UserRepresentationInterface $user): void
    {
        $data = $this->hydrator->extract($user);
        unset($data['id'], $data['created']);
        $response = $this->client->post("/auth/admin/realms/{$this->realm}/users", [
            'body' => json_encode($data)
        ]);

        if (201 !== $response->getStatusCode()) {
            throw new CannotCreateUserException("Unable to create user");
        }
    }

    /**
     * @param array|null $options
     * @return UserCreateResourceInterface
     */
    public function create(?array $options = null): UserCreateResourceInterface
    {
        $builderResource = $this->resourceFactory->createUsersCreateResource($this->realm);
        if (null !== $options) {
            foreach ($options as $key => $value) {
                $builderResource->$key($value);
            }
        }
        return $builderResource;
    }

    public function deleteByEmail($email)
    {
        if (false == ($user = $this->getByEmail($email))) {
            throw new UnknownUserException("User with email [$email] does not exist");
        }
        return $this->deleteById($user->getId());
    }

    public function deleteByUsername($username)
    {
        if (false == ($user = $this->getByUsername($username))) {
            throw new UnknownUserException("User with username [$username] does not exist");
        }
        return $this->deleteById($user->getId());
    }

    public function deleteById($id)
    {
        $response = $this->client->delete("/auth/admin/realms/{$this->realm}/users/{$id}");

        if (204 != $response->getStatusCode()) {
            throw new CannotDeleteUserException("User with id [$id] cannot be deleted");
        }
    }

    public function get($id)
    {
        return $this->resourceFactory
            ->createUserResource($this->realm, $id);
    }

    public function search(array $options = []): UsersSearchResourceInterface
    {
        $searchResource = $this
            ->resourceFactory
            ->createUsersSearchResource($this->realm);

        foreach ($options as $k => $v) {
            $searchResource->$k($v);
        }

        return $searchResource;
    }
}