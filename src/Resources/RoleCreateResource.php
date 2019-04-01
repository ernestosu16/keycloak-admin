<?php
namespace Keycloak\Admin\Resources;

use Keycloak\Admin\Representations\RoleRepresentationBuilderInterface;

class RoleCreateResource implements RoleCreateResourceInterface
{
    /**
     * @var RolesResourceInterface
     */
    private $rolesResource;
    /**
     * @var string
     */
    private $realm;
    /**
     * @var RoleRepresentationBuilderInterface
     */
    private $builder;

    public function __construct(RolesResourceInterface $rolesResource, RoleRepresentationBuilderInterface $builder, string $realm)
    {
        $this->rolesResource = $rolesResource;
        $this->realm = $realm;
        $this->builder = $builder;
    }

    public function name(string $name): RoleCreateResourceInterface
    {
        $this->builder->withName($name);
        return $this;
    }

    public function description(string $description): RoleCreateResourceInterface
    {
        $this->builder->withDescription($description);
        return $this;
    }

    public function composite(bool $composite): RoleCreateResourceInterface
    {
        $this->builder->withComposite($composite);
        return $this;
    }

    public function clientRole(bool $clientRole): RoleCreateResourceInterface
    {
        $this->builder->withClientRole($clientRole);
        return $this;
    }

    public function save(): void
    {
        $this->rolesResource->add($this->builder->build());
    }
}