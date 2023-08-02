<?php

declare(strict_types=1);

namespace Stancl\Tenancy\Resolvers;

use Illuminate\Routing\Route;
use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedByPathException;

class PathTenantResolver extends Contracts\CachedTenantResolver
{
    public function resolveWithoutCache(mixed ...$args): Tenant
    {
        /** @var Route $route */
        $route = $args[0];

        /** @var string $id */
        $id = $route->parameter(static::tenantParameterName());

        if ($id) {
            // Forget the tenant parameter so that we don't have to accept it in route action methods
            $route->forgetParameter(static::tenantParameterName());

            if ($tenant = tenancy()->find($id)) {
                return $tenant;
            }
        }

        throw new TenantCouldNotBeIdentifiedByPathException($id);
    }

    public function getArgsForTenant(Tenant $tenant): array
    {
        return [
            [$tenant->getTenantKey()],
        ];
    }

    public static function tenantParameterName(): string
    {
        return config('tenancy.identification.resolvers.' . static::class . '.tenant_parameter_name') ?? 'tenant';
    }

    public static function tenantRouteNamePrefix(): string
    {
        return config('tenancy.identification.resolvers.' . static::class . '.tenant_route_name_prefix') ?? static::tenantParameterName() . '.';
    }
}
