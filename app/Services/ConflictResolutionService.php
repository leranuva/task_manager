<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\DB;

class ConflictResolutionService
{
    /**
     * Resolver conflictos de edición simultánea usando Last-Write-Wins con merge inteligente
     */
    public function resolveTaskConflict(Task $task, array $incomingData, int $version): array
    {
        $currentData = $task->getAttributes();
        $resolved = [];

        // Estrategia: Last-Write-Wins para campos simples, merge para campos complejos
        foreach ($incomingData as $field => $value) {
            // Campos que siempre aceptan el último cambio
            $simpleFields = ['title', 'priority', 'due_date', 'assigned_to', 'status_id'];
            
            if (in_array($field, $simpleFields)) {
                $resolved[$field] = $value;
            } 
            // Descripción: merge inteligente
            elseif ($field === 'description') {
                $resolved[$field] = $this->mergeDescription($currentData['description'] ?? '', $value);
            }
            // Otros campos: mantener el actual si no hay conflicto
            else {
                $resolved[$field] = $currentData[$field] ?? $value;
            }
        }

        return $resolved;
    }

    /**
     * Merge inteligente de descripciones
     */
    protected function mergeDescription(string $current, string $incoming): string
    {
        // Si ambas son iguales, mantener la actual
        if ($current === $incoming) {
            return $current;
        }

        // Si la actual está vacía, usar la nueva
        if (empty(trim($current))) {
            return $incoming;
        }

        // Si la nueva está vacía, mantener la actual
        if (empty(trim($incoming))) {
            return $current;
        }

        // Si son diferentes, usar la más reciente (incoming)
        // En producción, podrías implementar un merge más sofisticado
        return $incoming;
    }

    /**
     * Verificar si hay conflicto de versión
     */
    public function hasConflict(Task $task, int $clientVersion): bool
    {
        // Obtener la versión actual del servidor (basada en updated_at)
        $serverVersion = $task->updated_at->timestamp;
        
        // Si la versión del cliente es menor, hay conflicto
        return $clientVersion < $serverVersion;
    }

    /**
     * Obtener información de conflicto
     */
    public function getConflictInfo(Task $task, array $incomingData): array
    {
        $currentData = $task->getAttributes();
        $conflicts = [];

        foreach ($incomingData as $field => $value) {
            if (isset($currentData[$field]) && $currentData[$field] != $value) {
                $conflicts[$field] = [
                    'current' => $currentData[$field],
                    'incoming' => $value,
                ];
            }
        }

        return [
            'has_conflict' => !empty($conflicts),
            'conflicts' => $conflicts,
            'server_version' => $task->updated_at->timestamp,
        ];
    }
}

