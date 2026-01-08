// ProtectedRoute.js
import { Navigate } from "react-router-dom";
import { useSelector } from "react-redux";

// ProtectedRoute to guard access
const ProtectedRoutetour = ({ children }) => {
  const { tourId } = useSelector((state) => state.auth);

  if (!tourId) {
    console.log("Redirecting to dashboard due to missing tourId.");
    return <Navigate to="/dashboard/db-dashboard" replace />;
  }

  return children;
};

export default ProtectedRoutetour;
