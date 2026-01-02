import { defineStore } from 'pinia';
import { warehouseApi } from '../services/warehouseApi';

export const useWarehouseStore = defineStore('warehouse', {
  state: () => ({
    deliveries: [],
    currentDelivery: null,
    statistics: {
      pending: 0,
      in_transit: 0,
      delayed: 0,
      delivered_today: 0
    },
    loading: false,
    error: null
  }),

  getters: {
    pendingDeliveries: (state) => {
      return state.deliveries.filter(d => d.status === 'pending');
    },

    inTransitDeliveries: (state) => {
      return state.deliveries.filter(d => d.status === 'in_transit');
    },

    delayedDeliveries: (state) => {
      return state.deliveries.filter(d => {
        if (d.status !== 'pending') return false;
        const now = new Date();
        const expected = new Date(d.expected_delivery_date);
        return expected < now;
      });
    },

    todayDeliveries: (state) => {
      const today = new Date().toISOString().split('T')[0];
      return state.deliveries.filter(d => {
        const deliveryDate = d.expected_delivery_date?.split('T')[0];
        return deliveryDate === today;
      });
    }
  },

  actions: {
    async fetchDeliveries(filters = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await warehouseApi.getDeliveries(filters);
        this.deliveries = response.data;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch deliveries';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchDelivery(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await warehouseApi.getDelivery(id);
        this.currentDelivery = response.data;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch delivery';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async shipDelivery(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await warehouseApi.shipDelivery(id);
        await this.fetchDeliveries();
        await this.fetchStatistics();
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to ship delivery';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async receiveDelivery(id, notes = null) {
      this.loading = true;
      this.error = null;
      try {
        const response = await warehouseApi.receiveDelivery(id, notes);
        await this.fetchDeliveries();
        await this.fetchStatistics();
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to receive delivery';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async rejectDelivery(id, notes) {
      this.loading = true;
      this.error = null;
      try {
        const response = await warehouseApi.rejectDelivery(id, notes);
        await this.fetchDeliveries();
        await this.fetchStatistics();
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to reject delivery';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchStatistics() {
      try {
        const response = await warehouseApi.getStatistics();
        this.statistics = response.data;
        return response.data;
      } catch (error) {
        console.error('Failed to fetch statistics:', error);
      }
    }
  }
});
