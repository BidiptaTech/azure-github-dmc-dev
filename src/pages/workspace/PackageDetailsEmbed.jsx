import PackageDetailsContainer from "@/components/pre-define-packages/details";
import { Box } from "@mui/material";

/** Package details without Header11 — partner shell provides nav. */
export default function PackageDetailsEmbed() {
  return (
    <Box sx={{ mt: 1 }}>
      <PackageDetailsContainer />
    </Box>
  );
}
