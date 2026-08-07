import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { setName, setEmail1, setAuthenticated, setAgentId } from "./loginSlice";
import { loginUser } from "../../slice/common/authSlices";
import { resetPackages } from "@/slice/tour-packages/prePackagesSlice";
import { setDmcFromAuth } from "../../slice/dmc/dmcSlice";
import {
  Box,
  TextField,
  Button,
  Typography,
  IconButton,
  InputAdornment,
  Paper,
  CircularProgress,
  Alert,
} from "@mui/material";
import {
  Email,
  Lock,
  Visibility,
  VisibilityOff,
  ArrowBack,
  Hotel,
  DirectionsCar,
  SupportAgent,
  VerifiedUser,
} from "@mui/icons-material";
import { ClearLists } from "@/slice/common/TourlistSlice";
import { resetAgentList } from "@/slice/common/agentListSlice";

const NAVY = "#13357b";

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
            dmcId: dmcId,
            dmcLogo: dmcLogo,
            dmcCompanyName: dmcCompanyName,
          })
        );
      }

      navigate("/dashboard/db-dashboard");
    } else {
      setIsLoading(false);
    }
  };

  const busy = isLoading || loginStatus === "loading";

  return (
    <Box
      sx={{
        minHeight: "100vh",
        display: "flex",
        bgcolor: "#f4f7fb",
        position: "relative",
        overflow: "hidden",
      }}
    >
      {/* Soft grid pattern across the page */}
      <Box
        sx={{
          position: "absolute",
          inset: 0,
          backgroundImage: `
            linear-gradient(rgba(19, 53, 123, 0.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(19, 53, 123, 0.04) 1px, transparent 1px)
          `,
          backgroundSize: "48px 48px",
          pointerEvents: "none",
        }}
      />

      {/* Mobile brand strip */}
      <Box
        sx={{
          display: { xs: "block", md: "none" },
          position: "absolute",
          top: 0,
          left: 0,
          right: 0,
          height: 120,
          bgcolor: NAVY,
          backgroundImage:
            "radial-gradient(circle, rgba(255,255,255,0.08) 1px, transparent 1px)",
          backgroundSize: "18px 18px",
          zIndex: 0,
        }}
      />

      {/* Left brand panel */}
      <Box
        sx={{
          display: { xs: "none", md: "flex" },
          width: { md: "42%", lg: "45%" },
          minHeight: "100vh",
          bgcolor: NAVY,
          position: "relative",
          flexDirection: "column",
          justifyContent: "center",
          px: { md: 5, lg: 7 },
          overflow: "hidden",
        }}
      >
        {/* Subtle diagonal shape */}
        <Box
          sx={{
            position: "absolute",
            top: "-10%",
            right: "-20%",
            width: "70%",
            height: "50%",
            bgcolor: "rgba(255,255,255,0.04)",
            transform: "rotate(18deg)",
            borderRadius: "24px",
          }}
        />
        <Box
          sx={{
            position: "absolute",
            bottom: "-15%",
            left: "-10%",
            width: "55%",
            height: "45%",
            bgcolor: "rgba(255,255,255,0.03)",
            transform: "rotate(-12deg)",
            borderRadius: "24px",
          }}
        />
        {/* Dot texture */}
        <Box
          sx={{
            position: "absolute",
            inset: 0,
            opacity: 0.12,
            backgroundImage:
              "radial-gradient(circle, rgba(255,255,255,0.7) 1px, transparent 1px)",
            backgroundSize: "22px 22px",
          }}
        />

        <Box sx={{ position: "relative", zIndex: 1 }}>
          <Typography
            sx={{
              color: "rgba(255,255,255,0.7)",
              fontSize: "0.75rem",
              fontWeight: 600,
              letterSpacing: "0.12em",
              textTransform: "uppercase",
              mb: 1.5,
            }}
          >
            TravClicks
          </Typography>
          <Typography
            sx={{
              color: "white",
              fontWeight: 700,
              fontSize: { md: "1.75rem", lg: "2rem" },
              lineHeight: 1.25,
              mb: 1.5,
              maxWidth: 360,
            }}
          >
            AI-powered travel operations for agencies
          </Typography>
          <Typography
            sx={{
              color: "rgba(255,255,255,0.75)",
              fontSize: "0.9rem",
              lineHeight: 1.6,
              maxWidth: 340,
              mb: 3,
            }}
          >
            Automate inquiries, bookings, quotations, and traveler engagement
            with Travclicks AI — built for growing travel enterprises.
          </Typography>

          <Box
            sx={{
              display: "flex",
              flexWrap: "wrap",
              gap: 1,
            }}
          >
            {[
              "Singapore",
              "Malaysia",
              "Thailand",
              "Indonesia",
              "Australia",
              "New Zealand",
              "Laos",
              "Cambodia",
              "UAE",
            ].map((market) => (
              <Box
                key={market}
                sx={{
                  display: "inline-flex",
                  alignItems: "center",
                  gap: 0.75,
                  px: 1.25,
                  py: 0.5,
                  borderRadius: "6px",
                  bgcolor: "rgba(255,255,255,0.1)",
                  border: "1px solid rgba(255,255,255,0.18)",
                  color: "rgba(255,255,255,0.95)",
                  fontSize: "0.78rem",
                  fontWeight: 500,
                }}
              >
                {market}
              </Box>
            ))}
          </Box>
        </Box>
      </Box>

      {/* Right form area */}
      <Box
        sx={{
          flex: 1,
          display: "flex",
          flexDirection: "column",
          alignItems: "center",
          justifyContent: "center",
          px: { xs: 2, md: 4 },
          py: 3,
          position: "relative",
          zIndex: 1,
          bgcolor: { xs: "transparent", md: "#eef3f9" },
          overflow: "hidden",
        }}
      >
        {/* Right-side decorative layers */}
        <Box
          sx={{
            position: "absolute",
            top: -80,
            right: -80,
            width: 280,
            height: 280,
            borderRadius: "50%",
            border: "40px solid rgba(19, 53, 123, 0.05)",
            pointerEvents: "none",
            display: { xs: "none", md: "block" },
          }}
        />
        <Box
          sx={{
            position: "absolute",
            bottom: -60,
            left: -40,
            width: 220,
            height: 220,
            borderRadius: "50%",
            bgcolor: "rgba(19, 53, 123, 0.04)",
            pointerEvents: "none",
            display: { xs: "none", md: "block" },
          }}
        />
        <Box
          sx={{
            position: "absolute",
            top: "18%",
            left: "8%",
            width: 12,
            height: 12,
            borderRadius: "50%",
            bgcolor: "rgba(19, 53, 123, 0.15)",
            display: { xs: "none", md: "block" },
          }}
        />
        <Box
          sx={{
            position: "absolute",
            top: "28%",
            right: "12%",
            width: 8,
            height: 8,
            borderRadius: "50%",
            bgcolor: "rgba(19, 53, 123, 0.12)",
            display: { xs: "none", md: "block" },
          }}
        />
        <Box
          sx={{
            position: "absolute",
            bottom: "22%",
            right: "18%",
            width: 10,
            height: 10,
            borderRadius: "2px",
            bgcolor: "rgba(19, 53, 123, 0.1)",
            transform: "rotate(45deg)",
            display: { xs: "none", md: "block" },
          }}
        />
        <Box
          sx={{
            position: "absolute",
            inset: 0,
            backgroundImage: `
              linear-gradient(rgba(19, 53, 123, 0.035) 1px, transparent 1px),
              linear-gradient(90deg, rgba(19, 53, 123, 0.035) 1px, transparent 1px)
            `,
            backgroundSize: "36px 36px",
            pointerEvents: "none",
            display: { xs: "none", md: "block" },
          }}
        />

        {/* Soft accent glow behind card */}
        <Box
          sx={{
            position: "absolute",
            width: 480,
            height: 480,
            borderRadius: "50%",
            background:
              "radial-gradient(circle, rgba(19,53,123,0.1) 0%, transparent 70%)",
            pointerEvents: "none",
          }}
        />

        <Box
          sx={{
            width: "100%",
            maxWidth: 440,
            position: "relative",
            zIndex: 1,
          }}
        >
          <Typography
            sx={{
              display: { xs: "none", md: "block" },
              textAlign: "center",
              color: NAVY,
              fontWeight: 600,
              fontSize: "0.75rem",
              letterSpacing: "0.1em",
              textTransform: "uppercase",
              mb: 2,
            }}
          >
            Partner Portal
          </Typography>

        <Paper
          elevation={0}
          sx={{
            width: "100%",
            p: { xs: 2.5, sm: 3.5 },
            borderRadius: "10px",
            border: "1px solid #dce4f0",
            boxShadow: "0 8px 32px rgba(19, 53, 123, 0.1)",
            position: "relative",
            bgcolor: "#fff",
          }}
        >
        <IconButton
          onClick={() => navigate("/")}
          size="small"
          aria-label="Back to home"
          sx={{
            position: "absolute",
            top: 12,
            left: 12,
            color: NAVY,
            border: "1px solid #dce4f0",
            borderRadius: "6px",
            width: 32,
            height: 32,
            "&:hover": { bgcolor: "#e8eef6" },
          }}
        >
          <ArrowBack sx={{ fontSize: 16 }} />
        </IconButton>

        <Box sx={{ textAlign: "center", mb: 3, pt: 1 }}>
          <Box
            component="img"
            src="/Images/logo.png"
            alt="TravClicks"
            sx={{
              height: { xs: 44, sm: 52 },
              width: "auto",
              objectFit: "contain",
              mb: 1.5,
            }}
          />
          <Typography
            sx={{
              fontWeight: 700,
              color: NAVY,
              fontSize: "1.25rem",
              lineHeight: 1.3,
            }}
          >
            Sign In
          </Typography>
          <Typography
            sx={{
              color: "#64748b",
              fontSize: "0.85rem",
              mt: 0.5,
            }}
          >
            Access your Travclicks AI partner dashboard
          </Typography>
        </Box>

        <Box component="form" onSubmit={handleLogin}>
          <TextField
            fullWidth
            size="small"
            label="Email Address"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            sx={{
              mb: 1.75,
              "& .MuiOutlinedInput-root": {
                borderRadius: "6px",
                fontSize: "0.875rem",
                "& fieldset": { borderColor: "#dce4f0" },
                "&:hover fieldset": { borderColor: "#a8bcd6" },
                "&.Mui-focused fieldset": { borderColor: NAVY },
              },
              "& .MuiInputLabel-root": {
                fontSize: "0.875rem",
                color: "#64748b",
                "&.Mui-focused": { color: NAVY },
              },
            }}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <Email sx={{ color: NAVY, fontSize: 18 }} />
                </InputAdornment>
              ),
            }}
          />

          <TextField
            fullWidth
            size="small"
            label="Password"
            type={showPassword ? "text" : "password"}
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            sx={{
              mb: 1,
              "& .MuiOutlinedInput-root": {
                borderRadius: "6px",
                fontSize: "0.875rem",
                "& fieldset": { borderColor: "#dce4f0" },
                "&:hover fieldset": { borderColor: "#a8bcd6" },
                "&.Mui-focused fieldset": { borderColor: NAVY },
              },
              "& .MuiInputLabel-root": {
                fontSize: "0.875rem",
                color: "#64748b",
                "&.Mui-focused": { color: NAVY },
              },
            }}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <Lock sx={{ color: NAVY, fontSize: 18 }} />
                </InputAdornment>
              ),
              endAdornment: (
                <InputAdornment position="end">
                  <IconButton
                    onClick={() => setShowPassword(!showPassword)}
                    edge="end"
                    size="small"
                    sx={{ color: NAVY }}
                  >
                    {showPassword ? (
                      <VisibilityOff fontSize="small" />
                    ) : (
                      <Visibility fontSize="small" />
                    )}
                  </IconButton>
                </InputAdornment>
              ),
            }}
          />

          {loginError && (
            <Alert
              severity="error"
              sx={{
                mt: 1.5,
                mb: 0.5,
                py: 0,
                borderRadius: "6px",
                "& .MuiAlert-message": { py: 0.75, fontSize: "0.8rem" },
              }}
            >
              {loginError}
            </Alert>
          )}

          <Button
            type="submit"
            fullWidth
            variant="contained"
            disabled={busy}
            sx={{
              mt: 2.5,
              py: 1.1,
              borderRadius: "6px",
              bgcolor: NAVY,
              fontSize: "0.9rem",
              fontWeight: 600,
              textTransform: "none",
              boxShadow: "none",
              "&:hover": {
                bgcolor: "#0f2d6b",
                boxShadow: "none",
              },
              "&.Mui-disabled": {
                bgcolor: "#94a3b8",
                color: "#fff",
              },
            }}
          >
            {busy ? (
              <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                <CircularProgress size={18} sx={{ color: "white" }} />
                Signing in...
              </Box>
            ) : (
              "Sign In"
            )}
          </Button>
        </Box>

        <Typography
          sx={{
            mt: 2.5,
            textAlign: "center",
            color: "#94a3b8",
            fontSize: "0.75rem",
          }}
        >
          Secure access powered by Travclicks Technologies
        </Typography>
        </Paper>

          {/* Feature strip under form */}
          <Box
            sx={{
              mt: 2.5,
              display: "grid",
              gridTemplateColumns: "1fr 1fr",
              gap: 1.25,
            }}
          >
            {[
              { icon: <Hotel sx={{ fontSize: 18 }} />, label: "Hotels" },
              { icon: <DirectionsCar sx={{ fontSize: 18 }} />, label: "Transfers" },
              { icon: <VerifiedUser sx={{ fontSize: 18 }} />, label: "Secure Access" },
              { icon: <SupportAgent sx={{ fontSize: 18 }} />, label: "Partner Support" },
            ].map((item) => (
              <Box
                key={item.label}
                sx={{
                  display: "flex",
                  alignItems: "center",
                  gap: 1,
                  px: 1.25,
                  py: 1,
                  borderRadius: "8px",
                  bgcolor: "rgba(255,255,255,0.85)",
                  border: "1px solid #dce4f0",
                  color: NAVY,
                }}
              >
                <Box
                  sx={{
                    width: 28,
                    height: 28,
                    borderRadius: "6px",
                    bgcolor: "rgba(19, 53, 123, 0.08)",
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                    flexShrink: 0,
                  }}
                >
                  {item.icon}
                </Box>
                <Typography
                  sx={{
                    fontSize: "0.75rem",
                    fontWeight: 600,
                    color: "#334155",
                  }}
                >
                  {item.label}
                </Typography>
              </Box>
            ))}
          </Box>
        </Box>
      </Box>
    </Box>
  );
}

export default Login;
