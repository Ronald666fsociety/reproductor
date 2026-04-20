<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityObserver
{
    public function created(Model $model)
    {
        $this->logActivity($model, 'created');
    }

    public function updated(Model $model)
    {
        $this->logActivity($model, 'updated');
    }

    public function deleted(Model $model)
    {
        $action = method_exists($model, 'isForceDeleting') && $model->isForceDeleting() ? 'force_deleted' : 'deleted';
        $this->logActivity($model, $action);
    }

    public function restored(Model $model)
    {
        $this->logActivity($model, 'restored');
    }

    protected function logActivity(Model $model, $action)
    {
        if (!auth()->check()) return;

        $description = $this->generateDescription($model, $action);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => get_class($model),
            'subject_id' => $model->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected function generateDescription(Model $model, $action)
    {
        $name = $model->name ?? $model->title ?? 'Objeto #' . $model->id;
        $type = class_basename($model);
        
        $actions = [
            'created' => "ha creado el/la $type: $name",
            'updated' => "ha modificado el/la $type: $name",
            'deleted' => "ha movido a la papelera el/la $type: $name",
            'force_deleted' => "ha eliminado permanentemente el/la $type: $name",
            'restored' => "ha restaurado el/la $type: $name",
        ];

        return $actions[$action] ?? "ha realizado la acción $action sobre $type: $name";
    }
}
