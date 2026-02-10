<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeviceService
{
    /**
     * Get paginated list of devices with optional filtering.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Device::query()
            ->when(isset($filters['search']), function (Builder $query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('imei', 'like', "%{$search}%")
                        ->orWhere('sim_number', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%"); // If name exists? distinct from Vehicle name
                });
            })
            ->when(isset($filters['vehicle_id']), function (Builder $query, $vehicleId) {
                $query->where('vehicle_id', $vehicleId);
            })
            ->when(isset($filters['model_id']), function (Builder $query, $modelId) {
                $query->where('device_model_id', $modelId);
            })
            ->when(isset($filters['status']), function (Builder $query, $status) {
                $query->where('status', $status);
            })
            ->when(isset($filters['is_online']), function (Builder $query, $isOnline) {
                $query->where('is_online', filter_var($isOnline, FILTER_VALIDATE_BOOLEAN));
            })
            ->with(['vehicle', 'model'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new device.
     *
     * @param array $data
     * @return Device
     */
    public function create(array $data): Device
    {
        return DB::transaction(function () use ($data) {
            // Ensure tenant_id is set
            if (!isset($data['tenant_id'])) {
                $data['tenant_id'] = auth()->user()->tenant_id;
            }

            // Check if vehicle belongs to tenant if set
            if (isset($data['vehicle_id'])) {
                $this->validateVehicleOwnership($data['vehicle_id'], $data['tenant_id']);
            }

            return Device::create($data);
        });
    }

    /**
     * Update an existing device.
     *
     * @param Device $device
     * @param array $data
     * @return Device
     */
    public function update(Device $device, array $data): Device
    {
        return DB::transaction(function () use ($device, $data) {
            // Check if vehicle belongs to tenant if changing
            if (isset($data['vehicle_id']) && $data['vehicle_id'] != $device->vehicle_id) {
                $this->validateVehicleOwnership($data['vehicle_id'], $device->tenant_id);
            }

            $device->update($data);
            return $device->fresh(['vehicle', 'model']);
        });
    }

    /**
     * Delete a device.
     *
     * @param Device $device
     * @return bool
     */
    public function delete(Device $device): bool
    {
        return DB::transaction(function () use ($device) {
            // Logic to handle historical data? For now soft delete is handled by model
            return $device->delete();
        });
    }

    /**
     * Get device models for dropdowns.
     *
     * @return Collection
     */
    public function getModels(): Collection
    {
        return DeviceModel::all();
    }

    /**
     * Validate that the vehicle belongs to the tenant.
     *
     * @param int $vehicleId
     * @param int $tenantId
     * @throws ValidationException
     */
    protected function validateVehicleOwnership(int $vehicleId, int $tenantId): void
    {
        $exists = DB::table('vehicles')
            ->where('id', $vehicleId)
            ->where('tenant_id', $tenantId)
            ->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'vehicle_id' => ['The selected vehicle does not belong to your account.']
            ]);
        }
    }
}
