import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { setName, setEmail1, setAuthenticated, setAgentId } from "./loginSlice";
import { loginUser } from "../../slice/common/authSlices";
import { resetPackages } from "@/slice/tour-packages/prePackagesSlice";
import { setDmcFromAuth } from "../../slice/dmc/dmcSlice";
import { CircularProgress, Alert, IconButton } from "@mui/material";
import {
  EmailOutlined,
  LockOutlined,
  Visibility,
  VisibilityOff,
  VerifiedUserOutlined,
  SupportAgentOutlined,
} from "@mui/icons-material";
import { ClearLists } from "@/slice/common/TourlistSlice";
import { resetAgentList } from "@/slice/common/agentListSlice";
import DestinationNetworkMap from "@/components/login/DestinationNetworkMap";
import AgentOutboundWorkspace from "@/components/login/AgentOutboundWorkspace";
import "@/styles/agent-signin.scss";

function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { loginStatus, loginError } = useSelector((state) => state.auth);

  const handleLogin = async (e) => {
    e.preventDefault();
    setIsLoading(true);
    dispatch(resetPackages());
    dispatch(resetAgentList());
    dispatch(ClearLists());
    const result = await dispatch(loginUser({ email, password }));

    if (loginUser.fulfilled.match(result)) {
      const {
        agent_id: agentId,
        userRole,
        dmcId,
        dmcLogo,
        dmcCompanyName,
      } = result.payload;

      dispatch(setAgentId(agentId));
      dispatch(setName(email.split("@")[0]));
      dispatch(setEmail1(email));
      dispatch(setAuthenticated(true));

      if (dmcId && userRole !== "Agent") {
        dispatch(
          setDmcFromAuth({
            dmcId,
            dmcLogo,
            dmcCompanyName,
          })
        );
      }

      navigate("/app");
    } else {
      setIsLoading(false);
    }
  };

  const busy = isLoading || loginStatus === "loading";

  return (
    <div className="agent-signin-page">
      <div className="agent-signin-layout">
        <section className="agent-map-panel" aria-labelledby="travel-title">
          <div className="agent-map-intro">
            <p className="agent-eyebrow">
              <span /> For outbound travel agents & tour operators
            </p>
            <h2 id="travel-title">
              Build the Journeys.{" "}
              <em>Worth Travelling For & Stay connected</em>
            </h2>
            <p>
              Create FIT and group enquiries, negotiate quotes with DMC partners,
              manage multi-city trips from proposal to pickup, and keep
              travellers informed.
            </p>
          </div>

          <DestinationNetworkMap />
        </section>

        <section className="agent-signin-panel" aria-labelledby="signin-title">
          <div className="agent-signin-shell">
            <div className="agent-partner-card">
              <p className="agent-partner-label">Partner Portal</p>

              <button
                type="button"
                className="agent-back-home"
                aria-label="Back to home"
                onClick={() => navigate("/")}
              >
                ←
              </button>

              <img
                className="agent-partner-logo"
                src="/Images/logo.png"
                alt="Travclicks — Travel Technology Transformed"
                width="222"
                height="88"
              />

              <h1 id="signin-title">Sign In</h1>
              <p className="agent-signin-description">
                Access your Travclicks AI partner dashboard
              </p>

              <form onSubmit={handleLogin}>
                <div className="agent-sample-field">
                  <label htmlFor="login-email">Email Address</label>
                  <div className="field-box">
                    <EmailOutlined sx={{ fontSize: 17, color: "#526684" }} />
                    <input
                      id="login-email"
                      type="email"
                      required
                      placeholder="you@agency.com"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      autoComplete="email"
                    />
                  </div>
                </div>

                <div className="agent-sample-field">
                  <label htmlFor="login-password">Password</label>
                  <div className="field-box">
                    <LockOutlined sx={{ fontSize: 17, color: "#526684" }} />
                    <input
                      id="login-password"
                      type={showPassword ? "text" : "password"}
                      required
                      placeholder="Enter your password"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      autoComplete="current-password"
                    />
                    <IconButton
                      onClick={() => setShowPassword(!showPassword)}
                      edge="end"
                      size="small"
                      aria-label={
                        showPassword ? "Hide password" : "Show password"
                      }
                      sx={{ color: "#526684", p: 0.5 }}
                    >
                      {showPassword ? (
                        <VisibilityOff sx={{ fontSize: 18 }} />
                      ) : (
                        <Visibility sx={{ fontSize: 18 }} />
                      )}
                    </IconButton>
                  </div>
                </div>

                {loginError && (
                  <Alert
                    severity="error"
                    sx={{
                      mt: 1.5,
                      py: 0,
                      borderRadius: "9px",
                      "& .MuiAlert-message": {
                        py: 0.75,
                        fontSize: "0.8rem",
                      },
                    }}
                  >
                    {loginError}
                  </Alert>
                )}

                <button
                  type="submit"
                  className="agent-partner-submit"
                  disabled={busy}
                >
                  {busy ? (
                    <>
                      <CircularProgress size={18} sx={{ color: "white" }} />
                      Signing in...
                    </>
                  ) : (
                    <>
                      Sign In <span aria-hidden="true">→</span>
                    </>
                  )}
                </button>
              </form>

              <p className="agent-signup-note">
                New to Travclicks?{" "}
                <a href="mailto:admin@travclicks.com?subject=Travclicks%20TA%20Partner%20Signup">
                  Request partner signup
                </a>
              </p>

              <p className="agent-secure-note">
                Secure access powered by Travclicks Technologies
              </p>

              <div className="agent-partner-tools">
                <span>
                  <VerifiedUserOutlined sx={{ fontSize: 16 }} />
                  Secure Access
                </span>
                <a href="mailto:admin@travclicks.com">
                  <SupportAgentOutlined sx={{ fontSize: 16 }} />
                  Partner Support
                </a>
              </div>
            </div>
          </div>
        </section>
      </div>

      <AgentOutboundWorkspace />
    </div>
  );
}

export default Login;
