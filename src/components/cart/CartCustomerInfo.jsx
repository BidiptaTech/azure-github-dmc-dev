import {
  useState,
  forwardRef,
  useImperativeHandle,
  useEffect,
  useRef,
} from "react";
import {
  TextField,
  Box,
  Typography,
  FormControl,
  Select,
  MenuItem,
  InputAdornment,
  Stack,
  Divider,
  Chip,
} from "@mui/material";
import { useSelector } from "react-redux";
import { toast } from "react-toastify";
import {
  MANUAL_COUNTRY_VALUE,
  fetchCountryDialCodes,
  sanitizeDialCode,
} from "@/utils/countryCodes";

const emptyForm = {
  fullName: "",
  email: "",
  phone: "",
  countryCode: "+1",
  address1: "",
  address2: "",
  state: "",
  zip: "",
  specialRequests: "",
};

const fieldSx = {
  "& .MuiOutlinedInput-root": {
    borderRadius: 1.5,
    bgcolor: "#fff",
  },
  "& .MuiInputLabel-root": {
    fontSize: "0.875rem",
  },
  "& .MuiFormHelperText-root": {
    marginTop: "2px",
    marginLeft: 0,
  },
};

const hasCartCustomerInfo = (info) =>
  Boolean(
    info &&
      typeof info === "object" &&
      (String(info.fullName || "").trim() || String(info.email || "").trim())
  );

/** Stable section wrapper — must stay outside the form component to avoid remount/focus loss */
const FormSection = ({ title, children }) => (
  <Box sx={{ mb: 1.75 }}>
    <Typography
      variant="caption"
      fontWeight={700}
      color="#64748b"
      sx={{
        mb: 1,
        display: "block",
        textTransform: "uppercase",
        letterSpacing: "0.04em",
        fontSize: "0.7rem",
      }}
    >
      {title}
    </Typography>
    <Stack spacing={1.25}>{children}</Stack>
  </Box>
);

/**
 * Cart checkout customer form — same fields/validation as hotel CustomerInfo,
 * compact layout for checkout.
 * When cart trip already has customerInfo, form is prefilled and read-only.
 */
