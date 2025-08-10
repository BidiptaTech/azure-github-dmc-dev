import { Checkbox, FormControl, FormControlLabel, Typography, Box, Avatar } from '@mui/material';
import { useSelector } from 'react-redux';
import { selectSelectedDmcLogo, selectSelectedDmcCompanyName } from "../../../slice/dmc/dmcSlice"; // Import DMC slice selectors

const PriceModeFilter = ({ showDmcOnly, onChange }) => {
  // Get DMC logo and company name from DMC slice instead of auth slice
  const dmcLogo = useSelector(selectSelectedDmcLogo);
  const dmcCompanyName = useSelector(selectSelectedDmcCompanyName) || 'DMC';

  return (
    <FormControl>
      <FormControlLabel
        control={
          <Checkbox
            checked={showDmcOnly}
            onChange={onChange}
            sx={{
              '&.Mui-checked': {
                color: '#3554D1', // Custom color when checked
              },
            }}
          />
        }
        label={
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            {dmcLogo && (
              <Avatar 
                src={dmcLogo} 
                alt={`${dmcCompanyName} Logo`} 
                sx={{ 
                  width: '16px', 
                  height: '16px', 
                  marginRight: '4px'
                }}
              />
            )}
            <Typography variant="body2">
              {`${dmcCompanyName}'s Mode`}
            </Typography>
          </Box>
        }
      />
      <Typography variant="caption" sx={{ ml: 4, color: 'text.secondary' }}>
        {showDmcOnly 
          ? `Only show hotels available in ${dmcCompanyName}'s mode` 
          : `Show all available hotels`}
      </Typography>
    </FormControl>
  );
};

export default PriceModeFilter;
  