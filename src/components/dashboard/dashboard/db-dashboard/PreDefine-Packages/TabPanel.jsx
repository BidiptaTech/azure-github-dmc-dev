import { Box } from "@mui/material";

// Tab Panel component
export function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`package-tabpanel-${index}`}
      aria-labelledby={`package-tab-${index}`}
      {...other}
      style={{ padding: "20px 0" }}
    >
      {value === index && children}
    </div>
  );
}

export function a11yProps(index) {
  return {
    id: `package-tab-${index}`,
    "aria-controls": `package-tabpanel-${index}`,
  };
} 