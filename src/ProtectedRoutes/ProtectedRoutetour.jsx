// ProtectedRoute.js
import { Navigate, useParams, useLocation } from "react-router-dom";
import { useSelector } from "react-redux";

// ProtectedRoute to guard access
const ProtectedRoutetour = ({ children }) => {
  const { tourId } = useSelector((state) => state.auth);
  const { id } = useParams();
  const location = useLocation();

  // Allow access when navigating to listing without an existing tour (id === '0')
  if (!tourId) {
    // Allow all service pages without tour until first booking is made
    const path = location?.pathname || "";
    const allowedPaths = [
      "/view-hotel-search/",
      "/hotel-details",
      "/hotel-checkout",
      "/thank-you",
      "/attractions",
      "/restaurants",
      "/pickupdrop",
      "/localtransfer",
      "/tourguide",
      "/tour-single/",
      "/restaurants-details/",
      "/activity-single",
      "/restaurants-checkout",
      "/attraction-checkout",
      "/restaurants-thank-you",
      "/attraction-thank-you",
      "/CheckOut",
      "/ThankYou"
    ];
    
    if (
      id === "0" ||
      allowedPaths.some(allowedPath => path.includes(allowedPath))
    ) {
      return children;
    }

    console.log("Redirecting to dashboard due to missing tourId.");
    return <Navigate to="/dashboard/db-dashboard" replace />;
  }

  return children;
};

export default ProtectedRoutetour;