const CartCustomerInfo = forwardRef(function CartCustomerInfo(
  { onFormChange, initialCustomerInfo },
  ref
) {
  const searchLocation = useSelector((state) => state.bookings.searchLocation);
  const existingUserInfo = useSelector(
    (state) => state.customerInfo?.userInfo
  );
  const onFormChangeRef = useRef(onFormChange);
  onFormChangeRef.current = onFormChange;

  const [isStaticFromCart] = useState(() =>
    hasCartCustomerInfo(initialCustomerInfo)
  );

  const [countries, setCountries] = useState([]);
  const [countriesLoading, setCountriesLoading] = useState(true);
  const [selectedCountry, setSelectedCountry] = useState(null);
  const [dialMinLength, setDialMinLength] = useState(8);
  const [dialMaxLength, setDialMaxLength] = useState(15);
  const [isManualCountryCode, setIsManualCountryCode] = useState(false);
  const countryManuallyOverriddenRef = useRef(false);
  const hasInitializedCountryRef = useRef(false);
  const lockedFromCartRef = useRef(isStaticFromCart);
  const hasNotifiedInitialRef = useRef(false);

  const [form, setForm] = useState(() => {
    if (hasCartCustomerInfo(initialCustomerInfo)) {
      return { ...emptyForm, ...initialCustomerInfo };
    }
    if (existingUserInfo?.fullName) {
      return { ...emptyForm, ...existingUserInfo };
    }
    try {
      const raw = localStorage.getItem("lastHotelUserInfo");
      if (raw) return { ...emptyForm, ...JSON.parse(raw) };
    } catch {
      /* ignore */
    }
    return { ...emptyForm };
  });

  const [errors, setErrors] = useState({});
  const [touched, setTouched] = useState({});

  // Prefill once from cart customerInfo when form is locked
  useEffect(() => {
    if (!isStaticFromCart || !hasCartCustomerInfo(initialCustomerInfo)) return;
    lockedFromCartRef.current = true;
    const updated = { ...emptyForm, ...initialCustomerInfo };
    setForm(updated);
    onFormChangeRef.current?.(updated);
    // Only on mount lock — avoid resetting while parent re-renders
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isStaticFromCart]);

  useEffect(() => {
    if (hasNotifiedInitialRef.current) return;
    hasNotifiedInitialRef.current = true;
    onFormChangeRef.current?.(form);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    let cancelled = false;
    const loadCountries = async () => {
      setCountriesLoading(true);
      try {
        const normalized = await fetchCountryDialCodes({
          showToastOnError: false,
        });
        if (!cancelled) {
          setCountries(normalized || []);
        }
      } catch (error) {
        if (!cancelled) {
          setCountries([]);
          toast.error("Failed to load country codes");
        }
      } finally {
        if (!cancelled) setCountriesLoading(false);
      }
    };
    loadCountries();
    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    if (countryManuallyOverriddenRef.current) return;
    if (!countries.length || hasInitializedCountryRef.current) return;

    let defaultCountry = null;
    if (form.countryCode) {
      defaultCountry = countries.find(
        (c) => c.country_code === form.countryCode
      );
    }
    if (!defaultCountry && searchLocation?.[0] && !isStaticFromCart) {
      defaultCountry = countries.find(
        (c) =>
          String(c.code).toLowerCase() ===
          String(searchLocation[0]).toLowerCase()
      );
    }
    if (!defaultCountry) defaultCountry = countries[0];

    hasInitializedCountryRef.current = true;
    setSelectedCountry(defaultCountry);
    setIsManualCountryCode(
      Boolean(form.countryCode) &&
        !countries.some((c) => c.country_code === form.countryCode)
    );
    setDialMinLength(defaultCountry?.contact_min_length || 8);
    setDialMaxLength(defaultCountry?.contact_max_length || 15);

    if (isStaticFromCart) return;

    setForm((prev) => {
      const updated = {
        ...prev,
        countryCode: prev.countryCode || defaultCountry.country_code,
      };
      localStorage.setItem("lastHotelUserInfo", JSON.stringify(updated));
      onFormChangeRef.current?.(updated);
      return updated;
    });
    // Intentionally omit form.countryCode — only run once countries load
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [countries, searchLocation, isStaticFromCart]);

  const validateField = (name, value) => {
    switch (name) {
      case "fullName":
        if (!value?.trim()) return "Full name is required";
        if (value.length < 2) return "Name must be at least 2 characters";
        return "";
      case "email": {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!value) return "Email is required";
        if (!emailRegex.test(value)) return "Please enter a valid email";
        return "";
      }
      case "phone": {
        const phoneRegex = new RegExp(
          `^\\d{${dialMinLength},${dialMaxLength}}$`
        );
        if (!value) return "Phone number is required";
        if (!phoneRegex.test(value)) {
          return `Please enter a valid phone number (${dialMinLength}-${dialMaxLength} digits)`;
        }
        return "";
      }
      case "address1":
        if (!value?.trim()) return "Address is required";
        if (value.length < 5) return "Address must be at least 5 characters";
        return "";
      default:
        return "";
    }
  };

  const persist = (updated) => {
    if (lockedFromCartRef.current || isStaticFromCart) return;
    localStorage.setItem("lastHotelUserInfo", JSON.stringify(updated));
    onFormChangeRef.current?.(updated);
  };

  const handleChange = (e) => {
    if (isStaticFromCart) return;
    const { name, value } = e.target;
    const updated = { ...form, [name]: value };
    setForm(updated);
    if (touched[name]) {
      setErrors((prev) => ({ ...prev, [name]: validateField(name, value) }));
    }
    persist(updated);
  };

  const handleBlur = (e) => {
    if (isStaticFromCart) return;
    const { name, value } = e.target;
    setTouched((prev) => ({ ...prev, [name]: true }));
    setErrors((prev) => ({ ...prev, [name]: validateField(name, value) }));
  };

  const handleCountryChange = (event) => {
    if (isStaticFromCart) return;
    const selectedCountryCode = event.target.value;
    if (selectedCountryCode === MANUAL_COUNTRY_VALUE) {
      countryManuallyOverriddenRef.current = true;
      setIsManualCountryCode(true);
      setSelectedCountry(null);
      setDialMinLength(8);
      setDialMaxLength(15);
      const updated = {
        ...form,
        countryCode: form.countryCode?.startsWith("+")
          ? form.countryCode
          : "+",
      };
      setForm(updated);
      persist(updated);
      return;
    }
    const country = countries.find((c) => c.code === selectedCountryCode);
    if (!country) return;
    countryManuallyOverriddenRef.current = false;
    setIsManualCountryCode(false);
    setSelectedCountry(country);
    setDialMinLength(country.contact_min_length || 8);
    setDialMaxLength(country.contact_max_length || 15);
    const updated = { ...form, countryCode: country.country_code };
    setForm(updated);
    persist(updated);
    if (touched.phone && form.phone) {
      setErrors((prev) => ({
        ...prev,
        phone: validateField("phone", form.phone),
      }));
    }
  };

  const handleManualCountryCodeChange = (e) => {
    if (isStaticFromCart) return;
    const value = sanitizeDialCode(e.target.value);
    countryManuallyOverriddenRef.current = true;
    setIsManualCountryCode(true);
    const updated = { ...form, countryCode: value };
    setForm(updated);
    persist(updated);
  };

  useImperativeHandle(ref, () => ({
    getFormData: () => form,
    isFormValid: () => {
      if (isStaticFromCart && (form.fullName?.trim() || form.email?.trim())) {
        return true;
      }
      const requiredFields = ["fullName", "email", "phone", "address1"];
      const newErrors = {};
      let isValid = true;
      requiredFields.forEach((field) => {
        const error = validateField(field, form[field]);
        if (error) {
          newErrors[field] = error;
          isValid = false;
        }
      });
      setErrors(newErrors);
      setTouched(
        requiredFields.reduce((acc, field) => ({ ...acc, [field]: true }), {})
      );
      return isValid;
    },
  }));

  const lockedFieldSx = isStaticFromCart
    ? {
        ...fieldSx,
        "& .MuiOutlinedInput-root": {
          ...fieldSx["& .MuiOutlinedInput-root"],
          bgcolor: "#f8fafc",
        },
      }
    : fieldSx;

  return (
    <Box
      sx={{
        p: { xs: 2, md: 2.25 },
        borderRadius: 2.5,
        border: "1px solid #e8ecf4",
        bgcolor: "#fff",
      }}
    >
      <Stack
        direction="row"
        alignItems="center"
        justifyContent="space-between"
        spacing={1}
        sx={{ mb: 1.5 }}
      >
        <Typography
          variant="subtitle1"
          fontWeight={800}
          color="#0f172a"
          sx={{ lineHeight: 1.2 }}
        >
          Customer details
        </Typography>
        {isStaticFromCart && (
          <Chip
            size="small"
            label="From cart · read-only"
            sx={{
              height: 24,
              fontSize: "0.7rem",
              fontWeight: 600,
              bgcolor: "#eef2ff",
              color: "#3554d1",
            }}
          />
        )}
      </Stack>

      <FormSection title="Contact">
        <TextField
          fullWidth
          size="small"
          label="Full name"
          name="fullName"
          value={form.fullName}
          onChange={handleChange}
          onBlur={handleBlur}
          error={touched.fullName && Boolean(errors.fullName)}
          helperText={touched.fullName && errors.fullName}
          required
          disabled={isStaticFromCart}
          InputProps={{ readOnly: isStaticFromCart }}
          sx={lockedFieldSx}
        />
        <Stack direction={{ xs: "column", sm: "row" }} spacing={1.25}>
          <TextField
            fullWidth
            size="small"
            label="Email"
            name="email"
            type="email"
            value={form.email}
            onChange={handleChange}
            onBlur={handleBlur}
            error={touched.email && Boolean(errors.email)}
            helperText={touched.email && errors.email}
            required
            disabled={isStaticFromCart}
            InputProps={{ readOnly: isStaticFromCart }}
            sx={lockedFieldSx}
          />
          <TextField
            fullWidth
            size="small"
            label="Phone"
            name="phone"
            value={form.phone}
            onChange={handleChange}
            onBlur={handleBlur}
            error={touched.phone && Boolean(errors.phone)}
            helperText={touched.phone && errors.phone}
            required
            disabled={isStaticFromCart}
            InputProps={{
              readOnly: isStaticFromCart,
              startAdornment: (
                <InputAdornment position="start">
                  <Box
                    sx={{
                      display: "flex",
                      alignItems: "center",
                      gap: 0.25,
                      mr: 0.25,
                    }}
                  >
                    <FormControl variant="standard" sx={{ minWidth: 64 }}>
                      <Select
                        value={
                          isManualCountryCode
                            ? MANUAL_COUNTRY_VALUE
                            : selectedCountry?.code || ""
                        }
                        onChange={handleCountryChange}
                        disableUnderline
                        disabled={countriesLoading || isStaticFromCart}
                        displayEmpty
                        sx={{ fontSize: "0.8rem" }}
                      >
                        {countries.map((c) => (
                          <MenuItem key={c.code} value={c.code}>
                            {c.country_code} ({c.code})
                          </MenuItem>
                        ))}
                        <MenuItem value={MANUAL_COUNTRY_VALUE}>Other</MenuItem>
                      </Select>
                    </FormControl>
                    {isManualCountryCode && (
                      <TextField
                        value={form.countryCode}
                        onChange={handleManualCountryCodeChange}
                        variant="standard"
                        placeholder="+XX"
                        disabled={isStaticFromCart}
                        sx={{ width: 48, "& input": { fontSize: "0.8rem" } }}
                        InputProps={{
                          disableUnderline: true,
                          readOnly: isStaticFromCart,
                        }}
                      />
                    )}
                  </Box>
                </InputAdornment>
              ),
            }}
            sx={lockedFieldSx}
          />
        </Stack>
      </FormSection>

      <Divider sx={{ my: 1.5 }} />

      <FormSection title="Address">
        <TextField
          fullWidth
          size="small"
          label="Address line 1"
          name="address1"
          value={form.address1}
          onChange={handleChange}
          onBlur={handleBlur}
          error={touched.address1 && Boolean(errors.address1)}
          helperText={touched.address1 && errors.address1}
          required
          disabled={isStaticFromCart}
          InputProps={{ readOnly: isStaticFromCart }}
          sx={lockedFieldSx}
        />
        <TextField
          fullWidth
          size="small"
          label="Address line 2 (optional)"
          name="address2"
          value={form.address2}
          onChange={handleChange}
          onBlur={handleBlur}
          disabled={isStaticFromCart}
          InputProps={{ readOnly: isStaticFromCart }}
          sx={lockedFieldSx}
        />
        <Stack direction={{ xs: "column", sm: "row" }} spacing={1.25}>
          <TextField
            fullWidth
            size="small"
            label="State / region"
            name="state"
            value={form.state}
            onChange={handleChange}
            onBlur={handleBlur}
            disabled={isStaticFromCart}
            InputProps={{ readOnly: isStaticFromCart }}
            sx={lockedFieldSx}
          />
          <TextField
            fullWidth
            size="small"
            label="ZIP / postal code"
            name="zip"
            value={form.zip}
            onChange={handleChange}
            onBlur={handleBlur}
            disabled={isStaticFromCart}
            InputProps={{ readOnly: isStaticFromCart }}
            sx={lockedFieldSx}
          />
        </Stack>
      </FormSection>

      <Divider sx={{ my: 1.5 }} />

      <FormSection title="Notes">
        <TextField
          fullWidth
          size="small"
          label="Special requests (optional)"
          name="specialRequests"
          value={form.specialRequests}
          onChange={handleChange}
          onBlur={handleBlur}
          multiline
          minRows={2}
          disabled={isStaticFromCart}
          InputProps={{ readOnly: isStaticFromCart }}
          sx={lockedFieldSx}
        />
      </FormSection>
    </Box>
  );
});

export default CartCustomerInfo;
