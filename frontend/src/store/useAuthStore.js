import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import authService from '../services/authService';

export const useAuthStore = create(
  persist(
    (set) => ({
      user: null,
      token: null,
      isAuthenticated: false,

      login: async (email, password) => {
        try {
          const data = await authService.login(email, password);
          set({ user: data.user, token: data.token, isAuthenticated: true });
          return { success: true };
        } catch (error) {
          return { 
            success: false, 
            message: error.response?.data?.message || 'Login failed' 
          };
        }
      },

      register: async (userData) => {
        try {
          const data = await authService.register(userData);
          set({ user: data.user, token: data.token, isAuthenticated: true });
          return { success: true };
        } catch (error) {
           return { 
            success: false, 
            message: error.response?.data?.message || 'Registration failed' 
          };
        }
      },

      logout: async () => {
        try {
          await authService.logout();
        } catch (e) {
          console.error('Logout failed', e);
        } finally {
          set({ user: null, token: null, isAuthenticated: false });
        }
      },

      checkAuth: async () => {
        try {
          const data = await authService.me();
          set({ user: data.user, isAuthenticated: true });
          return true;
        } catch (error) {
          set({ user: null, token: null, isAuthenticated: false });
          return false;
        }
      }
    }),
    {
      name: 'auth-storage',
      partialize: (state) => ({ token: state.token, user: state.user, isAuthenticated: state.isAuthenticated }),
    }
  )
);
