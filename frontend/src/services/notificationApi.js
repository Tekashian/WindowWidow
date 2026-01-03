import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

export default {
  /**
   * Get all notifications
   */
  async getNotifications(params = {}) {
    const token = localStorage.getItem('token');
    const response = await axios.get(`${API_URL}/notifications`, {
      headers: { Authorization: `Bearer ${token}` },
      params
    });
    return response.data;
  },

  /**
   * Get unread count
   */
  async getUnreadCount() {
    const token = localStorage.getItem('token');
    const response = await axios.get(`${API_URL}/notifications/unread-count`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },

  /**
   * Mark notification as read
   */
  async markAsRead(id) {
    const token = localStorage.getItem('token');
    const response = await axios.post(`${API_URL}/notifications/${id}/mark-read`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },

  /**
   * Mark all notifications as read
   */
  async markAllAsRead() {
    const token = localStorage.getItem('token');
    const response = await axios.post(`${API_URL}/notifications/mark-all-read`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },

  /**
   * Delete notification
   */
  async deleteNotification(id) {
    const token = localStorage.getItem('token');
    const response = await axios.delete(`${API_URL}/notifications/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },

  /**
   * Delete all read notifications
   */
  async deleteAllRead() {
    const token = localStorage.getItem('token');
    const response = await axios.delete(`${API_URL}/notifications/read/all`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  }
};
