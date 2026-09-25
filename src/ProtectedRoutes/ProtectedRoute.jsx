// // ProtectedRoute.js
// import { Navigate } from "react-router-dom";
// import { useSelector } from "react-redux";

// // ProtectedRoute to guard access
// const ProtectedRoute = ({ children }) => {
//   const { isAuthenticated } = useSelector((state) => state.auth);

//   if (!isAuthenticated) {
//     console.log("Redirecting from ProtectedRoute...");
//     return <Navigate to="/login" replace />
//   }

//   return <>{children}</>; // Ensures valid JSX return
// };

// export default ProtectedRoute;

import { Navigate, useLocation } from "react-router-dom";
import { useSelector, useDispatch } from "react-redux";
import { useEffect } from "react";
import { logoutUser } from "../slice/common/authSlices";

const ProtectedRoute = ({ children }) => {
  const { isAuthenticated } = useSelector((state) => state.auth);
  const storedAuth = localStorage.getItem("isAuthenticated");

  // Convert to boolean
  const finalAuthStatus = isAuthenticated ?? storedAuth === "true";

  // console.log("isAuthenticated:", finalAuthStatus);
  const dispatch = useDispatch();
  const location = useLocation();

  useEffect(() => {
    if (!isAuthenticated) {
      // console.log("Before dispatch........");
      dispatch(logoutUser());
    }
  }, [isAuthenticated, dispatch]);

  if (!isAuthenticated) {
    // console.log("Redirecting to login...");
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  return <>{children}</>;
};

export default ProtectedRoute;
