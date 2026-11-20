import {
  Box,
  Checkbox,
  FormControl,
  FormControlLabel,
  FormGroup,
  Paper,
  Typography,
} from "@mui/material";
import StarIcon from "@mui/icons-material/Star";
import { useDispatch, useSelector } from "react-redux";
import { setFilter } from "@/slice/hotel/CategorySlice";

const STAR_OPTIONS = [
  { id: "star3", label: "5 Star", stars: 5 },
  { id: "star2", label: "4 Star", stars: 4 },
  { id: "star1", label: "3 Star", stars: 3 },
];

const StarCategoryFilter = () => {
  const dispatch = useDispatch();
  const filters = useSelector((state) => state.category);

  const handleChange = (event) => {
    const { id, checked } = event.target;
    dispatch(setFilter({ filterName: id, value: checked }));
  };

  return (
    <Box className="pw-star-filter" sx={{ mb: 3 }}>
      <Paper
        elevation={0}
        className="pw-star-filter__paper"
        sx={{ p: 2, bgcolor: "background.paper", borderRadius: 2 }}
      >
        <Box sx={{ display: "flex", alignItems: "center", mb: 1.5 }}>
          <StarIcon sx={{ mr: 1, color: "primary.main", fontSize: 18 }} />
          <Typography variant="subtitle2" fontWeight={600} sx={{ fontSize: 13 }}>
            Star Category
          </Typography>
        </Box>

        <FormControl component="fieldset" fullWidth>
          <FormGroup>
            {STAR_OPTIONS.map((option) => (
              <FormControlLabel
                key={option.id}
                control={
                  <Checkbox
                    id={option.id}
                    size="small"
                    checked={Boolean(filters[option.id])}
                    onChange={handleChange}
                    sx={{
                      "&.Mui-checked": { color: "#3554D1" },
                    }}
                  />
                }
                label={
                  <Box sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
                    <Typography variant="body2" sx={{ fontSize: "0.875rem" }}>
                      {option.label}
                    </Typography>
                    <Box sx={{ display: "flex", color: "#f5a623", ml: 0.25 }}>
                      {Array.from({ length: option.stars }).map((_, index) => (
                        <StarIcon key={index} sx={{ fontSize: 14 }} />
                      ))}
                    </Box>
                  </Box>
                }
              />
            ))}
          </FormGroup>
        </FormControl>

        {(filters.star1 || filters.star2 || filters.star3) && (
          <Box sx={{ mt: 1.5, pt: 1.5, borderTop: "1px solid", borderColor: "divider" }}>
            <Typography variant="caption" color="primary.main" sx={{ fontSize: "0.75rem" }}>
              Showing selected star categories
            </Typography>
          </Box>
        )}
      </Paper>
    </Box>
  );
};

export default StarCategoryFilter;
