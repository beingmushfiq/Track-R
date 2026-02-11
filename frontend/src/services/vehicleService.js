import api from './api';

export const vehicleService = {
  // Get all vehicles
  async getAll(params = {}) {
    const response = await api.get('/vehicles', { params });
    return response.data;
  },

  // Get single vehicle
  async getById(id) {
    const response = await api.get(`/vehicles/${id}`);
    return response.data;
  },

  // Create vehicle
  async create(vehicleData) {
    const response = await api.post('/vehicles', vehicleData);
    return response.data;
  },

  // Update vehicle
  async update(id, vehicleData) {
    const response = await api.put(`/vehicles/${id}`, vehicleData);
    return response.data;
  },

  // Delete vehicle
  async delete(id) {
    const response = await api.delete(`/vehicles/${id}`);
    return response.data;
  },

  // Get vehicle location history
  async getLocationHistory(id, params = {}) {
    const response = await api.get(`/vehicles/${id}/locations`, { params });
    return response.data;
  },

  // Get vehicle stats
  async getStats(id, params = {}) {
    const response = await api.get(`/vehicles/${id}/stats`, { params });
    return response.data;
  },
};

export default vehicleService;
