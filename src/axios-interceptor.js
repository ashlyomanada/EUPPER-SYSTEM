// axios-interceptor.js
import axios from "axios";

const apiClient = axios.create({
  baseURL: "https://your-api-url.com",
  timeout: 5000, // Set a reasonable timeout
});

apiClient.interceptors.request.use(
  (config) => {
    // Trigger loader before the request
    return config;
  },
  (error) => {
    // Handle request errors
    return Promise.reject(error);
  }
);

apiClient.interceptors.response.use(
  (response) => {
    // Hide loader on response
    return response;
  },
  (error) => {
    if (error.code === "ECONNABORTED") {
      console.log("Slow network detected");
      // Handle slow network or timeout here
    }
    // Hide loader on error
    return Promise.reject(error);
  }
);

export default apiClient;
