import { create } from 'zustand';
import { trackingService } from '../services';

export const useTrackingStore = create((set, get) => ({
  positions: [],
  history: [],
  isLoading: false,
  error: null,

  // Fetch latest positions for all vehicles
  fetchLatestPositions: async (params = {}) => {
    set({ isLoading: true, error: null });
    try {
      const data = await trackingService.getLatestPositions(params);
      set({ positions: data.data || data, isLoading: false });
    } catch (error) {
      set({ error: error.message, isLoading: false });
    }
  },

  // Fetch position history for a device
  fetchHistory: async (deviceId, params = {}) => {
    set({ isLoading: true, error: null });
    try {
      const data = await trackingService.getHistory(deviceId, params);
      set({ history: data.data || data, isLoading: false });
    } catch (error) {
      set({ error: error.message, isLoading: false });
    }
  },

  // Get live data for specific vehicles
  fetchLiveData: async (vehicleIds = []) => {
    set({ isLoading: true, error: null });
    try {
      const data = await trackingService.getLiveData(vehicleIds);
      set({ positions: data.data || data, isLoading: false });
    } catch (error) {
      set({ error: error.message, isLoading: false });
    }
  },

  // Clear history
  clearHistory: () => set({ history: [] }),
}));

export default useTrackingStore;
