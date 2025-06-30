import { Checkbox, FormControl, FormControlLabel, Typography, Box, Avatar } from '@mui/material';
import { useSelector } from 'react-redux';

const PriceModeFilter = ({ showDmcOnly, onChange }) => {
  const dmcLogo = useSelector((state) => state.auth.DmcLogo);
  const dmcName = useSelector((state) => state.auth.DmcName) || 'DMC';

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
                alt={`${dmcName} Logo`} 
                sx={{ 
                  width: '16px', 
                  height: '16px', 
                  marginRight: '4px'
                }}
              />
            )}
            <Typography variant="body2">
              {`${dmcName}'s Mode`}
            </Typography>
          </Box>
        }
      />
      <Typography variant="caption" sx={{ ml: 4, color: 'text.secondary' }}>
        {showDmcOnly 
          ? `Only show hotels available in ${dmcName}'s mode` 
          : `Show all available hotels`}
      </Typography>
    </FormControl>
  );
};

export default PriceModeFilter;
  