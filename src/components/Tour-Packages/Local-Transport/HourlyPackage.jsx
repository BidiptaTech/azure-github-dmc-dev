import React, { useState, useEffect, useRef } from 'react';
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
import TimerIcon from '@mui/icons-material/Timer';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import { useDispatch, useSelector } from 'react-redux';
import { setHourCount } from "../../../slice/localtour/Localslice";

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

const HourlyPackage = ({ 
  hour, 
  sethours, 
  onHourChange, 
  availableHours = [1, 2, 4, 6, 8, 12] 
}) => {
  const dispatch = useDispatch();
  const reduxHour = useSelector(state => state.localtour.hourCount) || 1;
  
  // Use refs to track if we've initialized from props or Redux
  const initializedRef = useRef(false);
  const prevHourRef = useRef(hour);
  
  // Initialize state with prop value if provided, otherwise use Redux value
  const [selectedHour, setSelectedHour] = useState(hour !== undefined ? hour : reduxHour);
  const [anchorEl, setAnchorEl] = useState(null);

  // One-time initialization to Redux when component mounts
  useEffect(() => {
    if (!initializedRef.current) {
      // Only dispatch if values differ from what's already in Redux
      // and we're not handling state independently with custom handlers
      if (selectedHour !== reduxHour && !onHourChange && !sethours) {
        dispatch(setHourCount(selectedHour));
      }
      initializedRef.current = true;
    }
  }, [selectedHour, dispatch, reduxHour, onHourChange, sethours]);

  // Update the parent component when the hour changes
  useEffect(() => {
    if (initializedRef.current && prevHourRef.current !== selectedHour) {
      // First try the onHourChange callback
      if (onHourChange) {
        onHourChange(selectedHour);
      } 
      // Fall back to the sethours callback
      else if (sethours) {
        sethours(selectedHour);
      } 
      // If no callback props, update Redux
      else {
        dispatch(setHourCount(selectedHour));
      }
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
  const id = open ? 'hours-popover' : undefined;

  return (
    <Grid item xs={12} sm={6} md={12}>
      <StyledCard 
        variant="outlined" 
        onClick={handleClick}
        sx={{ 
          cursor: 'pointer',
          border: '1px solid',
          borderColor: 'divider',
          height: '42px',
          '& .MuiCardContent-root': {
            paddingTop: '7px',
            paddingBottom: '0px'
          }
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

export default HourlyPackage;
