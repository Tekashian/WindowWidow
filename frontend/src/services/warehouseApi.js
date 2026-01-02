import axios from 'axios';

const API_BASE = 'http://localhost:8000/api';

// Get token from localStorage
const getAuthHeaders = () => {
  const token = localStorage.getItem('authToken');
  return token ? { Authorization: `Bearer ${token}` } : {};
};

export const warehouseApi = {
  // Deliveries
  getDeliveries(filters = {}) {
    return axios.get(`${API_BASE}/warehouse/deliveries`, {
      params: filters,
      headers: getAuthHeaders()
    });
  },

  getDelivery(id) {
    return axios.get(`${API_BASE}/warehouse/deliveries/${id}`, {
      headers: getAuthHeaders()
    });
  },

  shipDelivery(id) {
    return axios.post(`${API_BASE}/warehouse/deliveries/${id}/ship`, {}, {
      headers: getAuthHeaders()
    });
  },

  receiveDelivery(id, notes = null) {
    return axios.post(`${API_BASE}/warehouse/deliveries/${id}/receive`, { notes }, {
      headers: getAuthHeaders()
    });
  },

  rejectDelivery(id, notes) {
    return axios.post(`${API_BASE}/warehouse/deliveries/${id}/reject`, { notes }, {
      headers: getAuthHeaders()
    });
  },

  getStatistics() {
    return axios.get(`${API_BASE}/warehouse/deliveries/statistics`, {
      headers: getAuthHeaders()
    });
  }
};
