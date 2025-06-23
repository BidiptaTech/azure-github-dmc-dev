import React from 'react';
import { CardMedia, CardContent, Typography, Box, Stack, Chip, Rating } from '@mui/material';
import PersonIcon from '@mui/icons-material/Person';
import TranslateIcon from '@mui/icons-material/Translate';
import WorkHistoryIcon from '@mui/icons-material/WorkHistory';
import VerifiedIcon from '@mui/icons-material/Verified';
import StarIcon from '@mui/icons-material/Star';

// Default image for guides when no image is provided
const defaultGuideImage = '/img/team/1.png';

/**
 * Renders a guide item in the selection modal
 * 
 * @param {Object} guide - The guide object to render
 * @returns {JSX.Element} The rendered guide item
 */
const GuideItemRenderer = (guide) => (
  <>
    <Box sx={{ height: '200px', overflow: 'hidden', position: 'relative' }}>
      <CardMedia
        component="img"
        height="200"
        width="100%"
        image={guide.image || defaultGuideImage}
        alt={guide.name || 'Tour Guide'}
        sx={{ 
          objectFit: 'cover',
          objectPosition: 'center',
          height: '200px !important',
          maxHeight: '200px !important'
        }}
      />
    </Box>
    <CardContent sx={{ p: 1.5, pb: '16px !important', flex: 1, minHeight: 0 }}>
      <Typography variant="subtitle2" fontWeight="medium" noWrap>
        {guide.name || 'Unnamed Guide'}
      </Typography>
      
      <Stack spacing={0.5} sx={{ mt: 0.5 }}>
        {guide.language && (
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <TranslateIcon fontSize="small" color="action" sx={{ fontSize: 14, mr: 0.5 }} />
            <Typography variant="caption" color="text.secondary" noWrap>
              {guide.language}
            </Typography>
          </Box>
        )}
        
        {guide.experience && (
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <WorkHistoryIcon fontSize="small" color="action" sx={{ fontSize: 14, mr: 0.5 }} />
            <Typography variant="caption" color="text.secondary">
              {guide.experience} years experience
            </Typography>
          </Box>
        )}
        
        {guide.rating && (
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <StarIcon fontSize="small" color="warning" sx={{ fontSize: 14, mr: 0.5 }} />
            <Typography variant="caption" color="text.secondary">
              {guide.rating} ({guide.reviews || 0})
            </Typography>
          </Box>
        )}
        
        {guide.specialization && (
          <Box sx={{ mt: 0.5 }}>
            <Chip 
              label={guide.specialization} 
              size="small" 
              color="primary" 
              variant="outlined"
              sx={{ height: 20, fontSize: '0.7rem' }}
            />
          </Box>
        )}
        
        {guide.certified && (
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <VerifiedIcon fontSize="small" color="success" sx={{ fontSize: 14, mr: 0.5 }} />
            <Typography variant="caption" color="success.main">
              Certified Guide
            </Typography>
          </Box>
        )}
      </Stack>
    </CardContent>
  </>
);

export default GuideItemRenderer; 