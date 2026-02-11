import { create } from 'zustand';
import { vehicleService } from '../services';

export const useVehicleStore = create((set, get) => ({
  vehicles: [],
  selectedVehicle: null,
  isLoading: false,
  error: null,

  // Fetch all vehicles
  fetchVehicles: async (params = {}) => {
    set({ isLoading: true, error: null });
    try {
      const data = await vehicleService.getAll(params);
      set({ vehicles: data.data || data, isLoading: false });
    } catch (error) {
      set({ error: error.message, isLoading: false });
    }
  },

  // Fetch single vehicle
  fetchVehicle: async (id) => {
    set({ isLoading: true, error: null });
    try {
      const data = await vehicleService.getById(id);
      set({ selectedVehicle: data.data || data, isLoading: false });
    } catch (error) {
      set({ error: error.message, isLoading: false });
    }
  },

  // Create vehicle
  createVehicle: async (vehicleData) => {
    set({ isLoading: true, error: null });
    try {
      const data = await vehicleService.create(vehicleData);
      set((state) => ({
        vehicles: [...state.vehicles, data.data || data],
        isLoading: false,
      }));
      return { success: true };
    } catch (error) {
      set({ error: error.message, isLoading: false });
      return { success: false, message: error.message };
    }
  },

  // Update vehicle
  updateVehicle: async (id, vehicleData) => {
    set({ isLoading: true, error: null });
    try {
      const data = await vehicleService.update(id, vehicleData);
      set((state) => ({
        vehicles: state.vehicles.map((v) => (v.id === id ? data.data || data : v)),
        isLoading: false,
      }));
      return { success: true };
    } catch (error) {
      set({ error: error.message, isLoading: false });
      return { success: false, message: error.message };
    }
  },

  // Delete vehicle
  deleteVehicle: async (id) => {
    set({ isLoading: true, error: null });
    try {
      await vehicleService.delete(id);
      set((state) => ({
        vehicles: state.vehicles.filter((v) => v.id !== id),
        isLoading: false,
      }));
      return { success: true };
    } catch (error) {
      set({ error: error.message, isLoading: false });
      return { success: false, message: error.message };
    }
  },

  // Clear selected vehicle
  clearSelectedVehicle: () => set({ selectedVehicle: null }),
}));

export default useVehicleStore;
