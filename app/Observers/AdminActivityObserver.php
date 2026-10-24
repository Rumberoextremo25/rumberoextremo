<?php

namespace App\Observers;

use App\Services\AdminActivityNotifier;
use Illuminate\Database\Eloquent\Model;

/**
 * Observa un modelo y avisa al AdminActivityNotifier en cada cambio.
 *
 * Se registra un observador por modelo en AppServiceProvider, de modo que
 * cualquier creación, edición o borrado (desde el panel, la API o artisan)
 * dispara la notificación sin tener que tocar los controladores.
 */
class AdminActivityObserver
{
    public function __construct(private AdminActivityNotifier $notifier)
    {
    }

    public function created(Model $model): void
    {
        $this->notifier->record('created', $model);
    }

    public function updated(Model $model): void
    {
        // getOriginal() se consulta antes de que Eloquent sincronice, así que
        // recuperamos el estado previo desde los cambios + atributos actuales.
        $changes = AdminActivityNotifier::describeChanges($model, $this->original($model));

        $this->notifier->record('updated', $model, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->notifier->record('deleted', $model);
    }

    /**
     * Reconstruye el estado previo de los atributos modificados.
     *
     * @return array<string, mixed>
     */
    private function original(Model $model): array
    {
        $original = [];

        foreach (array_keys($model->getChanges()) as $attribute) {
            $original[$attribute] = $model->getOriginal($attribute);
        }

        return $original;
    }
}
