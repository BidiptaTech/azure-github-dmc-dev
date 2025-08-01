import React, { useState } from "react";
import "./login.css";
import { useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { setName, setEmail1, setAuthenticated, setAgentId } from "./loginSlice";
import { loginUser } from "../../slice/common/authSlices";
import { setUserRole } from "@/slice/common/authSlices";
import { resetPackages } from "@/slice/tour-packages/prePackagesSlice";
import { setSelectedDmcId } from "../../slice/dmc/dmcSlice";
import {
  Box,
  Card,
  CardContent,
  TextField,
  Button,
  Typography,
  IconButton,
  InputAdornment,
  Container,
  Grid,
  Paper,
  Fade,
  Slide,
  Zoom,
  CircularProgress,
  Avatar,
  Divider,
  useTheme,
  alpha,
  Tooltip,
} from "@mui/material";
import {
  Email,
  Lock,
  Visibility,
  VisibilityOff,
  Login as LoginIcon,
  TravelExplore,
  Hotel,
  Attractions,
  Restaurant,
  DirectionsCar,
  Person,
  LocationOn,
  Star,
  Security,
  Speed,
  Support,
  PersonAdd,
  ArrowForward,
} from "@mui/icons-material";
import { keyframes } from "@mui/system";

// Custom animations
const float = keyframes`
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
`;

const pulse = keyframes`
  0% { box-shadow: 0 0 0 0 rgba(77, 115, 252, 0.7); }
  70% { box-shadow: 0 0 0 10px rgba(77, 115, 252, 0); }
  100% { box-shadow: 0 0 0 0 rgba(77, 115, 252, 0); }
`;

const slideInLeft = keyframes`
  from { transform: translateX(-100%); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
`;

const slideInRight = keyframes`
  from { transform: translateX(100%); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
`;

const rotate = keyframes`
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
`;

const doorOpenLeft = keyframes`
  0% { 
    transform: rotateY(0deg);
  }
  100% { 
    transform: rotateY(-120deg);
  }
`;

const doorOpenRight = keyframes`
  0% { 
    transform: rotateY(0deg);
  }
  100% { 
    transform: rotateY(120deg);
  }
`;

const fade = keyframes`
  0% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
  50% { opacity: 1; transform: translate(-50%, -50%) scale(1.1); }
  100% { opacity: 0; transform: translate(-50%, -50%) scale(1); }
`;

const welcomeGlow = keyframes`
  0%, 100% { 
    text-shadow: 0 0 20px rgba(255, 255, 255, 0.8), 0 0 40px rgba(102, 126, 234, 0.6);
  }
  50% { 
    text-shadow: 0 0 40px rgba(255, 255, 255, 1), 0 0 80px rgba(102, 126, 234, 0.9);
  }
`;

const sparkle = keyframes`
  0%, 100% { 
    opacity: 0; 
    transform: scale(0) rotate(0deg);
  }
  50% { 
    opacity: 1; 
    transform: scale(1) rotate(180deg);
  }
`;

const slideInUp = keyframes`
  0% { 
    opacity: 0; 
    transform: translateY(30px);
  }
  100% { 
    opacity: 1; 
    transform: translateY(0);
  }
`;

const bounceIn = keyframes`
  0% { 
    opacity: 0; 
    transform: scale(0.3);
  }
  50% { 
    opacity: 1; 
    transform: scale(1.05);
  }
  70% { 
    transform: scale(0.9);
  }
  100% { 
    opacity: 1; 
    transform: scale(1);
  }
`;

function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [showDoorAnimation, setShowDoorAnimation] = useState(false);

  const navigate = useNavigate();
  const dispatch = useDispatch();
  const theme = useTheme();

  const { loginStatus, loginError } = useSelector((state) => state.auth);

  const handleLogin = async (e) => {
    e.preventDefault();
    setIsLoading(true);
    dispatch(resetPackages());
    
    const result = await dispatch(loginUser({ email, password }));

    if (loginUser.fulfilled.match(result)) {
      const {
        agent_id: agentId,
        userRole,
        dmcId,
      } = result.payload;

      dispatch(setAgentId(agentId));
      dispatch(setName(email.split("@")[0]));
      dispatch(setEmail1(email));
      dispatch(setAuthenticated(true));

      // For non-Agent users, set DMC ID in DMC slice
      if (dmcId && userRole !== "Agent") {
        console.log('🎯 Login Component: Setting DMC ID in DMC slice for non-Agent user:', dmcId);
        dispatch(setSelectedDmcId({
          dmcId: dmcId,
          dmcData: {
            id: `dmc-auth-${dmcId}`,
            dmcId: dmcId,
            name: `DMC ${dmcId}`,
            location: 'Auth-selected',
            logo: '',
            rating: 4.5,
            description: 'DMC from authentication',
            originalData: { dmcId: dmcId }
          }
        }));
      }

      // Trigger door animation
      setShowDoorAnimation(true);
      setTimeout(() => navigate("/dashboard/db-dashboard"), 1200);
    } else {
      // Handle login error silently or show error in UI
      setIsLoading(false);
    }
  };

  const ServiceIcons = () => (
    <Box sx={{ display: "flex", justifyContent: "center", gap: 1, mt: 1 }}>
      <Tooltip title="Hotel Booking" arrow>
        <Box sx={{ color: "#667eea", cursor: "pointer" }}>
          <Hotel sx={{ fontSize: 20 }} />
        </Box>
      </Tooltip>
      <Tooltip title="Attractions" arrow>
        <Box sx={{ color: "#667eea", cursor: "pointer" }}>
          <Attractions sx={{ fontSize: 20 }} />
        </Box>
      </Tooltip>
      <Tooltip title="Restaurants" arrow>
        <Box sx={{ color: "#667eea", cursor: "pointer" }}>
          <Restaurant sx={{ fontSize: 20 }} />
        </Box>
      </Tooltip>
      <Tooltip title="Tour Guide" arrow>
        <Box sx={{ color: "#667eea", cursor: "pointer" }}>
          <Person sx={{ fontSize: 20 }} />
        </Box>
      </Tooltip>
      <Tooltip title="Transport" arrow>
        <Box sx={{ color: "#667eea", cursor: "pointer" }}>
          <DirectionsCar sx={{ fontSize: 20 }} />
        </Box>
      </Tooltip>
    </Box>
  );

  const FeatureCard = ({ icon, title, description, delay }) => (
    <Fade in timeout={1000} style={{ transitionDelay: `${delay}ms` }}>
      <Card
        sx={{
          p: 3,
          textAlign: "center",
          background: "linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%)",
          border: "1px solid rgba(102, 126, 234, 0.1)",
          color: "#333",
          borderRadius: 3,
          transform: "translateY(0)",
          transition: "all 0.3s ease",
          boxShadow: "0 8px 32px rgba(102, 126, 234, 0.1)",
          "&:hover": {
            transform: "translateY(-10px)",
            boxShadow: "0 20px 40px rgba(102, 126, 234, 0.2)",
            border: "1px solid rgba(102, 126, 234, 0.3)",
          },
        }}
      >
        <Box
          sx={{
            animation: `${float} 3s ease-in-out infinite`,
            mb: 2,
            color: "#667eea",
          }}
        >
          {icon}
        </Box>
        <Typography 
          variant="h6" 
          gutterBottom
          sx={{
            background: "linear-gradient(45deg, #667eea, #764ba2)",
            backgroundClip: "text",
            WebkitBackgroundClip: "text",
            WebkitTextFillColor: "transparent",
            fontWeight: "600",
          }}
        >
          {title}
        </Typography>
        {title === "Travel Services" ? (
          <ServiceIcons />
        ) : (
          <Typography variant="body2" sx={{ color: "#666", opacity: 0.9 }}>
            {description}
          </Typography>
        )}
      </Card>
    </Fade>
  );

  return (
    <Box
      sx={{
        minHeight: "100vh",
        background: "linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%)",
        position: "relative",
        overflow: "hidden",
      }}
    >
             {/* Background Animation Elements */}
       <Box
         sx={{
           position: "absolute",
           top: "10%",
           left: "10%",
           width: "100px",
           height: "100px",
           borderRadius: "50%",
           background: "linear-gradient(45deg, #667eea20, #764ba220)",
           animation: `${float} 6s ease-in-out infinite`,
         }}
       />
       <Box
         sx={{
           position: "absolute",
           top: "70%",
           right: "15%",
           width: "150px",
           height: "150px",
           borderRadius: "50%",
           background: "linear-gradient(45deg, #FE6B8B20, #FF8E5320)",
           animation: `${float} 4s ease-in-out infinite`,
         }}
       />
       <Box
         sx={{
           position: "absolute",
           top: "30%",
           right: "5%",
           width: "80px",
           height: "80px",
           borderRadius: "50%",
           background: "linear-gradient(45deg, #667eea15, #764ba215)",
           animation: `${float} 5s ease-in-out infinite`,
         }}
       />

             <Container maxWidth="lg" sx={{ py: 4 }}>
         <Grid 
           container 
           spacing={4} 
           alignItems="center" 
           minHeight="100vh"
           sx={{ 
             opacity: showDoorAnimation ? 0 : 1,
             transition: "opacity 0.3s ease-out"
           }}
         >
           {/* Left side - Features */}
           <Grid item xs={12} md={6}>
            <Slide direction="right" in timeout={800}>
              <Box sx={{ animation: `${slideInLeft} 1s ease-out` }}>
                <Box sx={{ mb: 6, textAlign: "center" }}>
                  <Avatar
                    sx={{
                      width: 80,
                      height: 80,
                      mx: "auto",
                      mb: 3,
                      background: "linear-gradient(45deg, #FE6B8B 30%, #FF8E53 90%)",
                      animation: `${pulse} 2s infinite`,
                    }}
                  >
                    <TravelExplore sx={{ fontSize: 40 }} />
                  </Avatar>
                                     <Typography
                     variant="h3"
                     component="h1"
                     gutterBottom
                     sx={{
                       background: "linear-gradient(45deg, #667eea, #764ba2)",
                       backgroundClip: "text",
                       WebkitBackgroundClip: "text",
                       WebkitTextFillColor: "transparent",
                       fontWeight: "bold",
                       textShadow: "none",
                     }}
                   >
                     Welcome Back!
                   </Typography>
                   <Typography
                     variant="h6"
                     sx={{
                       color: "#666",
                       mb: 4,
                     }}
                   >
                     Sign in to access your travel dashboard
                   </Typography>
                </Box>

                <Grid container spacing={3}>
                  <Grid item xs={12} sm={6}>
                    <FeatureCard
                      icon={<TravelExplore sx={{ fontSize: 40 }} />}
                      title="Travel Services"
                      description="Hotel, Attractions, Restaurants, Guide & Transport"
                      delay={200}
                    />
                  </Grid>
                  <Grid item xs={12} sm={6}>
                    <FeatureCard
                      icon={<LocationOn sx={{ fontSize: 40 }} />}
                      title="Tour Packages"
                      description="Discover amazing destinations"
                      delay={400}
                    />
                  </Grid>
                  <Grid item xs={12} sm={6}>
                    <FeatureCard
                      icon={<Security sx={{ fontSize: 40 }} />}
                      title="Secure Payment"
                      description="Your transactions are protected"
                      delay={600}
                    />
                  </Grid>
                  <Grid item xs={12} sm={6}>
                    <FeatureCard
                      icon={<Support sx={{ fontSize: 40 }} />}
                      title="24/7 Support"
                      description="We're here to help anytime"
                      delay={800}
                    />
                  </Grid>
                </Grid>
              </Box>
            </Slide>
          </Grid>

          {/* Right side - Login Form */}
          <Grid item xs={12} md={6}>
            <Slide direction="left" in timeout={800}>
              <Box sx={{ animation: `${slideInRight} 1s ease-out` }}>
                                 <Paper
                   elevation={24}
                   sx={{
                     p: 4,
                     borderRadius: 4,
                     background: "rgba(255,255,255,0.98)",
                     backdropFilter: "blur(10px)",
                     border: "1px solid rgba(102, 126, 234, 0.1)",
                     boxShadow: "0 24px 48px rgba(102, 126, 234, 0.1)",
                     maxWidth: 500,
                     mx: "auto",
                   }}
                 >
                  <Zoom in timeout={1000}>
                    <Box sx={{ textAlign: "center", mb: 4 }}>
                      <img
                        src="/Images/travclicklogo.jpeg"
                        alt="Logo"
                        style={{
                          width: "200px",
                          height: "auto",
                          borderRadius: "12px",
                          boxShadow: "0 8px 32px rgba(0,0,0,0.1)",
                        }}
                      />
                    </Box>
                  </Zoom>

                  <Typography
                    variant="h4"
                    component="h2"
                    gutterBottom
                    sx={{
                      textAlign: "center",
                      fontWeight: "bold",
                      background: "linear-gradient(45deg, #667eea, #764ba2)",
                      backgroundClip: "text",
                      WebkitBackgroundClip: "text",
                      WebkitTextFillColor: "transparent",
                      mb: 3,
                    }}
                  >
                    Sign In
                  </Typography>

                  <Box component="form" onSubmit={handleLogin} sx={{ mt: 2 }}>
                    <Fade in timeout={1200}>
                      <TextField
                        fullWidth
                        label="Email Address"
                              type="email"
                              value={email}
                              onChange={(e) => setEmail(e.target.value)}
                              required
                        margin="normal"
                        InputProps={{
                          startAdornment: (
                            <InputAdornment position="start">
                              <Email sx={{ color: "#667eea" }} />
                            </InputAdornment>
                          ),
                        }}
                        sx={{
                          "& .MuiOutlinedInput-root": {
                            borderRadius: 2,
                            "&:hover fieldset": {
                              borderColor: "#667eea",
                            },
                            "&.Mui-focused fieldset": {
                              borderColor: "#667eea",
                            },
                          },
                        }}
                      />
                    </Fade>

                    <Fade in timeout={1400}>
                      <TextField
                        fullWidth
                        label="Password"
                              type={showPassword ? "text" : "password"}
                              value={password}
                              onChange={(e) => setPassword(e.target.value)}
                              required
                        margin="normal"
                        InputProps={{
                          startAdornment: (
                            <InputAdornment position="start">
                              <Lock sx={{ color: "#667eea" }} />
                            </InputAdornment>
                          ),
                          endAdornment: (
                            <InputAdornment position="end">
                              <IconButton
                                onClick={() => setShowPassword(!showPassword)}
                                edge="end"
                                sx={{
                                  color: "#667eea",
                                  "&:hover": {
                                    background: "rgba(102, 126, 234, 0.1)",
                                    transform: "scale(1.1)",
                                  },
                                }}
                            >
                                {showPassword ? <VisibilityOff /> : <Visibility />}
                              </IconButton>
                            </InputAdornment>
                          ),
                        }}
                        sx={{
                          "& .MuiOutlinedInput-root": {
                            borderRadius: 2,
                            "&:hover fieldset": {
                              borderColor: "#667eea",
                            },
                            "&.Mui-focused fieldset": {
                              borderColor: "#667eea",
                            },
                          },
                        }}
                      />
                    </Fade>

                    <Fade in timeout={1600}>
                      <Button
                            type="submit"
                        fullWidth
                        variant="contained"
                        disabled={isLoading || loginStatus === "loading"}
                        sx={{
                          mt: 3,
                          mb: 2,
                          py: 1.5,
                          borderRadius: 3,
                          background: "linear-gradient(45deg, #667eea 30%, #764ba2 90%)",
                          fontSize: "1.1rem",
                          fontWeight: "bold",
                          textTransform: "none",
                          boxShadow: "0 8px 32px rgba(102, 126, 234, 0.3)",
                          position: "relative",
                          overflow: "hidden",
                          "&:hover": {
                            background: "linear-gradient(45deg, #5a6fd8 30%, #6a4190 90%)",
                            transform: "translateY(-2px)",
                            boxShadow: "0 12px 40px rgba(102, 126, 234, 0.4)",
                          },
                          "&:active": {
                            transform: "translateY(0)",
                          },
                          "&::before": {
                            content: '""',
                            position: "absolute",
                            top: 0,
                            left: "-100%",
                            width: "100%",
                            height: "100%",
                            background: "linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent)",
                            transition: "left 0.5s",
                          },
                          "&:hover::before": {
                            left: "100%",
                          },
                        }}
                        startIcon={
                          isLoading || loginStatus === "loading" ? (
                            <CircularProgress size={20} color="inherit" />
                          ) : (
                            <LoginIcon />
                          )
                        }
                          >
                        {isLoading || loginStatus === "loading" ? "Signing In..." : "Sign In"}
                      </Button>
                    </Fade>

                    {/* Sign Up Section */}
                    <Fade in timeout={1800}>
                      <Box sx={{ textAlign: "center", mt: 2 }}>
                        <Typography 
                          variant="body2" 
                          sx={{ 
                            color: "#666",
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "center",
                            gap: 1,
                            mb: 1
                          }}
                        >
                          <PersonAdd sx={{ fontSize: 16, color: "#667eea" }} />
                          Don't have an account?
                        </Typography>
                        <Button
                          variant="text"
                          size="small"
                          onClick={() => navigate("/register")}
                          sx={{
                            color: "#667eea",
                            fontWeight: 600,
                            textTransform: "none",
                            fontSize: "0.9rem",
                            p: 1,
                            borderRadius: 2,
                            transition: "all 0.3s ease",
                            "&:hover": {
                              background: "rgba(102, 126, 234, 0.1)",
                              transform: "translateY(-1px)",
                              color: "#5a6fd8",
                            },
                            "&:active": {
                              transform: "translateY(0)",
                            },
                          }}
                          endIcon={
                            <ArrowForward sx={{ 
                              fontSize: 16,
                              transition: "transform 0.3s ease",
                              "&:hover": {
                                transform: "translateX(2px)",
                              }
                            }} />
                          }
                        >
                          Sign Up
                        </Button>
                      </Box>
                    </Fade>

                    {loginError && (
                      <Fade in timeout={300}>
                        <Box
                          sx={{
                            mt: 2,
                            p: 2,
                            borderRadius: 2,
                            background: "rgba(244, 67, 54, 0.1)",
                            border: "1px solid rgba(244, 67, 54, 0.3)",
                            color: "#d32f2f",
                            textAlign: "center",
                          }}
                        >
                          <Typography variant="body2">{loginError}</Typography>
                        </Box>
                      </Fade>
                    )}
                  </Box>

                  <Divider sx={{ my: 3 }}>
                    <Typography variant="body2" color="text.secondary">
                      Secure Login
                    </Typography>
                  </Divider>

                  <Box sx={{ textAlign: "center" }}>
                    <Typography variant="body2" color="text.secondary">
                      Protected by enterprise-grade security
                    </Typography>
                    <Box sx={{ mt: 2, display: "flex", justifyContent: "center", gap: 1 }}>
                      {[...Array(5)].map((_, i) => (
                        <Star
                          key={i}
                          sx={{
                            color: "#FFD700",
                            fontSize: 20,
                            animation: `${pulse} 2s infinite ${i * 0.2}s`,
                          }}
                        />
                      ))}
                    </Box>
                  </Box>
                </Paper>
              </Box>
            </Slide>
          </Grid>
        </Grid>
      </Container>

      {/* Door Opening Animation */}
      {showDoorAnimation && (
        <Box
          sx={{
            position: "fixed",
            top: 0,
            left: 0,
            width: "100vw",
            height: "100vh",
            zIndex: 9999,
            pointerEvents: "none",
          }}
        >
          {/* Left Door */}
          <Box
            sx={{
              position: "absolute",
              top: 0,
              left: 0,
              width: "50%",
              height: "100%",
              background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
              transformOrigin: "left center",
              animation: `${doorOpenLeft} 1.2s ease-in-out forwards`,
              boxShadow: "0 0 20px rgba(0,0,0,0.3)",
            }}
          />
          
          {/* Right Door */}
          <Box
            sx={{
              position: "absolute",
              top: 0,
              right: 0,
              width: "50%",
              height: "100%",
              background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
              transformOrigin: "right center",
              animation: `${doorOpenRight} 1.2s ease-in-out forwards`,
              boxShadow: "0 0 20px rgba(0,0,0,0.3)",
            }}
          />
          

          
          {/* Enhanced Welcome Message */}
          <Box
            sx={{
              position: "absolute",
              top: "50%",
              left: "50%",
              transform: "translate(-50%, -50%)",
              textAlign: "center",
              color: "white",
              zIndex: 10000,
              width: "100%",
              maxWidth: "600px",
            }}
          >
            {/* Sparkle Effects */}
            <Box
              sx={{
                position: "absolute",
                top: "-50px",
                left: "20%",
                width: "20px",
                height: "20px",
                background: "radial-gradient(circle, #FFD700, #FFA500)",
                borderRadius: "50%",
                animation: `${sparkle} 2s ease-in-out infinite`,
                animationDelay: "0s",
              }}
            />
            <Box
              sx={{
                position: "absolute",
                top: "-30px",
                right: "25%",
                width: "15px",
                height: "15px",
                background: "radial-gradient(circle, #FF6B6B, #FF8E8E)",
                borderRadius: "50%",
                animation: `${sparkle} 2s ease-in-out infinite`,
                animationDelay: "0.5s",
              }}
            />
            <Box
              sx={{
                position: "absolute",
                bottom: "-40px",
                left: "30%",
                width: "18px",
                height: "18px",
                background: "radial-gradient(circle, #4ECDC4, #45B7B8)",
                borderRadius: "50%",
                animation: `${sparkle} 2s ease-in-out infinite`,
                animationDelay: "1s",
              }}
            />

            {/* Main Welcome Icon */}
            <Box
              sx={{
                animation: `${bounceIn} 1s ease-out`,
                mb: 3,
              }}
            >
              <Avatar
                sx={{
                  width: 80,
                  height: 80,
                  mx: "auto",
                  mb: 2,
                  background: "linear-gradient(45deg, #FFD700, #FFA500)",
                  animation: `${pulse} 2s infinite`,
                  boxShadow: "0 0 40px rgba(255, 215, 0, 0.6)",
                }}
              >
                <TravelExplore sx={{ fontSize: 40, color: "white" }} />
              </Avatar>
            </Box>

            {/* Welcome Text */}
            <Box
              sx={{
                animation: `${slideInUp} 0.8s ease-out 0.2s both`,
                mb: 3,
              }}
            >
              <Typography
                variant="h2"
                sx={{
                  fontWeight: "bold",
                  background: "linear-gradient(45deg, #FFD700, #FFA500, #FF6B6B)",
                  backgroundClip: "text",
                  WebkitBackgroundClip: "text",
                  WebkitTextFillColor: "transparent",
                  animation: `${welcomeGlow} 2s ease-in-out infinite`,
                  mb: 1,
                  fontFamily: "'Poppins', sans-serif",
                }}
              >
                Welcome Back!
              </Typography>
              
              <Typography
                variant="h5"
                sx={{
                  color: "rgba(255, 255, 255, 0.95)",
                  fontWeight: "500",
                  textShadow: "0 2px 4px rgba(0,0,0,0.3)",
                  letterSpacing: "0.5px",
                }}
              >
                Preparing your travel dashboard
              </Typography>
            </Box>

            {/* Loading Animation */}
            <Box
              sx={{
                animation: `${slideInUp} 0.6s ease-out 0.4s both`,
                display: "flex",
                justifyContent: "center",
                alignItems: "center",
                gap: 2,
                mb: 2,
              }}
            >
              <Box
                sx={{
                  display: "flex",
                  gap: 1,
                  alignItems: "center",
                }}
              >
                <CircularProgress
                  size={24}
                  sx={{
                    color: "#FFD700",
                    animation: `${pulse} 1.5s ease-in-out infinite`,
                  }}
                />
                <Typography
                  variant="body1"
                  sx={{
                    color: "rgba(255, 255, 255, 0.9)",
                    fontWeight: "500",
                  }}
                >
                  Loading
                </Typography>
                <Box sx={{ display: "flex", gap: 0.5 }}>
                  {[0, 1, 2].map((i) => (
                    <Box
                      key={i}
                      sx={{
                        width: 4,
                        height: 4,
                        borderRadius: "50%",
                        backgroundColor: "#FFD700",
                        animation: `${pulse} 1s ease-in-out infinite`,
                        animationDelay: `${i * 0.3}s`,
                      }}
                    />
                  ))}
                </Box>
              </Box>
            </Box>

            {/* Features Preview */}
            <Box
              sx={{
                animation: `${slideInUp} 0.6s ease-out 0.6s both`,
                display: "flex",
                justifyContent: "center",
                gap: 3,
                opacity: 0.8,
              }}
            >
              <Box sx={{ textAlign: "center" }}>
                <Hotel sx={{ fontSize: 28, color: "#FFD700", mb: 0.5 }} />
                <Typography variant="caption" sx={{ color: "rgba(255,255,255,0.8)" }}>
                  Hotels
                </Typography>
              </Box>
              <Box sx={{ textAlign: "center" }}>
                <Attractions sx={{ fontSize: 28, color: "#FF6B6B", mb: 0.5 }} />
                <Typography variant="caption" sx={{ color: "rgba(255,255,255,0.8)" }}>
                  Attractions
                </Typography>
              </Box>
              <Box sx={{ textAlign: "center" }}>
                <Restaurant sx={{ fontSize: 28, color: "#4ECDC4", mb: 0.5 }} />
                <Typography variant="caption" sx={{ color: "rgba(255,255,255,0.8)" }}>
                  Dining
                </Typography>
              </Box>
              <Box sx={{ textAlign: "center" }}>
                <DirectionsCar sx={{ fontSize: 28, color: "#45B7B8", mb: 0.5 }} />
                <Typography variant="caption" sx={{ color: "rgba(255,255,255,0.8)" }}>
                  Transport
                </Typography>
              </Box>
            </Box>
          </Box>
        </Box>
      )}
    </Box>
  );
}

export default Login;
