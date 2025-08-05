import React, { useState } from "react";
import {
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Checkbox,
  ListItemText,
  Box,
  Chip,
  OutlinedInput,
  useTheme
} from "@mui/material";
import { styled } from "@mui/material/styles";
import StarIcon from "@mui/icons-material/Star";
import CancelIcon from "@mui/icons-material/Cancel";

// Styled components
const StyledFormControl = styled(FormControl)(({ theme }) => ({
  width: "100%",
}));

const StyledChip = styled(Chip)(({ theme }) => ({
  margin: theme.spacing(0.5),
  backgroundColor: theme.palette.primary.dark,
  color: "white",
  "& .MuiChip-deleteIcon": {
    color: "white",
    "&:hover": {
      color: "white",
    },
  },
    "& .MuiChip-icon ": {
    color: "white",
    "&:hover": {
      color: "white",
    },
  },
}));

const ITEM_HEIGHT = 48;
const ITEM_PADDING_TOP = 8;
const MenuProps = {
  PaperProps: {
    style: {
      maxHeight: ITEM_HEIGHT * 4.5 + ITEM_PADDING_TOP,
      width: 250,
    },
  },
};

const starOptions = ["3", "4", "5"];

const StarCategorySelect = ({ onChange }) => {
  const [selectedStars, setSelectedStars] = useState([]);
  const theme = useTheme();

  const handleChange = (event) => {
    const {
      target: { value },
    } = event;
    
    // On autofill we get a stringified value.
    const newSelectedStars = typeof value === 'string' ? value.split(',') : value;
    setSelectedStars(newSelectedStars);
    
    // Pass the selected stars to the parent component
    if (onChange) {
      onChange(newSelectedStars.join(', '));
    }
  };

  const handleDelete = (starToDelete) => {
    const newSelectedStars = selectedStars.filter(star => star !== starToDelete);
    setSelectedStars(newSelectedStars);
    
    // Pass the updated selected stars to the parent component
    if (onChange) {
      onChange(newSelectedStars.join(', '));
    }
  };

  return (
 <FormControl
  fullWidth
  variant="outlined"
  sx={{
    mt: 2,
    '& .MuiInputLabel-outlined': {
      zIndex: 1,
      backgroundColor: 'white',
      px: 0.5,
    },
    '& .MuiOutlinedInput-root': {
      alignItems: 'start',
      minHeight: '56px',
      pt: 1.5,
    },
  }}
>
  <InputLabel id="star-category-select-label">select Star Categories</InputLabel>
  <Select
    labelId="star-category-select-label"
    id="star-category-select"
    multiple
    value={selectedStars}
    onChange={handleChange}
    input={<OutlinedInput label="Star Categories" />}
    renderValue={(selected) => (
      <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1 }}>
        {selected.map((value) => (
          <Chip
            key={value}
            label={`${value} Star`}
            onDelete={(e) => {
              e.stopPropagation();
              handleDelete(value);
            }}
            deleteIcon={<CancelIcon onMouseDown={(e) => e.stopPropagation()} />}
            icon={<StarIcon fontSize="small" />}
            sx={{
              pl: '4px',
              pr: '6px',
              '& .MuiChip-icon': { color: 'goldenrod' },
            }}
          />
        ))}
      </Box>
    )}
    MenuProps={{
      PaperProps: {
        style: {
          maxHeight: 250,
        },
      },
    }}
  >
    {starOptions.map((star) => (
      <MenuItem key={star} value={star}>
        <Checkbox checked={selectedStars.indexOf(star) > -1} />
        <ListItemText
          primary={`${star} Star`}
          secondary={
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              {[...Array(parseInt(star))].map((_, i) => (
                <StarIcon key={i} fontSize="small" sx={{ color: '#fbc02d' }} />
              ))}
            </Box>
          }
        />
      </MenuItem>
    ))}
  </Select>
</FormControl>

  );
};

export default StarCategorySelect;
