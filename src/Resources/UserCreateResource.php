<?php
namespace Keycloak\Admin\Resources;

use Keycloak\Admin\Representations\UserRepresentationBuilderInterface;

/**
 * Class UserCreateResource
 * @package Keycloak\Admin\Resources
 */
class UserCreateResource extends AbstractCreateResource implements UserCreateResourceInterface
{
    /**
     * @var UserRepresentationBuilderInterface
     */
    private $builder;
    /**
     * @var UsersResourceInterface
     */
    private $usersResource;

    public function __construct(UsersResourceInterface $usersResource, UserRepresentationBuilderInterface $builder)
    {
        parent::__construct();
        $this->usersResource = $usersResource;
        $this->builder = $builder;
    }

    public function username(string $username): UserCreateResourceInterface
    {
        $this->builder->withUsername($username);
        return $this;
    }

    public function email(string $email): UserCreateResourceInterface
    {
        $this->builder->withEmail($email);
        return $this;
    }

    public function enabled(bool $enabled): UserCreateResourceInterface
    {
        $this->builder->withEnabled($enabled);
        return $this;
    }

    public function password(string $password): UserCreateResourceInterface
    {
        $this->builder->withPassword($password);
        return $this;
    }

    public function temporaryPassword(string $password): UserCreateResourceInterface
    {
        $this->builder->withTemporaryPassword($password);
        return $this;
    }

    public function passwordIsTemporary(bool $temporary): UserCreateResourceInterface
    {
        $this->builder->withPasswordIsTemporary($temporary);
        return $this;
    }

    public function requiredActions(?array $actions): UserCreateResourceInterface
    {
        $this->builder->withRequiredActions($actions);
        return $this;
    }

    public function save(): void
    {
        $user = $this->builder->build();
        $this->usersResource->add($user);
    }
}
