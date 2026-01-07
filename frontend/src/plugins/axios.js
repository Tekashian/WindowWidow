import axios from 'axios';

// Set base URL
axios.defaults.baseURL = 'http://localhost:8000/api';

// Add request interceptor to include auth token
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('authToken');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    // Debug log
    console.log('🔐 Axios Request:', {
      url: config.url,
      hasToken: !!token,
      headers: config.headers
    });
    
    return config;
  },
  (error) => {
    console.error('❌ Axios Request Error:', error);
    return Promise.reject(error);
  }
);

// Add response interceptor to handle 401 errors
axios.interceptors.response.use(
  (response) => {
    // Debug log
    console.log('✅ Axios Response:', response.config.url, response.status);
    return response;
  },
  (error) => {
    console.error('❌ Axios Response Error:', {
      url: error.config?.url,
      status: error.response?.status,
      data: error.response?.data
    });
    
    if (error.response && error.response.status === 401) {
      // Token expired or invalid - redirect to login
      console.warn('🚨 401 Unauthorized - Redirecting to login');
      localStorage.removeItem('authToken');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default axios;
