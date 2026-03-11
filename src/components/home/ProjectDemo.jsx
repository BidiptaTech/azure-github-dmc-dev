import React, { useState, useMemo } from "react";
import {
  Box,
  Container,
  Typography,
  Button,
  Grid,
  Card,
  CardContent,
  TextField,
  MenuItem,
  Alert,
  Snackbar,
  IconButton,
  Tooltip,
  Stack,
  Checkbox,
  FormControlLabel,
  InputAdornment,
  Fade,
  Grow,
} from "@mui/material";
import {
  ChevronLeft,
  ChevronRight,
  ArrowBack,
  InfoOutlined,
  Star,
  AccessTime,
  HeadsetMic,
  WorkspacePremium,
  Person,
  Email,
  Phone,
  Business,
  Groups,
  CalendarMonth,
  Schedule,
  Public,
  LocationOn,
  Campaign,
} from "@mui/icons-material";
import { useTheme } from "@mui/material/styles";
import { keyframes } from "@mui/system";
import LandingNavbar from "@/components/header/LandingNavbar";
import Footer from "@/components/common/Footer";
import MetaComponent from "@/components/common/MetaComponent";

const metadata = {
  title: "Book a Product Demo - Travclicks",
  description: "Schedule a live walkthrough of the Travclicks platform with our product experts.",
};

const WEEKDAYS = ["M", "T", "W", "T", "F", "S", "S"];
const MONTHS = [
  "Jan", "Feb", "Mar", "Apr", "May", "Jun",
  "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
];

const TIME_ZONES = [
  { value: "Asia/Kolkata", label: "Asia/Kolkata - IST (+05:30)" },
  { value: "America/New_York", label: "America/New_York - EST (-05:00)" },
  { value: "Europe/London", label: "Europe/London - GMT (+00:00)" },
  { value: "Asia/Dubai", label: "Asia/Dubai - GST (+04:00)" },
  { value: "Asia/Singapore", label: "Asia/Singapore - SGT (+08:00)" },
];

const COUNTRY_CODES = [
  { code: "+91", country: "India", flag: "🇮🇳" },
  { code: "+1", country: "USA/Canada", flag: "🇺🇸" },
  { code: "+44", country: "UK", flag: "🇬🇧" },
  { code: "+971", country: "UAE", flag: "🇦🇪" },
  { code: "+65", country: "Singapore", flag: "🇸🇬" },
  { code: "+61", country: "Australia", flag: "🇦🇺" },
  { code: "+81", country: "Japan", flag: "🇯🇵" },
  { code: "+49", country: "Germany", flag: "🇩🇪" },
  { code: "+33", country: "France", flag: "🇫🇷" },
];

const AGENCY_TYPES = [
  "DMC (Destination Management Company)",
  "Online Travel Agency",
  "Retail Travel Agency",
  "Corporate Travel Agency",
  "Tour Operator",
  "Activity / Experience Provider",
  "Other",
];

const PROCESS_OPTIONS = [
  "Itinerary Builder",
  "Lead Management & CRM",
  "Supplier Contracting",
  "Reservations & Tour Operations",
  "Payment Tracking & Collection",
];

// Generate time slots: 9:00 AM to 6:00 PM, 10-min steps
const getTimeSlots = () => {
  const slots = [];
  for (let h = 9; h <= 18; h++) {
    for (let m = 0; m < 60; m += 10) {
      if (h === 18 && m > 0) break;
      const hour = h === 12 ? 12 : h > 12 ? h - 12 : h;
      const ampm = h < 12 ? "am" : "pm";
      const minStr = m === 0 ? "00" : String(m);
      slots.push(`${hour}:${minStr} ${ampm}`);
    }
  }
  return slots;
};

const TIME_SLOTS = getTimeSlots();

const isSameDay = (d1, d2) =>
  d1.getFullYear() === d2.getFullYear() &&
  d1.getMonth() === d2.getMonth() &&
  d1.getDate() === d2.getDate();

const isPast = (d) => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const dNorm = new Date(d);
  dNorm.setHours(0, 0, 0, 0);
  return dNorm < today;
};

const getCalendarDays = (year, month) => {
  const first = new Date(year, month, 1);
  const last = new Date(year, month + 1, 0);
  const startPad = (first.getDay() + 6) % 7; // Monday = 0
  const days = [];
  const prevMonth = month === 0 ? 11 : month - 1;
  const prevYear = month === 0 ? year - 1 : year;
  const prevLast = new Date(prevYear, prevMonth + 1, 0).getDate();

  for (let i = 0; i < startPad; i++) {
    const d = prevLast - startPad + i + 1;
    days.push({
      date: new Date(prevYear, prevMonth, d),
      label: d,
      isCurrentMonth: false,
      isPast: isPast(new Date(prevYear, prevMonth, d)),
      isToday: false,
    });
  }

  const today = new Date();
  for (let d = 1; d <= last.getDate(); d++) {
    const date = new Date(year, month, d);
    days.push({
      date,
      label: d,
      isCurrentMonth: true,
      isPast: isPast(date),
      isToday: isSameDay(date, today),
    });
  }

  const remaining = 42 - days.length;
  for (let d = 1; d <= remaining; d++) {
    const date = new Date(year, month + 1, d);
    days.push({
      date,
      label: d,
      isCurrentMonth: false,
      isPast: true,
      isToday: false,
    });
  }

  return days;
};

