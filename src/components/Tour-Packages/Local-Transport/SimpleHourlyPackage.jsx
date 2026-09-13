import React, { useState, useEffect, useRef } from 'react';
import TimerIcon from '@mui/icons-material/Timer';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import { useDispatch, useSelector } from 'react-redux';
import { setHourCount } from "../../../slice/localtour/Localslice";
import { 
  Box, 
  Card, 
  CardContent, 
  Typography, 
  Paper, 
  Button, 
  Divider, 
  List, 
  ListItem, 
  ListItemText, 
  ListItemButton,
  alpha,
  styled,
  Popover,
  Grid
} from '@mui/material';

// Styled components
const StyledCard = styled(Card)(({ theme }) => ({
  '&:hover': {
    boxShadow: theme.shadows[4],
    transform: 'translateY(-2px)',
    transition: 'all 0.3s ease'
  },
  transition: 'all 0.3s ease'
}));

const StyledListItemButton = styled(ListItemButton)(({ theme, selected }) => ({
  borderRadius: theme.shape.borderRadius,
  backgroundColor: selected ? alpha(theme.palette.primary.main, 0.1) : 'transparent',
  '&:hover': {
    backgroundColor: selected ? alpha(theme.palette.primary.main, 0.15) : alpha(theme.palette.primary.main, 0.05)
  }
}));

const SimpleHourlyPackage = ({ 
  hour,
  sethours,
  onHourChange,
  availableHours = [1, 2, 4, 6, 8, 12]
}) => {
  const dispatch = useDispatch();
  const reduxHour = useSelector(state => state.localtour.hourCount) || 4;
  
  // Use refs to track if we've initialized from props or Redux
  const initializedRef = useRef(false);
  const prevHourRef = useRef(hour);
  
  // Initialize state
  const [selectedHour, setSelectedHour] = useState(hour !== undefined ? hour : reduxHour);
  const [anchorEl, setAnchorEl] = useState(null);

  // One-time initialization to Redux when component mounts
  useEffect(() => {
    if (!initializedRef.current) {
      // Only dispatch if values differ from what's already in Redux
      if (selectedHour !== reduxHour) {
        dispatch(setHourCount(selectedHour));
      }
      initializedRef.current = true;
    }
  }, [selectedHour, dispatch, reduxHour]);

  // Update the parent component when the hour changes
  useEffect(() => {
    if (initializedRef.current && prevHourRef.current !== selectedHour) {
      if (sethours) sethours(selectedHour);
      if (onHourChange) onHourChange(selectedHour);
      dispatch(setHourCount(selectedHour));
      prevHourRef.current = selectedHour;
    }
  }, [selectedHour, sethours, onHourChange, dispatch]);
  
  const handleClick = (event) => {
    setAnchorEl(event.currentTarget);
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  const handleHourSelect = (hours) => {
    setSelectedHour(hours);
    handleClose();
  };

  const open = Boolean(anchorEl);
  const id = open ? 'simple-hours-popover' : undefined;

  return (
    <Grid item xs={12} sm={6} md={3}>
      <StyledCard 
        variant="outlined" 
        onClick={handleClick}
        sx={{ 
          cursor: 'pointer',
          border: '1px solid',
          borderColor: 'divider'
        }}
      >
        <CardContent sx={{ p: 2, '&:last-child': { pb: 2 } }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            <TimerIcon sx={{ color: 'primary.main' }} />
            <Typography>
              {selectedHour} Hours
            </Typography>
          </Box>
        </CardContent>
      </StyledCard>

      <Popover
        id={id}
        open={open}
        anchorEl={anchorEl}
        onClose={handleClose}
        anchorOrigin={{
          vertical: 'bottom',
          horizontal: 'left',
        }}
        transformOrigin={{
          vertical: 'top',
          horizontal: 'left',
        }}
        PaperProps={{
          sx: {
            width: '350px',
            mt: 1,
            overflow: 'visible',
            '&:before': {
              content: '""',
              display: 'block',
              position: 'absolute',
              top: 0,
              left: 32,
              width: 10,
              height: 10,
              bgcolor: 'background.paper',
              transform: 'translateY(-50%) rotate(45deg)',
              zIndex: 0,
            },
          }
        }}
      >
        <Paper sx={{ p: 3 }}>
          <Box sx={{ display: 'flex', alignItems: 'center', mb: 3 }}>
            <AccessTimeIcon sx={{ mr: 1, color: 'primary.main' }} />
            <Typography variant="h6" sx={{ fontWeight: 600 }}>
              Select rental hours
            </Typography>
          </Box>

          <List sx={{ py: 0 }}>
            {availableHours.map((hours) => (
              <ListItem key={hours} disablePadding sx={{ my: 0.5 }}>
                <StyledListItemButton
                  selected={selectedHour === hours}
                  onClick={() => handleHourSelect(hours)}
                  sx={{ px: 2, py: 1.5 }}
                >
                  <ListItemText 
                    primary={
                      <Typography variant="subtitle1" sx={{ fontWeight: selectedHour === hours ? 600 : 400 }}>
                        {hours} Hours
                      </Typography>
                    } 
                  />
                </StyledListItemButton>
              </ListItem>
            ))}
          </List>

          <Divider sx={{ my: 2 }} />

          <Button 
            variant="contained" 
            fullWidth 
            onClick={handleClose}
            sx={{ 
              mt: 2,
              py: 1.5,
              textTransform: 'none',
              fontSize: '1rem'
            }}
          >
            Done
          </Button>
        </Paper>
      </Popover>
    </Grid>
  );
};

export default SimpleHourlyPackage; 