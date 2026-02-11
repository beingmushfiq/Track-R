import api from './api';

export const authService = {
  // Login
  async login(email, password) {
    const response = await api.post('/auth/login', { email, password });
    return response.data;
  },

  // Register
  async register(userData) {
    const response = await api.post('/auth/register', userData);
    return response.data;
  },

  // Logout
  async logout() {
    const response = await api.post('/auth/logout');
    return response.data;
  },

  // Get current user
  async me() {
    const response = await api.get('/auth/me');
    return response.data;
  },

  // Refresh token
  async refresh() {
    const response = await api.post('/auth/refresh');
    return response.data;
  },
};

export default authService;
