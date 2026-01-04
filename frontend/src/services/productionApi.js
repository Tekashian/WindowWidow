import axios from 'axios';

const API_BASE = 'http://localhost:8000/api';

// Get token from localStorage
const getAuthHeaders = () => {
  const token = localStorage.getItem('token');
  return token ? { Authorization: `Bearer ${token}` } : {};
};

export const productionApi = {
  // Helper endpoints
  getProducts() {
    return axios.get(`${API_BASE}/production/products`, {
      headers: getAuthHeaders()
    });
  },

  getCompanySettings() {
    return axios.get(`${API_BASE}/production/company-settings`, {
      headers: getAuthHeaders()
    });
  },

  // Production Orders
  getOrders(filters = {}) {
    return axios.get(`${API_BASE}/production/orders`, {
      params: filters,
      headers: getAuthHeaders()
    });
  },

  getOrder(id) {
    return axios.get(`${API_BASE}/production/orders/${id}`, {
      headers: getAuthHeaders()
    });
  },

  createOrder(data) {
    return axios.post(`${API_BASE}/production/orders`, data, {
      headers: getAuthHeaders()
    });
  },

  updateOrder(id, data) {
    return axios.put(`${API_BASE}/production/orders/${id}`, data, {
      headers: getAuthHeaders()
    });
  },

  deleteOrder(id) {
    return axios.delete(`${API_BASE}/production/orders/${id}`, {
      headers: getAuthHeaders()
    });
  },

  startProduction(id, productionData) {
    return axios.post(`${API_BASE}/production/orders/${id}/start`, productionData, {
      headers: getAuthHeaders()
    });
  },

  updateStatus(id, statusData) {
    return axios.post(`${API_BASE}/production/orders/${id}/update-status`, statusData, {
      headers: getAuthHeaders()
    });
  },

  reportIssue(id, issueData) {
    return axios.post(`${API_BASE}/production/orders/${id}/report-issue`, issueData, {
      headers: getAuthHeaders()
    });
  },

  createBatch(id, batchData) {
    return axios.post(`${API_BASE}/production/orders/${id}/create-batch`, batchData, {
      headers: getAuthHeaders()
    });
  },

  shipToWarehouse(id, shipmentData) {
    return axios.post(`${API_BASE}/production/orders/${id}/ship-to-warehouse`, shipmentData, {
      headers: getAuthHeaders()
    });
  },

  getStatistics() {
    return axios.get(`${API_BASE}/production/orders/statistics`, {
      headers: getAuthHeaders()
    });
  },

  // Batches
  getBatches(filters = {}) {
    return axios.get(`${API_BASE}/production/batches`, {
      params: filters,
      headers: getAuthHeaders()
    });
  },

  getBatch(id) {
    return axios.get(`${API_BASE}/production/batches/${id}`, {
      headers: getAuthHeaders()
    });
  },

  updateBatchStatus(id, statusData) {
    return axios.post(`${API_BASE}/production/batches/${id}/update-status`, statusData, {
      headers: getAuthHeaders()
    });
  },

  // Issues
  getIssues(filters = {}) {
    return axios.get(`${API_BASE}/production/issues`, {
      params: filters,
      headers: getAuthHeaders()
    });
  },

  getIssue(id) {
    return axios.get(`${API_BASE}/production/issues/${id}`, {
      headers: getAuthHeaders()
    });
  },

  updateIssueStatus(id, statusData) {
    return axios.post(`${API_BASE}/production/issues/${id}/update-status`, statusData, {
      headers: getAuthHeaders()
    });
  },

  resolveIssue(id) {
    return axios.post(`${API_BASE}/production/issues/${id}/resolve`, {}, {
      headers: getAuthHeaders()
    });
  },

  getIssueStatistics() {
    return axios.get(`${API_BASE}/production/issues/statistics`, {
      headers: getAuthHeaders()
    });
  }
};
