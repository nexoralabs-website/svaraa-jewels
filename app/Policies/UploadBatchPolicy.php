<?php

namespace App\Policies;

use App\Models\UploadBatch;
use App\Models\User;
use App\Services\BulkUploadAuditService;

class UploadBatchPolicy
{
    /**
     * Helper method to check if user is admin
     */
    private function isAdmin(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Helper method to record access denied event
     */
    private function recordDenied(User $user, string $action, ?array $metadata = null): void
    {
        BulkUploadAuditService::record(
            batchUuid: 'N/A',
            eventType: 'access_denied',
            actorId: $user->id,
            metadata: array_merge([
                'action' => $action,
            ], $metadata ?? []),
        );
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        $allowed = $this->isAdmin($user);
        if (!$allowed) {
            $this->recordDenied($user, 'viewAny');
        }
        return $allowed;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UploadBatch $uploadBatch): bool
    {
        $allowed = $this->isAdmin($user);
        if (!$allowed) {
            $this->recordDenied($user, 'view', ['batch_uuid' => $uploadBatch->id]);
        }
        return $allowed;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $allowed = $this->isAdmin($user);
        if (!$allowed) {
            $this->recordDenied($user, 'create');
        }
        return $allowed;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UploadBatch $uploadBatch): bool
    {
        $allowed = $this->isAdmin($user);
        if (!$allowed) {
            $this->recordDenied($user, 'update', ['batch_uuid' => $uploadBatch->id]);
        }
        return $allowed;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UploadBatch $uploadBatch): bool
    {
        $allowed = $this->isAdmin($user);
        if (!$allowed) {
            $this->recordDenied($user, 'delete', ['batch_uuid' => $uploadBatch->id]);
        }
        return $allowed;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UploadBatch $uploadBatch): bool
    {
        $allowed = $this->isAdmin($user);
        if (!$allowed) {
            $this->recordDenied($user, 'restore', ['batch_uuid' => $uploadBatch->id]);
        }
        return $allowed;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UploadBatch $uploadBatch): bool
    {
        $allowed = $this->isAdmin($user);
        if (!$allowed) {
            $this->recordDenied($user, 'forceDelete', ['batch_uuid' => $uploadBatch->id]);
        }
        return $allowed;
    }

    /**
     * Determine whether the user can publish the batch.
     */
    public function publish(User $user): bool
    {
        $allowed = $this->isAdmin($user);
        if (!$allowed) {
            $this->recordDenied($user, 'publish');
        }
        return $allowed;
    }

    /**
     * Determine whether the user can retry failed batches.
     */
    public function retry(User $user): bool
    {
        $allowed = $this->isAdmin($user);
        if (!$allowed) {
            $this->recordDenied($user, 'retry');
        }
        return $allowed;
    }

    /**
     * Determine whether the user can run cleanup actions.
     */
    public function cleanup(User $user): bool
    {
        $allowed = $this->isAdmin($user);
        if (!$allowed) {
            $this->recordDenied($user, 'cleanup');
        }
        return $allowed;
    }

    /**
     * Determine whether the user can access the operations dashboard.
     */
    public function dashboardAccess(User $user): bool
    {
        $allowed = $this->isAdmin($user);
        if (!$allowed) {
            $this->recordDenied($user, 'dashboardAccess');
        }
        return $allowed;
    }
}
