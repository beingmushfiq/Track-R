<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleGroup;
use App\Models\VehicleType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VehicleService
{
    /**
     * Get paginated list of vehicles with optional filtering.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Vehicle::query()
            ->when(isset($filters['search']), function (Builder $query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%")
                        ->orWhere('vin', 'like', "%{$search}%")
                        ->orWhere('driver_name', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['group_id']), function (Builder $query, $groupId) {
                $query->where('vehicle_group_id', $groupId);
            })
            ->when(isset($filters['type_id']), function (Builder $query, $typeId) {
                $query->where('vehicle_type_id', $typeId);
            })
            ->when(isset($filters['status']), function (Builder $query, $status) {
                $query->where('status', $status);
            })
            ->with(['type', 'group', 'device'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new vehicle.
     *
     * @param array $data
     * @return Vehicle
     */
    public function create(array $data): Vehicle
    {
        return DB::transaction(function () use ($data) {
            // Ensure tenant_id is set (usually handled by Global Scope/Observer, but safe to add)
            if (!isset($data['tenant_id'])) {
                $data['tenant_id'] = auth()->user()->tenant_id;
            }

            return Vehicle::create($data);
        });
    }

    /**
     * Update an existing vehicle.
     *
     * @param Vehicle $vehicle
     * @param array $data
     * @return Vehicle
     */
    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        return DB::transaction(function () use ($vehicle, $data) {
            $vehicle->update($data);
            return $vehicle->fresh(['type', 'group', 'device']);
        });
    }

    /**
     * Delete a vehicle.
     *
     * @param Vehicle $vehicle
     * @return bool
     */
    public function delete(Vehicle $vehicle): bool
    {
        return DB::transaction(function () use ($vehicle) {
            // Logic to detach devices or handle related data can go here
            return $vehicle->delete();
        });
    }

    /**
     * Get vehicle types for dropdowns.
     *
     * @return Collection
     */
    public function getTypes(): Collection
    {
        return VehicleType::all();
    }

    /**
     * Get vehicle groups for dropdowns (tenant scoped).
     *
     * @return Collection
     */
    public function getGroups(): Collection
    {
        // VehicleGroup should also have BelongsToTenant trait
        return VehicleGroup::all();
    }
}
