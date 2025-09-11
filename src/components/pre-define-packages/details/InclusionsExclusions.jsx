import React from 'react';
import {  Typography, Grid, List, ListItem, ListItemIcon, ListItemText, Card, CardContent } from '@mui/material';
import CheckCircleOutlineIcon from '@mui/icons-material/CheckCircleOutline';
import RemoveCircleOutlineIcon from '@mui/icons-material/RemoveCircleOutline';

const InclusionsExclusions = ({ packageData }) => {
  // Function to parse and format inclusions/exclusions content
  const formatContent = (content) => {
    if (!content) return [];
    
    // If content is already an array, return it
    if (Array.isArray(content)) return content;
    
    // Otherwise, try to split by newlines or periods
    if (content.includes('\n')) {
      return content.split('\n').filter(item => item.trim() !== '');
    }
    
    if (content.includes('.')) {
      return content.split('.').filter(item => item.trim() !== '');
    }
    
    // If no clear delimiter, return as a single item
    return [content];
  };
  
  const inclusionsList = formatContent(packageData.inclusions);
  const exclusionsList = formatContent(packageData.exclusions);
  
  return (
    <Grid container spacing={2}>
      <Grid item xs={12} md={6}>
        <Card elevation={0} sx={{ height: '100%', border: '1px solid', borderColor: 'divider', borderRadius: '4px' }}>
          <CardContent sx={{ p: 1.5, pb: 1.5 }}>
            <Typography variant="subtitle2" fontWeight="bold" sx={{ mb: 1, color: 'primary.main', display: 'flex', alignItems: 'center' }}>
              <CheckCircleOutlineIcon fontSize="small" sx={{ mr: 0.5, fontSize: 18 }} />
              What's Included
            </Typography>
            
            <List dense disablePadding>
              {inclusionsList.map((item, index) => (
                <ListItem 
                  key={`inclusion-${index}`} 
                  alignItems="flex-start" 
                  sx={{ py: 0.3, px: 0 }}
                >
                  <ListItemIcon sx={{ minWidth: 24 }}>
                    <CheckCircleOutlineIcon color="primary" fontSize="small" />
                  </ListItemIcon>
                  <ListItemText 
                    primary={item.trim()} 
                    primaryTypographyProps={{ 
                      variant: 'body2',
                      sx: { lineHeight: 1.3 }
                    }} 
                  />
                </ListItem>
              ))}
            </List>
          </CardContent>
        </Card>
      </Grid>
      
      <Grid item xs={12} md={6}>
        <Card elevation={0} sx={{ height: '100%', border: '1px solid', borderColor: 'divider', borderRadius: '4px' }}>
          <CardContent sx={{ p: 1.5, pb: 1.5 }}>
            <Typography variant="subtitle2" fontWeight="bold" sx={{ mb: 1, color: 'error.main', display: 'flex', alignItems: 'center' }}>
              <RemoveCircleOutlineIcon fontSize="small" sx={{ mr: 0.5, fontSize: 18 }} />
              What's Not Included
            </Typography>
            
            <List dense disablePadding>
              {exclusionsList.map((item, index) => (
                <ListItem 
                  key={`exclusion-${index}`} 
                  alignItems="flex-start" 
                  sx={{ py: 0.3, px: 0 }}
                >
                  <ListItemIcon sx={{ minWidth: 24 }}>
                    <RemoveCircleOutlineIcon color="error" fontSize="small" />
                  </ListItemIcon>
                  <ListItemText 
                    primary={item.trim()} 
                    primaryTypographyProps={{ 
                      variant: 'body2',
                      sx: { lineHeight: 1.3 }
                    }} 
                  />
                </ListItem>
              ))}
            </List>
          </CardContent>
        </Card>
      </Grid>
    </Grid>
  );
};

export default InclusionsExclusions; 