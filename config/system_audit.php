<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Correos autorizados para la auditoría del sistema
    |--------------------------------------------------------------------------
    |
    | Si se define, la sección solo será visible para esos correos (además de
    | exigir el rol de administrador). Si queda vacío, cualquier administrador
    | puede entrar. Para restringirla a una sola persona:
    |
    |   SYSTEM_AUDIT_EMAILS="tu-correo@dominio.com"
    |
    */
    'allowed_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('SYSTEM_AUDIT_EMAILS', ''))
    ))),

    'history_limit' => (int) env('SYSTEM_AUDIT_HISTORY', 15),

    'log_tail_kb' => (int) env('SYSTEM_AUDIT_LOG_TAIL_KB', 256),

    'backup_paths' => [
        'app/backups',
        'app/backup',
        'backups',
    ],
];