const formatSelectedDay = (date) => {
  if (!date) return "";
  const d = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"][date.getDay()];
  const m = MONTHS[date.getMonth()];
  const yy = String(date.getFullYear()).slice(-2);
  return `${d}, ${m} ${date.getDate()}, ${yy}`;
};

const fadeInUp = keyframes`
  0% { opacity: 0; transform: translateY(16px); }
  100% { opacity: 1; transform: translateY(0); }
`;
const fadeIn = keyframes`
  0% { opacity: 0; }
  100% { opacity: 1; }
`;
const softPulse = keyframes`
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.9; transform: scale(1.02); }
`;

const ProjectDemo = () => {
  const theme = useTheme();
  const [step, setStep] = useState(1);

  const [viewDate, setViewDate] = useState(() => new Date());
  const [selectedDate, setSelectedDate] = useState(() => new Date());
  const [selectedTime, setSelectedTime] = useState("");
  const [timeZone, setTimeZone] = useState("Asia/Kolkata");

  const [formValues, setFormValues] = useState({
    name: "",
    email: "",
    countryCode: "+91",
    contactNumber: "",
    inviteGuests: "",
    companyName: "",
    noOfEmployees: "",
    agencyType: "",
    destinations: "",
    processes: [],
    officeLocation: "",
    hearAbout: "",
  });

  const [submitting, setSubmitting] = useState(false);
  const [successOpen, setSuccessOpen] = useState(false);
  const [errors, setErrors] = useState({});

  const viewYear = viewDate.getFullYear();
  const viewMonth = viewDate.getMonth();
  const calendarDays = useMemo(
    () => getCalendarDays(viewYear, viewMonth),
    [viewYear, viewMonth]
  );

  const handlePrevMonth = () => {
    setViewDate((d) => new Date(d.getFullYear(), d.getMonth() - 1));
  };
  const handleNextMonth = () => {
    setViewDate((d) => new Date(d.getFullYear(), d.getMonth() + 1));
  };

  const handleChange = (field) => (e) => {
    setFormValues((prev) => ({ ...prev, [field]: e.target.value }));
    if (errors[field]) setErrors((prev) => ({ ...prev, [field]: "" }));
  };

  const handleToggleProcess = (value) => (e) => {
    const checked = e.target.checked;
    setFormValues((prev) => {
      const current = new Set(prev.processes);
      if (checked) {
        current.add(value);
      } else {
        current.delete(value);
      }
      return { ...prev, processes: Array.from(current) };
    });
    if (errors.processes) setErrors((prev) => ({ ...prev, processes: "" }));
  };

  const validateForm = () => {
    const e = {};
    if (!formValues.name?.trim()) e.name = "Name is required";
    if (!formValues.email?.trim()) e.email = "Email is required";
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formValues.email.trim())) e.email = "Enter a valid email address";
    if (!formValues.contactNumber?.trim()) e.contactNumber = "Contact number is required";
    else if (!/^[\d\s\-+()]{6,20}$/.test(formValues.contactNumber.trim())) e.contactNumber = "Enter a valid contact number";
    if (formValues.inviteGuests?.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+(\s*,\s*[^\s@]+@[^\s@]+\.[^\s@]+)*$/.test(formValues.inviteGuests.trim())) e.inviteGuests = "Enter valid email(s), comma-separated";
    if (!formValues.companyName?.trim()) e.companyName = "Company name is required";
    if (!formValues.noOfEmployees?.trim()) e.noOfEmployees = "Number of employees is required";
    if (!formValues.agencyType?.trim()) e.agencyType = "Please select agency type";
    if (!formValues.destinations?.trim()) e.destinations = "Destinations you serve is required";
    if (!formValues.officeLocation?.trim()) e.officeLocation = "Office location is required";
    if (!formValues.hearAbout?.trim()) e.hearAbout = "Please select how you heard about us";
    if (!formValues.processes?.length) e.processes = "Select at least one process";
    return e;
  };

  const canGoNext = selectedDate && selectedTime && !selectedDate.isPast;
  const handleNext = () => {
    if (canGoNext) setStep(2);
  };

  const handleBack = () => setStep(1);

  const handleSubmit = (e) => {
    e.preventDefault();
    const formErrors = validateForm();
    if (Object.keys(formErrors).length > 0) {
      setErrors(formErrors);
      return;
    }
    setErrors({});
    setSubmitting(true);
    setTimeout(() => {
      setSubmitting(false);
      setSuccessOpen(true);
      setStep(1);
      setSelectedDate(null);
      setSelectedTime("");
      setFormValues({
        name: "",
        email: "",
        countryCode: "+91",
        contactNumber: "",
        inviteGuests: "",
        companyName: "",
        noOfEmployees: "",
        agencyType: "",
        destinations: "",
        processes: [],
        officeLocation: "",
        hearAbout: "",
      });
    }, 800);
  };

  const primaryColor = theme.palette.primary?.main || "#6366f1";
  const primaryLight = theme.palette.primary?.light || "#818cf8";
  const accentTeal = "#14b8a6";
  const accentAmber = "#f59e0b";

  return (
    <>
      <MetaComponent meta={metadata} />
      <LandingNavbar />

      <Box
        sx={{
          py: { xs: 3, md: 4 },
          px: 2,
          minHeight: "100vh",
          background:
            theme.palette.mode === "light"
              ? "linear-gradient(160deg, #f0f9ff 0%, #e0f2fe 30%, #ede9fe 70%, #f5f3ff 100%)"
              : "radial-gradient(ellipse 80% 60% at 50% 0%, rgba(99,102,241,0.15), transparent), radial-gradient(circle at bottom,#0f172a,#020617)",
        }}
      >
        <Container maxWidth="lg">
          <Box
            sx={{
              textAlign: "center",
              mb: 3,
              animation: `${fadeInUp} 0.6s ease-out`,
            }}
          >
            <Typography
              variant="h4"
              fontWeight={700}
              sx={{
                mb: 1,
                background: theme.palette.mode === "light" ? "linear-gradient(135deg, #1e293b 0%, #475569 100%)" : "linear-gradient(135deg, #f1f5f9, #cbd5e1)",
                backgroundClip: "text",
                WebkitBackgroundClip: "text",
                color: "transparent",
              }}
            >
              A personalised demo built for travel teams
            </Typography>
            <Typography
              variant="body2"
              color="text.secondary"
              sx={{ maxWidth: 480, mx: "auto" }}
            >
              See real workflows, real data and how Travclicks can streamline your day‑to‑day
              operations.
            </Typography>
            <Box
              sx={{
                mt: 3,
                display: "inline-flex",
                alignItems: "center",
                borderRadius: 999,
                p: 0.5,
                bgcolor:
                  theme.palette.mode === "light"
                    ? "rgba(99,102,241,0.08)"
                    : "rgba(148,163,184,0.12)",
                border: `1px solid ${theme.palette.mode === "light" ? "rgba(99,102,241,0.2)" : "rgba(148,163,184,0.2)"}`,
                transition: "all 0.3s ease",
              }}
            >
              {[{ label: "1. Date & time", value: 1 }, { label: "2. Your details", value: 2 }].map(
                ({ label, value }) => (
                  <Box
                    key={value}
                    sx={{
                      px: 2.5,
                      py: 0.75,
                      borderRadius: 999,
                      fontSize: 13,
                      fontWeight: 600,
                      color: step === value ? "#fff" : "text.secondary",
                      bgcolor: step === value ? primaryColor : "transparent",
                      transition: "all 0.3s cubic-bezier(0.4, 0, 0.2, 1)",
                      "&:hover": step !== value ? { bgcolor: "rgba(99,102,241,0.08)" } : {},
                    }}
                  >
                    {label}
                  </Box>
                )
              )}
            </Box>
          </Box>

          <Grid container spacing={2.5} alignItems="stretch">
            {/* Left: main content */}
            <Grid item xs={12} lg={8}>
              <Stack spacing={1} sx={{ height: "100%" }}>
          <Card
            variant="outlined"
            sx={{
              borderRadius: 3,
              borderColor:
                theme.palette.mode === "light"
                  ? "rgba(99,102,241,0.25)"
                  : "rgba(129,140,248,0.3)",
              boxShadow:
                theme.palette.mode === "light"
                  ? "0 4px 24px rgba(99,102,241,0.08), 0 1px 3px rgba(0,0,0,0.06)"
                  : "0 4px 24px rgba(0,0,0,0.2)",
              overflow: "hidden",
              animation: `${fadeInUp} 0.5s ease-out 0.1s both`,
              transition: "box-shadow 0.3s ease, transform 0.3s ease",
              "&:hover": {
                boxShadow:
                  theme.palette.mode === "light"
                    ? "0 12px 40px rgba(99,102,241,0.12), 0 2px 8px rgba(0,0,0,0.06)"
                    : "0 12px 40px rgba(0,0,0,0.3)",
              },
            }}
          >
            <CardContent sx={{ p: { xs: 2.5, md: 3 } }}>
              <Stack spacing={1.5}>
                <Stack direction="row" spacing={1.5} alignItems="center">
                  <Box
                    sx={{
                      width: 44,
                      height: 44,
                      borderRadius: 2,
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "center",
                      background: `linear-gradient(135deg, ${accentTeal}, #0d9488)`,
                      color: "#fff",
                      boxShadow: `0 4px 14px rgba(20,184,166,0.4)`,
                    }}
                  >
                    <Schedule sx={{ fontSize: 24 }} />
                  </Box>
                  <Box>
                    <Typography
                      variant="overline"
                      sx={{ letterSpacing: 0.8, color: "text.secondary", display: "block" }}
                    >
                      Travclicks Product Demo
                    </Typography>
                    <Typography variant="h6" fontWeight={600}>
                      Travclicks Travel Software Demo Meeting
                    </Typography>
                  </Box>
                </Stack>
                <Stack direction="row" spacing={2} alignItems="center" flexWrap="wrap">
                  <Stack direction="row" spacing={0.75} alignItems="center">
                    <AccessTime sx={{ fontSize: 18, color: primaryColor }} />
                    <Typography variant="body2" color="text.secondary">
                      30 mins
                    </Typography>
                  </Stack>
                  <Box
                    sx={{
                      px: 1.5,
                      py: 0.5,
                      borderRadius: 999,
                      bgcolor:
                        theme.palette.mode === "light"
                          ? "rgba(20,184,166,0.12)"
                          : "rgba(20,184,166,0.25)",
                      border: `1px solid ${theme.palette.mode === "light" ? "rgba(20,184,166,0.3)" : "rgba(20,184,166,0.4)"}`,
                    }}
                  >
                    <Typography
                      variant="caption"
                      sx={{ color: accentTeal, fontWeight: 600 }}
                    >
                      Live product walkthrough
                    </Typography>
                  </Box>
                </Stack>
                <Typography variant="body2" color="text.secondary" sx={{ lineHeight: 1.6 }}>
                  Travclicks helps DMCs, tour operators and travel agencies manage enquiries,
                  itineraries, bookings and supplier operations in one place – used by teams
                  across multiple destinations.
                </Typography>
              </Stack>
            </CardContent>
          </Card>

          {step === 1 && (
            <>
              <Card
                variant="outlined"
                sx={{
                  borderRadius: 3,
                  overflow: "hidden",
                  borderColor:
                    theme.palette.mode === "light"
                      ? "rgba(99,102,241,0.25)"
                      : "rgba(129,140,248,0.3)",
                  boxShadow:
                    theme.palette.mode === "light"
                      ? "0 4px 24px rgba(99,102,241,0.08), 0 1px 3px rgba(0,0,0,0.06)"
                      : "0 4px 24px rgba(0,0,0,0.2)",
                  animation: `${fadeInUp} 0.5s ease-out 0.15s both`,
                  transition: "box-shadow 0.3s ease",
                  "&:hover": {
                    boxShadow:
                      theme.palette.mode === "light"
                        ? "0 12px 40px rgba(99,102,241,0.12)"
                        : "0 12px 40px rgba(0,0,0,0.3)",
                  },
                }}
              >
                <CardContent sx={{ p: 3 }}>
                  <Stack direction="row" alignItems="center" justifyContent="center" spacing={1} sx={{ mb: 3 }}>
                    <CalendarMonth sx={{ color: primaryColor, fontSize: 22 }} />
                    <Typography variant="subtitle1" fontWeight={600}>
                      Select date and time
                    </Typography>
                  </Stack>
                  <Grid container spacing={3} alignItems="flex-start">
                    {/* Left: Calendar */}
                    <Grid item xs={12} md={6}>
                      <Box sx={{ display: "flex", alignItems: "center", justifyContent: "space-between", mb: 2 }}>
                        <IconButton
                          onClick={handlePrevMonth}
                          size="small"
                          sx={{
                            border: 1,
                            borderColor: "rgba(99,102,241,0.3)",
                            borderRadius: 2,
                            color: primaryColor,
                            transition: "all 0.2s ease",
                            "&:hover": { bgcolor: "rgba(99,102,241,0.1)", borderColor: primaryColor },
                          }}
                        >
                          <ChevronLeft />
                        </IconButton>
                        <Typography variant="subtitle1" fontWeight={600} sx={{ color: "text.primary" }}>
                          {MONTHS[viewMonth]} {viewYear}
                        </Typography>
                        <IconButton
                          onClick={handleNextMonth}
                          size="small"
                          sx={{
                            border: 1,
                            borderColor: "rgba(99,102,241,0.3)",
                            borderRadius: 2,
                            color: primaryColor,
                            transition: "all 0.2s ease",
                            "&:hover": { bgcolor: "rgba(99,102,241,0.1)", borderColor: primaryColor },
                          }}
                        >
                          <ChevronRight />
                        </IconButton>
                      </Box>

                      <Grid container columns={7} sx={{ mb: 2 }}>
                        {WEEKDAYS.map((day, i) => (
                          <Grid item xs={1} key={i}>
                            <Typography variant="caption" fontWeight="600" color="text.secondary" textAlign="center" display="block">
                              {day}
                            </Typography>
                          </Grid>
                        ))}
                        {calendarDays.map((cell, i) => {
                          const selected = selectedDate && isSameDay(cell.date, selectedDate);
                          const disabled = cell.isPast || !cell.isCurrentMonth;
                          return (
                            <Grid item xs={1} key={i}>
                              <Box
                                onClick={() => {
                                  if (disabled) return;
                                  setSelectedDate(cell.date);
                                }}
                                sx={{
                                  py: 0.75,
                                  textAlign: "center",
                                  borderRadius: 1.5,
                                  cursor: disabled ? "default" : "pointer",
                                  bgcolor: selected ? primaryColor : "transparent",
                                  color: selected ? "#fff" : disabled ? "text.disabled" : "text.primary",
                                  position: "relative",
                                  transition: "all 0.2s ease",
                                  "&:hover": disabled ? {} : {
                                    bgcolor: selected ? primaryColor : "rgba(99,102,241,0.12)",
                                    transform: selected ? "none" : "scale(1.05)",
                                  },
                                }}
                              >
                                {cell.label}
                                {cell.isToday && !selected && (
                                  <Box
                                    sx={{
                                      position: "absolute",
                                      bottom: 2,
                                      left: "50%",
                                      transform: "translateX(-50%)",
                                      width: 0,
                                      height: 0,
                                      borderLeft: "4px solid transparent",
                                      borderRight: "4px solid transparent",
                                      borderTop: `4px solid ${theme.palette.text.primary}`,
                                    }}
                                  />
                                )}
                              </Box>
                            </Grid>
                          );
                        })}
                      </Grid>

                      <TextField
                        select
                        fullWidth
                        size="small"
                        label="Time Zone"
                        value={timeZone}
                        onChange={(e) => setTimeZone(e.target.value)}
                        sx={{ mt: 2 }}
                      >
                        {TIME_ZONES.map((tz) => (
                          <MenuItem key={tz.value} value={tz.value}>
                            {tz.label}
                          </MenuItem>
                        ))}
                      </TextField>
                    </Grid>

                    {/* Right: Time slots */}
                    <Grid item xs={12} md={6}>
                      <Box
                        sx={{
                          maxWidth: 260,
                          mx: "auto",
                        }}
                      >
                        <Stack direction="row" alignItems="center" justifyContent="center" spacing={0.75} sx={{ mb: 2 }}>
                          <Schedule sx={{ fontSize: 18, color: primaryColor }} />
                          <Typography variant="subtitle1" fontWeight={600}>
                            {formatSelectedDay(selectedDate)}
                          </Typography>
                        </Stack>
                        <Stack spacing={1} sx={{ maxHeight: 320, overflowY: "auto", '&::-webkit-scrollbar': { width: 6 }, '&::-webkit-scrollbar-thumb': { borderRadius: 3, bgcolor: 'rgba(99,102,241,0.3)' } }}>
                          {TIME_SLOTS.map((slot) => {
                            const isSelected = selectedTime === slot;
                            return (
                              <Box
                                key={slot}
                                sx={{
                                  display: "flex",
                                  alignItems: "center",
                                  gap: 1,
                                  width: "100%",
                                }}
                              >
                                <Button
                                  variant={isSelected ? "contained" : "outlined"}
                                  size="small"
                                  onClick={() => setSelectedTime(slot)}
                                  sx={{
                                    flex: 1,
                                    justifyContent: "center",
                                    borderRadius: 2,
                                    transition: "all 0.3s cubic-bezier(0.4, 0, 0.2, 1)",
                                    ...(isSelected && {
                                      bgcolor: primaryColor,
                                      borderColor: primaryLight,
                                      boxShadow: `0 4px 14px ${primaryColor}40`,
                                      animation: `${fadeInUp} 0.35s ease-out`,
                                      "&:hover": { bgcolor: primaryColor, opacity: 0.95, transform: "translateY(-1px)" },
                                    }),
                                    ...(!isSelected && {
                                      "&:hover": { borderColor: primaryColor, color: primaryColor, bgcolor: "rgba(99,102,241,0.06)" },
                                    }),
                                  }}
                                >
                                  {slot}
                                </Button>
                                <Box
                                  sx={{
                                    flex: isSelected ? 1 : 0,
                                    minWidth: isSelected ? undefined : 0,
                                    overflow: "hidden",
                                    transition: "flex 0.35s cubic-bezier(0.4, 0, 0.2, 1), min-width 0.35s ease",
                                  }}
                                >
                                  <Fade in={isSelected} timeout={{ enter: 350, exit: 200 }}>
                                    <Box sx={{ display: "block", width: "100%" }}>
                                      <Grow in={isSelected} timeout={{ enter: 400 }} style={{ transformOrigin: "left center" }}>
                                        <Button
                                          variant="contained"
                                          size="small"
                                          fullWidth
                                          onClick={handleNext}
                                          sx={{
                                            justifyContent: "center",
                                            borderRadius: 2,
                                            bgcolor: accentTeal,
                                            transition: "all 0.25s ease",
                                            "&:hover": { bgcolor: "#0d9488", transform: "translateY(-1px)", boxShadow: `0 6px 20px ${accentTeal}50` },
                                          }}
                                        >
                                          Next
                                        </Button>
                                      </Grow>
                                    </Box>
                                  </Fade>
                                </Box>
                              </Box>
                            );
                          })}
                        </Stack>
                      </Box>
                    </Grid>
                  </Grid>
                </CardContent>
              </Card>
            </>
          )}

          {step === 2 && (
            <>
              <Box
                sx={{
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "space-between",
                  mb: 2,
                  flexWrap: "wrap",
                  gap: 1,
                  animation: `${fadeIn} 0.4s ease-out`,
                }}
              >
                <Button
                  startIcon={<ArrowBack />}
                  onClick={handleBack}
                  variant="outlined"
                  size="small"
                  sx={{
                    borderRadius: 2,
                    borderColor: primaryColor,
                    color: primaryColor,
                    "&:hover": { borderColor: primaryLight, bgcolor: "rgba(99,102,241,0.08)" },
                  }}
                >
                  Back
                </Button>
                {selectedDate && selectedTime && (
                  <Stack direction="row" alignItems="center" spacing={0.5}>
                    <CalendarMonth sx={{ fontSize: 16, color: primaryColor }} />
                    <Typography variant="body2" color="text.secondary" fontWeight={500}>
                      {formatSelectedDay(selectedDate)} at {selectedTime}
                    </Typography>
                  </Stack>
                )}
              </Box>

              <Card
                variant="outlined"
                sx={{
                  borderRadius: 3,
                  borderColor:
                    theme.palette.mode === "light"
                      ? "rgba(99,102,241,0.25)"
                      : "rgba(129,140,248,0.3)",
                  boxShadow:
                    theme.palette.mode === "light"
                      ? "0 4px 24px rgba(99,102,241,0.08), 0 1px 3px rgba(0,0,0,0.06)"
                      : "0 4px 24px rgba(0,0,0,0.2)",
                  animation: `${fadeInUp} 0.5s ease-out both`,
                  "&:hover": { boxShadow: theme.palette.mode === "light" ? "0 12px 40px rgba(99,102,241,0.12)" : "0 12px 40px rgba(0,0,0,0.3)" },
                }}
              >
                <CardContent sx={{ p: 3 }}>
                  <form onSubmit={handleSubmit} noValidate>
                    <Grid container spacing={2.5} alignItems="flex-start">
                      <Grid item xs={12} sm={6}>
                        <TextField
                          fullWidth
                          required
                          label="Name"
                          placeholder="Name"
                          value={formValues.name}
                          onChange={handleChange("name")}
                          error={!!errors.name}
                          helperText={errors.name}
                          InputProps={{
                            startAdornment: (
                              <InputAdornment position="start">
                                <Person sx={{ color: primaryColor, fontSize: 20 }} />
                              </InputAdornment>
                            ),
                          }}
                          sx={{ "& .MuiOutlinedInput-root": { borderRadius: 2 } }}
                        />
                      </Grid>
                      <Grid item xs={12} sm={6}>
                        <TextField
                          fullWidth
                          required
                          type="email"
                          label="Email"
                          placeholder="Email"
                          value={formValues.email}
                          onChange={handleChange("email")}
                          error={!!errors.email}
                          helperText={errors.email}
                          InputProps={{
                            startAdornment: (
                              <InputAdornment position="start">
                                <Email sx={{ color: primaryColor, fontSize: 20 }} />
                              </InputAdornment>
                            ),
                          }}
                          sx={{ "& .MuiOutlinedInput-root": { borderRadius: 2 } }}
                        />
                      </Grid>
                      <Grid item xs={12}>
                        <Box>
                          <Typography variant="caption" color="text.secondary" sx={{ display: "flex", alignItems: "center", gap: 0.5, mb: 0.5 }}>
                            <Phone sx={{ fontSize: 14, color: primaryColor }} /> Contact Number *
                          </Typography>
                          <Box sx={{ display: "flex", gap: 1 }}>
                            <TextField
                              select
                              value={formValues.countryCode}
                              onChange={handleChange("countryCode")}
                              sx={{ minWidth: 120, "& .MuiOutlinedInput-root": { borderRadius: 2 } }}
                              size="small"
                              variant="outlined"
                            >
                              {COUNTRY_CODES.map((c) => (
                                <MenuItem key={c.code} value={c.code}>
                                  {c.flag} {c.code}
                                </MenuItem>
                              ))}
                            </TextField>
                            <TextField
                              fullWidth
                              required
                              placeholder="Contact Number"
                              value={formValues.contactNumber}
                              onChange={handleChange("contactNumber")}
                              size="small"
                              error={!!errors.contactNumber}
                              helperText={errors.contactNumber}
                              sx={{ "& .MuiOutlinedInput-root": { borderRadius: 2 } }}
                            />
                          </Box>
                        </Box>
                      </Grid>
                      <Grid item xs={12} sm={6}>
                        <TextField
                          fullWidth
                          label="Invite Guest(s)"
                          placeholder="Guest emails (optional)"
                          value={formValues.inviteGuests}
                          onChange={handleChange("inviteGuests")}
                          error={!!errors.inviteGuests}
                          helperText={errors.inviteGuests}
                          InputProps={{
                            startAdornment: (
                              <InputAdornment position="start">
                                <Groups sx={{ color: primaryColor, fontSize: 20 }} />
                              </InputAdornment>
                            ),
                            endAdornment: (
                              <Tooltip title="Add email addresses of colleagues you want to include in the demo.">
                                <InfoOutlined sx={{ fontSize: 18, color: "text.disabled", cursor: "help", mr: 0.5 }} />
                              </Tooltip>
                            ),
                          }}
                          sx={{ "& .MuiOutlinedInput-root": { borderRadius: 2 } }}
                        />
                      </Grid>
                      <Grid item xs={12} sm={6}>
                        <TextField
                          fullWidth
                          required
                          label="Company Name"
                          placeholder="Company Name"
                          value={formValues.companyName}
                          onChange={handleChange("companyName")}
                          error={!!errors.companyName}
                          helperText={errors.companyName}
                          InputProps={{
                            startAdornment: (
                              <InputAdornment position="start">
                                <Business sx={{ color: primaryColor, fontSize: 20 }} />
                              </InputAdornment>
                            ),
                          }}
                          sx={{ "& .MuiOutlinedInput-root": { borderRadius: 2 } }}
                        />
                      </Grid>
                      <Grid item xs={12} sm={6}>
                        <TextField
                          fullWidth
                          required
                          label="No of Employees"
                          placeholder="e.g. 1-10, 11-50, 51-200"
                          value={formValues.noOfEmployees}
                          onChange={handleChange("noOfEmployees")}
                          error={!!errors.noOfEmployees}
                          helperText={errors.noOfEmployees}
                          InputProps={{
                            startAdornment: (
                              <InputAdornment position="start">
                                <Groups sx={{ color: primaryColor, fontSize: 20 }} />
                              </InputAdornment>
                            ),
                          }}
                          sx={{ "& .MuiOutlinedInput-root": { borderRadius: 2 } }}
                        />
                      </Grid>
                      <Grid item xs={12} sm={6}>
                        <TextField
                          select
                          fullWidth
                          required
                          label="Specify the type of Travel agency"
                          value={formValues.agencyType}
                          onChange={handleChange("agencyType")}
                          error={!!errors.agencyType}
                          helperText={errors.agencyType}
                          InputProps={{
                            startAdornment: (
                              <InputAdornment position="start">
                                <Business sx={{ color: primaryColor, fontSize: 20 }} />
                              </InputAdornment>
                            ),
                          }}
                          sx={{ "& .MuiOutlinedInput-root": { borderRadius: 2 } }}
                        >
                          <MenuItem value="">Select Option</MenuItem>
                          {AGENCY_TYPES.map((type) => (
                            <MenuItem key={type} value={type}>
                              {type}
                            </MenuItem>
                          ))}
                        </TextField>
                      </Grid>
                      <Grid item xs={12}>
                        <TextField
                          fullWidth
                          required
                          multiline
                          minRows={2}
                          label="Name of Destinations you serve"
                          placeholder="Name of Destinations you serve"
                          value={formValues.destinations}
                          onChange={handleChange("destinations")}
                          error={!!errors.destinations}
                          helperText={errors.destinations}
                          InputProps={{
                            startAdornment: (
                              <InputAdornment position="start" sx={{ alignSelf: "flex-start", mt: 1.5 }}>
                                <Public sx={{ color: primaryColor, fontSize: 20 }} />
                              </InputAdornment>
                            ),
                          }}
                          sx={{ "& .MuiOutlinedInput-root": { borderRadius: 2 } }}
                        />
                      </Grid>
                      <Grid item xs={12}>
                        <Box>
                          <Typography
                            variant="caption"
                            color={errors.processes ? "error.main" : "text.secondary"}
                            sx={{ display: "block", mb: 0.5 }}
                          >
                            Processes you want to automate with Software *
                          </Typography>
                          <Grid container spacing={1.5}>
                            {PROCESS_OPTIONS.map((option) => (
                              <Grid item xs={12} sm={4} key={option}>
                                <FormControlLabel
                                  control={
                                    <Checkbox
                                      size="small"
                                      checked={formValues.processes.includes(option)}
                                      onChange={handleToggleProcess(option)}
                                    />
                                  }
                                  label={
                                    <Typography variant="body2" color="text.primary">
                                      {option}
                                    </Typography>
                                  }
                                  sx={{ mr: 0 }}
                                />
                              </Grid>
                            ))}
                          </Grid>
                          {errors.processes && (
                            <Typography variant="caption" color="error.main" sx={{ display: "block", mt: 0.5 }}>
                              {errors.processes}
                            </Typography>
                          )}
                        </Box>
                      </Grid>
                      <Grid item xs={12} sm={6}>
                        <TextField
                          fullWidth
                          required
                          label="Office Location"
                          placeholder="Office Location"
                          value={formValues.officeLocation}
                          onChange={handleChange("officeLocation")}
                          error={!!errors.officeLocation}
                          helperText={errors.officeLocation}
                          InputProps={{
                            startAdornment: (
                              <InputAdornment position="start">
                                <LocationOn sx={{ color: primaryColor, fontSize: 20 }} />
                              </InputAdornment>
                            ),
                          }}
                          sx={{ "& .MuiOutlinedInput-root": { borderRadius: 2 } }}
                        />
                      </Grid>
                      <Grid item xs={12} sm={6}>
                        <TextField
                          select
                          fullWidth
                          required
                          label="How did you first hear about Travclicks?"
                          value={formValues.hearAbout}
                          onChange={handleChange("hearAbout")}
                          error={!!errors.hearAbout}
                          helperText={errors.hearAbout}
                          InputProps={{
                            startAdornment: (
                              <InputAdornment position="start">
                                <Campaign sx={{ color: primaryColor, fontSize: 20 }} />
                              </InputAdornment>
                            ),
                          }}
                          sx={{ "& .MuiOutlinedInput-root": { borderRadius: 2 } }}
                        >
                          <MenuItem value="">Select Option</MenuItem>
                          <MenuItem value="Search engine">Search engine</MenuItem>
                          <MenuItem value="Social media">Social media</MenuItem>
                          <MenuItem value="Referral">Referral</MenuItem>
                          <MenuItem value="Event / conference">Event / conference</MenuItem>
                          <MenuItem value="Other">Other</MenuItem>
                        </TextField>
                      </Grid>
                      <Grid item xs={12}>
                      <Box sx={{ display: "flex", justifyContent: "space-between", pt: 2, gap: 2 }}>
                        <Button
                          type="button"
                          variant="outlined"
                          onClick={handleBack}
                          startIcon={<ArrowBack />}
                          sx={{ borderRadius: 2, borderColor: primaryColor, color: primaryColor, "&:hover": { borderColor: primaryLight, bgcolor: "rgba(99,102,241,0.08)" } }}
                        >
                          Back
                        </Button>
                        <Button
                          type="submit"
                          variant="contained"
                          disabled={submitting}
                          sx={{
                            borderRadius: 2,
                            px: 3,
                            background: `linear-gradient(135deg, ${accentTeal}, #0d9488)`,
                            boxShadow: `0 4px 14px ${accentTeal}40`,
                            "&:hover": { background: "linear-gradient(135deg, #0d9488, #0f766e)", boxShadow: `0 6px 20px ${accentTeal}50` },
                          }}
                        >
                          {submitting ? "Submitting..." : "Schedule appointment"}
                        </Button>
                      </Box>
                      </Grid>
                    </Grid>
                  </form>
                </CardContent>
              </Card>
            </>
          )}
              </Stack>
            </Grid>

            {/* Right: sidebar */}
            <Grid item xs={12} lg={4}>
              <Box
                sx={{
                  position: { lg: "sticky" },
                  top: { lg: 24 },
                  borderRadius: 3,
                  p: 3,
                  minHeight: 260,
                  background:
                    theme.palette.mode === "light"
                      ? "linear-gradient(180deg, #ede9fe 0%, #e0e7ff 50%, #f5f3ff 100%)"
                      : "linear-gradient(180deg, rgba(99,102,241,0.18) 0%, rgba(79,70,229,0.12) 100%)",
                  border: `1px solid ${theme.palette.mode === "light" ? "rgba(99,102,241,0.25)" : "rgba(129,140,248,0.25)"}`,
                  boxShadow: theme.palette.mode === "light" ? "0 4px 24px rgba(99,102,241,0.1)" : "none",
                  animation: `${fadeInUp} 0.5s ease-out 0.2s both`,
                }}
              >
                <Stack spacing={3}>
                  <Box>
                    <Stack direction="row" spacing={1.5} alignItems="center">
                      <Box
                        sx={{
                          width: 44,
                          height: 44,
                          borderRadius: 2,
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          bgcolor: "rgba(245,158,11,0.2)",
                          border: "1px solid rgba(245,158,11,0.4)",
                          transition: "transform 0.3s ease, box-shadow 0.3s ease",
                          "&:hover": { transform: "scale(1.05)", boxShadow: "0 4px 12px rgba(245,158,11,0.3)" },
                        }}
                      >
                        <Star sx={{ fontSize: 26, color: accentAmber }} />
                      </Box>
                      <Box>
                        <Typography variant="subtitle1" fontWeight={700}>
                          Proven Travel CRM
                        </Typography>
                        <Typography variant="body2" color="text.secondary">
                          4.9 Star Google Rating
                        </Typography>
                      </Box>
                    </Stack>
                  </Box>
                  <Box>
                    <Stack direction="row" spacing={1.5} alignItems="center">
                      <Box
                        sx={{
                          width: 44,
                          height: 44,
                          borderRadius: 2,
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          bgcolor: "rgba(245,158,11,0.2)",
                          border: "1px solid rgba(245,158,11,0.4)",
                          transition: "transform 0.3s ease, box-shadow 0.3s ease",
                          "&:hover": { transform: "scale(1.05)", boxShadow: "0 4px 12px rgba(245,158,11,0.3)" },
                        }}
                      >
                        <HeadsetMic sx={{ fontSize: 26, color: accentAmber }} />
                      </Box>
                      <Box>
                        <Typography variant="subtitle1" fontWeight={700}>
                          Real Support
                        </Typography>
                        <Typography variant="body2" color="text.secondary">
                          No bots, only experts
                        </Typography>
                      </Box>
                    </Stack>
                  </Box>
                  <Box>
                    <Stack direction="row" spacing={1.5} alignItems="center">
                      <Box
                        sx={{
                          width: 44,
                          height: 44,
                          borderRadius: 2,
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          bgcolor: "rgba(245,158,11,0.2)",
                          border: "1px solid rgba(245,158,11,0.4)",
                          transition: "transform 0.3s ease, box-shadow 0.3s ease",
                          "&:hover": { transform: "scale(1.05)", boxShadow: "0 4px 12px rgba(245,158,11,0.3)" },
                        }}
                      >
                        <WorkspacePremium sx={{ fontSize: 26, color: accentAmber }} />
                      </Box>
                      <Box>
                        <Typography variant="subtitle1" fontWeight={700}>
                          Industry Built
                        </Typography>
                        <Typography variant="body2" color="text.secondary">
                          Designed exclusively for DMCs
                        </Typography>
                      </Box>
                    </Stack>
                  </Box>
                  <Box
                    sx={{
                      pt: 2,
                      borderTop: `1px solid ${
                        theme.palette.mode === "light"
                          ? "rgba(139,92,246,0.2)"
                          : "rgba(129,140,248,0.2)"
                      }`,
                    }}
                  >
                    <Box sx={{ mb: 1.5 }}>
                      <Box
                        sx={{
                          display: "inline-flex",
                          px: 1.5,
                          py: 0.75,
                          borderRadius: 2,
                          bgcolor:
                            theme.palette.mode === "light"
                              ? "rgba(255,255,255,0.9)"
                              : "rgba(15,23,42,0.6)",
                          boxShadow: "0 6px 18px rgba(15,23,42,0.12)",
                        }}
                      >
                        <Typography
                          variant="caption"
                          fontWeight={700}
                          sx={{ letterSpacing: 0.4, color: primaryColor }}
                        >
                          Neptune Holidays · Customer Story
                        </Typography>
                      </Box>
                    </Box>
                    <Typography
                      sx={{
                        fontStyle: "italic",
                        color: "text.secondary",
                        mb: 2,
                        fontSize: "1.0625rem",
                        lineHeight: 1.55,
                      }}
                    >
                      &ldquo;Employees can easily find and book the most cost‑effective and
                      convenient travel arrangements. The software&apos;s ability to consolidate
                      various travel options into a single, easy‑to‑navigate platform has been a
                      game changer for our team.&rdquo;
                    </Typography>
                    <Box sx={{ display: "flex", flexDirection: "column", alignItems: "center", gap: 1, mt: 1 }}>
                      <Box
                        component="img"
                        src="/Images/dilip%20das.jpeg"
                        alt="Dilip Das"
                        sx={{
                          width: 110,
                          height: 130,
                          borderRadius: 4,
                          objectFit: "cover",
                          display: "block",
                          boxShadow: "0 4px 20px rgba(0,0,0,0.12)",
                        }}
                      />
                      <Typography
                        sx={{
                          fontSize: "0.8125rem",
                          color: "text.secondary",
                          fontStyle: "normal",
                        }}
                      >
                        — Dilip Das, CEO
                      </Typography>
                    </Box>
                  </Box>
                </Stack>
              </Box>
            </Grid>
          </Grid>
        </Container>
      </Box>

      <Snackbar
        open={successOpen}
        autoHideDuration={4000}
        onClose={() => setSuccessOpen(false)}
        anchorOrigin={{ vertical: "bottom", horizontal: "center" }}
      >
        <Alert onClose={() => setSuccessOpen(false)} severity="success" sx={{ width: "100%" }}>
          Your demo request has been received. Our team will contact you shortly to confirm the slot.
        </Alert>
      </Snackbar>

      <Footer />
    </>
  );
};

export default ProjectDemo;
