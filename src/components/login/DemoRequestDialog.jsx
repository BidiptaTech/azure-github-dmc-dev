import { Dialog, DialogContent, IconButton, Box } from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import ProjectDemo from "@/components/home/ProjectDemo";

const DemoRequestDialog = ({ open, onClose }) => (
  <Dialog
    open={open}
    onClose={onClose}
    fullWidth
    maxWidth="lg"
    scroll="paper"
    PaperProps={{
      sx: {
        borderRadius: 3,
        overflow: "hidden",
        maxHeight: "92vh",
      },
    }}
  >
    <Box
      sx={{
        position: "sticky",
        top: 0,
        zIndex: 2,
        display: "flex",
        justifyContent: "flex-end",
        px: 1,
        pt: 1,
        bgcolor: "transparent",
      }}
    >
      <IconButton
        aria-label="Close demo request"
        onClick={onClose}
        size="small"
        sx={{
          bgcolor: "rgba(15,23,42,0.06)",
          "&:hover": { bgcolor: "rgba(15,23,42,0.12)" },
        }}
      >
        <CloseIcon fontSize="small" />
      </IconButton>
    </Box>
    <DialogContent sx={{ p: { xs: 1, sm: 2 }, pt: 0 }}>
      <ProjectDemo embedded />
    </DialogContent>
  </Dialog>
);

export default DemoRequestDialog;
