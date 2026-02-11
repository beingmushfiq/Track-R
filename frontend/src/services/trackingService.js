import api from './api';

export const trackingService = {
  // Get latest positions for all vehicles
  async getLatestPositions(params = {}) {
    const response = await api.get('/tracking/latest', { params });
    return response.data;
  },

  // Get position history for a device
  async getHistory(deviceId, params = {}) {
    const response = await api.get(`/tracking/history/${deviceId}`, { params });
    return response.data;
  },

  // Get live tracking data (for specific vehicles)
  async getLiveData(vehicleIds = []) {
    const response = await api.post('/tracking/live', { vehicle_ids: vehicleIds });
    return response.data;
  },
};

export const reportService = {
  // Get trip report
  async getTripReport(params = {}) {
    const response = await api.get('/reports/trips', { params });
    return response.data;
  },

  // Get distance report
  async getDistanceReport(params = {}) {
    const response = await api.get('/reports/distance', { params });
    return response.data;
  },

  // Get fuel report
  async getFuelReport(params = {}) {
    const response = await api.get('/reports/fuel', { params });
    return response.data;
  },

  // Get utilization report
  async getUtilizationReport(params = {}) {
    const response = await api.get('/reports/utilization', { params });
    return response.data;
  },

  // Get alert report
  async getAlertReport(params = {}) {
    const response = await api.get('/reports/alerts', { params });
    return response.data;
  },

  // Get daily activity report
  async getDailyActivityReport(params = {}) {
    const response = await api.get('/reports/daily-activity', { params });
    return response.data;
  },
};

export const alertService = {
  // Get all alerts
  async getAll(params = {}) {
    const response = await api.get('/alerts', { params });
    return response.data;
  },

  // Mark alert as read
  async markAsRead(id) {
    const response = await api.put(`/alerts/${id}/read`);
    return response.data;
  },

  // Get alert rules
  async getRules(params = {}) {
    const response = await api.get('/alert-rules', { params });
    return response.data;
  },

  // Create alert rule
  async createRule(ruleData) {
    const response = await api.post('/alert-rules', ruleData);
    return response.data;
  },
};

export { trackingService, reportService, alertService };
