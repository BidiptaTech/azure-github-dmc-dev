import React, { useState } from "react";
import "./login.css";
import { useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { setName, setEmail1, setAuthenticated, setAgentId } from "./loginSlice";
import { ToastContainer, toast } from "react-toastify";
// import { AiOutlineEye, AiOutlineEyeInvisible } from "react-icons/ai";
import { loginUser } from "../../slice/common/authSlices";
import { setUserRole } from "@/slice/common/authSlices";
import Visibility from "@mui/icons-material/Visibility";
import VisibilityOff from "@mui/icons-material/VisibilityOff";
import { resetPackages } from "@/slice/tour-packages/prePackagesSlice";

function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  // const [showDemoOptions, setShowDemoOptions] = useState(false);

  const navigate = useNavigate();
  const dispatch = useDispatch();

  // ✅ Extract loginStatus and loginError from the Redux store
  const { loginStatus, loginError } = useSelector((state) => state.auth);

  // Helper for testing different roles
  // const setDemoRole = (role) => {
  //   dispatch(setUserRole(role));
  //   toast.info(`Role set to "${role}" for demo purposes`);
  // };

  const handleLogin = async (e) => {
    e.preventDefault();
    dispatch(resetPackages());
    const result = await dispatch(loginUser({ email, password }));

    if (loginUser.fulfilled.match(result)) {
      const {
        agent_id: agentId,
        userRole, // Extract userRole from response
      } = result.payload;

      dispatch(setAgentId(agentId));
      dispatch(setName(email.split("@")[0]));
      dispatch(setEmail1(email));
      dispatch(setAuthenticated(true));

      toast.success("Login successful! Redirecting...");

      // Log the user role for debugging
      // console.log("User logged in with role:", userRole);

      setTimeout(() => navigate("/dashboard/db-dashboard"), 1000);
    } else {
      toast.error(loginError || "Invalid credentials.");
    }
  };

  return (
    <div className="o-scroll" id="js-scroll">
      <div id="main-wrapper" className="main-wrapper overflow-hidden">
        <div className="page-content m-0">
          <section className="signup bg-white">
            <div className="row align-items-center justify-content-center form-row">
              <div className="col-lg-5 col-md-9 col-sm-10 p-0">
                <div className="container-fluid">
                  <div className="form-block login-form">
                    <div
                      className="logo"
                      style={{
                       
                        width: "100%",
                        display: "flex",
                        justifyContent: "center",
                      }}
                    >
                      <img
                        src="/Images/travclicklogo.jpeg"
                        alt="Logo"
                        style={{ width: "100%", height: "auto" }} // Or height: "80px" if you want fixed height
                      />
                    </div>
                    <h2 className="mb-30 light-black"></h2>
                    <h5 className="mb-24 text-center">
                      Sign up with your email address
                    </h5>

                    <form
                      onSubmit={handleLogin}
                      className="form-group contact-form"
                    >
                      <div className="row">
                        <div className="col-sm-12">
                          <div className="mb-24">
                            <input
                              type="email"
                              className="form-control"
                              value={email}
                              onChange={(e) => setEmail(e.target.value)}
                              required
                              placeholder="Email"
                            />
                          </div>
                        </div>

                        <div className="col-sm-12">
                          <div className="mb-24 position-relative">
                            <input
                              type={showPassword ? "text" : "password"}
                              className="form-control"
                              value={password}
                              onChange={(e) => setPassword(e.target.value)}
                              required
                              placeholder="Password"
                            />
                            <button
                              type="button"
                              className="eye-icon-btn"
                              onClick={() => setShowPassword((prev) => !prev)}
                              aria-label="Toggle password visibility"
                            >
                              {showPassword ? (
                                <VisibilityOff fontSize="small" />
                              ) : (
                                <Visibility fontSize="small" />
                              )}
                            </button>
                          </div>
                        </div>

                        <div className="d-flex justify-content-end">
                          <button
                            type="submit"
                            className="cus-btn small-pad mb-24"
                            disabled={loginStatus === "loading"}
                          >
                            {loginStatus === "loading"
                              ? "Logging in..."
                              : "Login"}
                          </button>
                        </div>
                      </div>
                    </form>

                    {loginError && (
                      <div className="col-sm-12 mt-2">
                        <div className="alert alert-danger">{loginError}</div>
                      </div>
                    )}
                  </div>
                </div>
              </div>

              <div className="col-lg-7 p-0">
                <div className="img-block">
                  <img
                    src="/Images/travel-concept-with-baggage.jpg"
                    alt="Travel concept"
                  />
                </div>
              </div>
            </div>
          </section>
        </div>
      </div>
      <ToastContainer />
    </div>
  );
}

export default Login;
