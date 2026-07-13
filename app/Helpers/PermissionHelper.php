<?php

namespace App\Helpers;

class PermissionHelper
{
    const MATRIX = [
        'clients' => [
            'view'   => ['ADMIN', 'DIRECTEUR', 'ASSISTANT'],
            'create' => ['ADMIN', 'ASSISTANT'],
            'update' => ['ADMIN', 'ASSISTANT'],
            'delete' => ['ADMIN'],
        ],
        'lotissements' => [
            'view'   => ['ADMIN', 'DIRECTEUR', 'ASSISTANT'],
            'create' => ['ADMIN', 'ASSISTANT'],
            'update' => ['ADMIN', 'ASSISTANT'],
            'delete' => ['ADMIN'],
        ],
        'lots' => [
            'view'   => ['ADMIN', 'DIRECTEUR', 'ASSISTANT'],
            'create' => ['ADMIN', 'ASSISTANT'],
            'update' => ['ADMIN', 'ASSISTANT'],
            'delete' => ['ADMIN'],
        ],
        'contrats' => [
            'view'         => ['ADMIN', 'DIRECTEUR', 'ASSISTANT'],
            'create'       => ['ADMIN', 'ASSISTANT'],
            'update'       => ['ADMIN', 'ASSISTANT'],
            'delete'       => ['ADMIN', 'ASSISTANT'],
            'admin_delete' => ['ADMIN'],
            'print'        => ['ADMIN', 'DIRECTEUR', 'ASSISTANT'],
        ],
        'paiements' => [
            'view'         => ['ADMIN', 'DIRECTEUR', 'ASSISTANT'],
            'create'       => ['ADMIN', 'ASSISTANT'],
            'update'       => ['ADMIN', 'ASSISTANT'],
            'delete'       => ['ADMIN', 'ASSISTANT'],
            'admin_delete' => ['ADMIN'],
            'valider'      => ['ADMIN', 'DIRECTEUR'],
            'rejeter'      => ['ADMIN', 'DIRECTEUR'],
        ],
        'visites' => [
            'view'   => ['ADMIN', 'DIRECTEUR', 'ASSISTANT'],
            'create' => ['ADMIN', 'ASSISTANT'],
            'update' => ['ADMIN', 'ASSISTANT'],
            'delete' => ['ADMIN'],
        ],
        'echeances' => [
            'view'          => ['ADMIN', 'DIRECTEUR', 'ASSISTANT'],
            'marquer_payee' => ['ADMIN', 'ASSISTANT'],
        ],
        'documents' => [
            'view'   => ['ADMIN'],
            'manage' => ['ADMIN'],
            'upload' => ['ADMIN', 'ASSISTANT'],
        ],
        'contrat_documents' => [
            'view'   => ['ADMIN', 'DIRECTEUR', 'ASSISTANT'],
            'upload' => ['ADMIN', 'ASSISTANT'],
            'update' => ['ADMIN', 'ASSISTANT'],
            'delete' => ['ADMIN'],
        ],
        'utilisateurs' => [
            'view'   => ['ADMIN'],
            'create' => ['ADMIN'],
            'update' => ['ADMIN'],
            'delete' => ['ADMIN'],
        ],
    ];

    public static function can(string $resource, string $action): bool
    {
        $user = auth('utilisateurs')->user();
        if (!$user) return false;
        return in_array($user->role, self::MATRIX[$resource][$action] ?? []);
    }

    public static function cannot(string $resource, string $action): bool
    {
        return !self::can($resource, $action);
    }

    public static function abort403(string $resource, string $action): void
    {
        if (self::cannot($resource, $action)) {
            abort(403, 'Action non autorisée pour votre rôle.');
        }
    }

    public static function userPermissions(): array
    {
        $user = auth('utilisateurs')->user();
        if (!$user) return [];

        $perms = [];
        foreach (self::MATRIX as $resource => $actions) {
            foreach ($actions as $action => $roles) {
                $perms[$resource][$action] = in_array($user->role, $roles);
            }
        }
        return $perms;
    }
}
