import { defineStore } from 'pinia';
import { productionApi } from '../services/productionApi';

export const useProductionStore = defineStore('production', {
  state: () => ({
    orders: [],
    currentOrder: null,
    statistics: {
      total_orders: 0,
      pending: 0,
      in_progress: 0,
      completed: 0,
      delayed: 0,
      on_hold: 0,
      critical_issues: 0
    },
    batches: [],
    issues: [],
    loading: false,
    error: null
  }),

  getters: {
    ordersByStatus: (state) => (status) => {
      return state.orders.filter(order => order.status === status);
    },

    urgentOrders: (state) => {
      return state.orders.filter(order => 
        order.priority === 'urgent' || order.priority === 'high'
      );
    },

    delayedOrders: (state) => {
      return state.orders.filter(order => {
        if (!order.estimated_completion_at) return false;
        const now = new Date();
        const estimated = new Date(order.estimated_completion_at);
        return estimated < now && !['completed', 'delivered', 'cancelled'].includes(order.status);
      });
    },

    criticalIssues: (state) => {
      return state.issues.filter(issue => 
        issue.severity === 'critical' && issue.status !== 'resolved'
      );
    }
  },

  actions: {
    async fetchOrders(filters = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await productionApi.getOrders(filters);
        this.orders = response.data.data || response.data;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch orders';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchOrder(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await productionApi.getOrder(id);
        this.currentOrder = response.data;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch order';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Alias for fetchOrder
    async fetchOrderDetails(id) {
      return this.fetchOrder(id);
    },

    async createOrder(orderData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await productionApi.createOrder(orderData);
        await this.fetchOrders();
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create order';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateOrder(id, orderData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await productionApi.updateOrder(id, orderData);
        await this.fetchOrders();
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update order';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteOrder(id) {
      this.loading = true;
      this.error = null;
      try {
        await productionApi.deleteOrder(id);
        await this.fetchOrders();
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete order';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async startProduction(id, materials) {
      this.loading = true;
      this.error = null;
      try {
        const response = await productionApi.startProduction(id, materials);
        await this.fetchOrder(id);
        await this.fetchStatistics();
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to start production';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateStatus(id, statusData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await productionApi.updateStatus(id, statusData);
        await this.fetchOrder(id);
        await this.fetchStatistics();
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update status';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async reportIssue(id, issueData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await productionApi.reportIssue(id, issueData);
        await this.fetchOrder(id);
        await this.fetchIssues();
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to report issue';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createBatch(id, batchData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await productionApi.createBatch(id, batchData);
        await this.fetchOrder(id);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create batch';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async shipToWarehouse(id, shipmentData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await productionApi.shipToWarehouse(id, shipmentData);
        await this.fetchOrder(id);
        await this.fetchStatistics();
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to ship to warehouse';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchStatistics() {
      try {
        const response = await productionApi.getStatistics();
        this.statistics = response.data;
        return response.data;
      } catch (error) {
        console.error('Failed to fetch statistics:', error);
      }
    },

    async fetchBatches(filters = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await productionApi.getBatches(filters);
        this.batches = response.data;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch batches';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateBatchStatus(id, statusData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await productionApi.updateBatchStatus(id, statusData);
        await this.fetchBatches();
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update batch status';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchIssues(filters = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await productionApi.getIssues(filters);
        this.issues = response.data;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch issues';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async resolveIssue(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await productionApi.resolveIssue(id);
        await this.fetchIssues();
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to resolve issue';
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});
