import React from 'react';
import { Typography, Box, Avatar, Chip, Stack, Card } from '@mui/material';
import WorkHistoryIcon from '@mui/icons-material/WorkHistory';
import TranslateIcon from '@mui/icons-material/Translate';
import VerifiedIcon from '@mui/icons-material/Verified';
import StarIcon from '@mui/icons-material/Star';

const defaultGuideAvatar = '/img/team/1.png';

const GuideDetails = ({ packageData }) => {
  const guides = packageData.selected_guides || [];
  
  if (!guides.length) {
    return (
      <Typography variant="body2" color="text.secondary">
        No tour guide information available for this package.
      </Typography>
    );
  }
  
  // Only show the first guide
  const guide = guides[0];
  
  return (
    <>
      <Typography variant="body2" sx={{ mb: 1 }}>
        Your professional tour guide for this package:
      </Typography>
      
      <Card 
        elevation={0}
        sx={{ 
          display: 'flex',
          p: 2,
          border: '1px solid',
          borderColor: 'divider',
          borderRadius: '8px',
          height: '100%'
        }}
      >
        <Avatar 
          src={guide.image || defaultGuideAvatar}
          alt={guide.name}
          sx={{ 
            width: 80,
            height: 80,
            mr: 2
          }}
        />
        
        <Box sx={{ flex: 1, minWidth: 0 }}>
          <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
            <Typography variant="subtitle1" fontWeight="bold" sx={{ flex: 1 }}>
              {guide.name}
            </Typography>
            
            {guide.certified && (
              <VerifiedIcon fontSize="small" color="primary" sx={{ ml: 0.5 }} />
            )}
          </Box>
          
          <Stack spacing={0.75}>
            {/* Experience */}
            {guide.experience && (
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <WorkHistoryIcon fontSize="small" sx={{ fontSize: 16, mr: 0.75 }} color="action" />
                <Typography variant="body2" color="text.secondary">
                  {guide.experience} years experience
                </Typography>
              </Box>
            )}
            
            {/* Languages */}
            {guide.language && (
              <Box sx={{ display: 'flex', alignItems: 'flex-start' }}>
                <TranslateIcon fontSize="small" sx={{ fontSize: 16, mr: 0.75, mt: 0.2 }} color="action" />
                <Box sx={{ flex: 1, minWidth: 0 }}>
                  <Typography variant="body2" color="text.secondary">
                    {guide.language}
                  </Typography>
                </Box>
              </Box>
            )}
            
            {/* Rating */}
            {guide.rating && (
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <StarIcon fontSize="small" sx={{ fontSize: 16, mr: 0.75 }} color="warning" />
                <Typography variant="body2" color="text.secondary">
                  {guide.rating} / 5 ({guide.reviews || 0} reviews)
                </Typography>
              </Box>
            )}
            
            {/* Specialization */}
            {guide.specialization && (
              <Box sx={{ mt: 0.5 }}>
                <Chip 
                  label={guide.specialization} 
                  size="small" 
                  color="primary" 
                  variant="outlined"
                  sx={{ height: 24, fontSize: '0.75rem' }}
                />
              </Box>
            )}
            
            {/* Description */}
            {guide.description && (
              <Typography variant="body2" color="text.secondary" sx={{ mt: 1 }}>
                {guide.description}
              </Typography>
            )}
          </Stack>
        </Box>
      </Card>
      
      {guides.length > 1 && (
        <Typography variant="caption" color="text.secondary" sx={{ fontStyle: 'italic', display: 'block', mt: 1 }}>
          * {guides.length - 1} more guide{guides.length > 2 ? 's' : ''} available. Click "Change" to view and select.
        </Typography>
      )}
    </>
  );
};

export default GuideDetails; 