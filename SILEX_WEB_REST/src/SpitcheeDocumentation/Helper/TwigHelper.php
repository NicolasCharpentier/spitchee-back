<?php

namespace SpitcheeDocumentation\Helper;

use Container;

class TwigHelper
{
    private static function getRouteAuthActors(Container $app, $authConfiguration) {
        if (! $authConfiguration or 'none' === $authConfiguration) {
            return array();
        }

        $actors = array();

        foreach ($app['documentation']['roles'] as $roleId => $roleName) {
            if ('all' === $authConfiguration or false !== strpos($authConfiguration, $roleId)) {
                $actors[] = $roleName;
            }
        }

        return $actors;
    }

    public static function getRouteAuthDescription(Container $app, $authConfiguration) {
        if (0 === count($categories = self::getRouteAuthActors($app, $authConfiguration))) {
            return 'Vous n\'avez pas besoin d\'être connecté pour accéder à cette route';
        }

        $categories = join(' ou ', $categories);

        return "Afin d'accéder à cette route vous devez être connecté (en basic auth) en tant que $categories";
    }

    public static function getRouteAuthResume(Container $app, $authConfiguration) {
        if (count($app['documentation']['roles']) ===
            count($categories = self::getRouteAuthActors($app, $authConfiguration))) {
            return 'ALL';
        }

        return strtoupper(join(', ', $categories));
    }
}