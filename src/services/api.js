import axios from "axios";
import Cookies from "js-cookie";

// Base URL for all API requests
export const BASE_URL = "https://dmcdemo.coactivehub.com/backadm-dmc/api/v1";

// Create an axios instance with default configuration
const api = axios.create({
  baseURL: BASE_URL,
});

// Add request interceptor to automatically include auth headers
api.interceptors.request.use(
  (config) => {
    const authToken = Cookies.get("authToken");
    const AgentId = Cookies.get("AgentId");

    if (authToken) {
      config.headers.Authorization = `Bearer ${authToken}`;
    }

    if (AgentId) {
      config.headers["agent-id"] = AgentId;
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Utility function to transform parameter names from underscore to hyphen format
// This handles the API's requirement for hyphenated parameter names
const transformParams = (params) => {
  if (!params) return params;

  const transformed = {};

  // List of parameters that need to be transformed from underscore to hyphen
  const hyphenParams = [
    { from: "dmc_id", to: "dmc-id" },
    { from: "price_mode", to: "price-mode" },
    { from: "agent_id", to: "agent-id" },
    { from: "tour_id", to: "tour-id" },
  ];

  // Copy all parameters to the new object
  Object.keys(params).forEach((key) => {
    // Check if this key needs to be transformed
    const paramToTransform = hyphenParams.find((param) => param.from === key);

    if (paramToTransform) {
      // Transform the key from underscore to hyphen format
      transformed[paramToTransform.to] = params[key];
    } else {
      // Keep the key as is
      transformed[key] = params[key];
    }
  });

  return transformed;
};

// Export API endpoints as functions
export const endpoints = {
  // Location endpoints
  getCities: (country) => {
    const authToken = Cookies.get("authToken");
    if (!authToken) {
      return Promise.reject(new Error("No auth token found"));
    }

    return api
      .get("/get-cities", {
        headers: {
          Authorization: `Bearer ${authToken}`,
          country: country,
        },
      })
      .then((response) => {
        // Handle different response formats
        if (response.data && typeof response.data === "object") {
          // If response is already structured, return it
          return response;
        } else if (typeof response.data === "string") {
          // If the response is a JSON string, parse it
          try {
            const parsedData = JSON.parse(response.data);
            return { ...response, data: parsedData };
          } catch (e) {
            console.error("Error parsing cities response:", e);
            return response;
          }
        }
        return response;
      });
  },

  // Package endpoints
  fetchPackages: (params) =>
    api.get("/packages", { params: transformParams(params) }),
  fetchPackageDetails: (params) =>
    api.get("/package-details", { params: transformParams(params) }),

  // Hotel endpoints
  fetchHotels: (params) =>
    api.get("/location", { params: transformParams(params) }),
  fetchHotelDetails: (params) =>
    api.get("/hotel-details", { params: transformParams(params) }),

  //dashboard endpoints
  fetchTourDetails: (params, config = {}) => {
    const mergedConfig = {
      ...config,
      params: transformParams(params),
    };
    return api.get("/tour-details", mergedConfig);
  },
  fetchTourList: (params) =>
    api.get("/tour-list", { params: transformParams(params) }),

  // Vehicle endpoints
  fetchVehicles: (params) =>
    api.get("/vehicles-list", { params: transformParams(params) }),
  getVehicleDetails: (params) =>
    api.get("/vehicle-details", { params: transformParams(params) }),

  // Attraction endpoints
  fetchAttractions: (params) =>
    api.get("/attractions", { params: transformParams(params) }),
  getAttractionDetails: (params) =>
    api.get("/attraction-details", { params: transformParams(params) }),

  // Restaurant endpoints
  fetchRestaurants: (params) =>
    api.get("/restaurants", { params: transformParams(params) }),
  getRestaurantDetails: (params) =>
    api.get("/restaurant-details", { params: transformParams(params) }),

  // Guide endpoints
  fetchGuides: (params) =>
    api.get("/tour-guides", { params: transformParams(params) }),
  getGuideDetails: (params) =>
    api.get("/guide-details", { params: transformParams(params) }),

  // Tour endpoints
  getTourDetails: (params) =>
    api.get("/tour-details", { params: transformParams(params) }),
  getEnquiryStatus: (params) =>
    api.get("/enquiry-status", { params: transformParams(params) }),

  // Custom request function for any other endpoints
  request: (method, endpoint, data, config = {}) => {
    // For POST requests, we don't transform the data, only the URL params if any
    if (config.params) {
      config.params = transformParams(config.params);
    }
    return api[method](`${endpoint}`, data, config);
  },
};

// Export the api instance for direct use if needed
export default api;
