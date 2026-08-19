import React from 'react';
import { Box, Typography, Card, CardContent, Avatar, Button, Grid } from '@mui/material';
import {
  AnimatedBox,
  BubbleBackground,
  GradientAnimatedBox,
  StaggeredContainer,
  AnimatedGrid,
  DashboardEntrance,
  PulseBox,
  FloatingBox,
  ShimmerBox,
  GlassMorphismBox,
  BubbleFloatingBox
} from './DashboardAnimations';

const DashboardAnimationDemo = () => {
  return (
    <DashboardEntrance>
      <Box sx={{ p: 4, position: 'relative', zIndex: 2 }}>
        <Typography 
          variant="h2" 
          sx={{ 
            textAlign: 'center', 
            mb: 6, 
            color: 'white',
            fontWeight: 'bold',
            textShadow: '2px 2px 4px rgba(0,0,0,0.3)'
          }}
        >
          Enhanced Dashboard Animations
        </Typography>

        {/* Row 1: Basic Animated Boxes */}
        <StaggeredContainer sx={{ mb: 6 }}>
          <Typography variant="h4" sx={{ color: 'white', mb: 3, textAlign: 'center' }}>
            Glass Morphism Cards
          </Typography>
          <Grid container spacing={3}>
            <Grid item xs={12} md={4}>
              <AnimatedBox direction="up" delay={0}>
                <GlassMorphismBox sx={{ p: 3, textAlign: 'center' }}>
                  <Avatar sx={{ mx: 'auto', mb: 2, bgcolor: '#ff6b6b' }}>
                    👤
                  </Avatar>
                  <Typography variant="h6" sx={{ color: 'white', mb: 1 }}>
                    User Profile
                  </Typography>
                  <Typography variant="body2" sx={{ color: 'rgba(255,255,255,0.8)' }}>
                    Manage your account settings and preferences
                  </Typography>
                </GlassMorphismBox>
              </AnimatedBox>
            </Grid>
            <Grid item xs={12} md={4}>
              <AnimatedBox direction="up" delay={200}>
                <GlassMorphismBox sx={{ p: 3, textAlign: 'center' }}>
                  <Avatar sx={{ mx: 'auto', mb: 2, bgcolor: '#4ecdc4' }}>
                    📊
                  </Avatar>
                  <Typography variant="h6" sx={{ color: 'white', mb: 1 }}>
                    Analytics
                  </Typography>
                  <Typography variant="body2" sx={{ color: 'rgba(255,255,255,0.8)' }}>
                    View detailed insights and performance metrics
                  </Typography>
                </GlassMorphismBox>
              </AnimatedBox>
            </Grid>
            <Grid item xs={12} md={4}>
              <AnimatedBox direction="up" delay={400}>
                <GlassMorphismBox sx={{ p: 3, textAlign: 'center' }}>
                  <Avatar sx={{ mx: 'auto', mb: 2, bgcolor: '#45b7d1' }}>
                    ⚙️
                  </Avatar>
                  <Typography variant="h6" sx={{ color: 'white', mb: 1 }}>
                    Settings
                  </Typography>
                  <Typography variant="body2" sx={{ color: 'rgba(255,255,255,0.8)' }}>
                    Customize your dashboard experience
                  </Typography>
                </GlassMorphismBox>
              </AnimatedBox>
            </Grid>
          </Grid>
        </StaggeredContainer>

        {/* Row 2: Gradient Animated Boxes */}
        <Box sx={{ mb: 6 }}>
          <Typography variant="h4" sx={{ color: 'white', mb: 3, textAlign: 'center' }}>
            Gradient Cards
          </Typography>
          <Grid container spacing={3}>
            <Grid item xs={12} md={6}>
              <GradientAnimatedBox 
                direction="left" 
                delay={0}
                gradient="linear-gradient(135deg, #ff6b6b 0%, #ff8787 100%)"
              >
                <Typography variant="h5" sx={{ color: 'white', mb: 2 }}>
                  Revenue Growth
                </Typography>
                <Typography variant="h3" sx={{ color: 'white', fontWeight: 'bold', mb: 1 }}>
                  $24,567
                </Typography>
                <Typography variant="body2" sx={{ color: 'rgba(255,255,255,0.9)' }}>
                  +12% from last month
                </Typography>
              </GradientAnimatedBox>
            </Grid>
            <Grid item xs={12} md={6}>
              <GradientAnimatedBox 
                direction="right" 
                delay={200}
                gradient="linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%)"
              >
                <Typography variant="h5" sx={{ color: 'white', mb: 2 }}>
                  Active Users
                </Typography>
                <Typography variant="h3" sx={{ color: 'white', fontWeight: 'bold', mb: 1 }}>
                  1,234
                </Typography>
                <Typography variant="body2" sx={{ color: 'rgba(255,255,255,0.9)' }}>
                  +8% from last week
                </Typography>
              </GradientAnimatedBox>
            </Grid>
          </Grid>
        </Box>

        {/* Row 3: Floating and Bubble Elements */}
        <Box sx={{ mb: 6 }}>
          <Typography variant="h4" sx={{ color: 'white', mb: 3, textAlign: 'center' }}>
            Floating Elements
          </Typography>
          <Grid container spacing={3}>
            <Grid item xs={12} md={4}>
              <FloatingBox sx={{ p: 3, textAlign: 'center' }}>
                <Typography variant="h6" sx={{ mb: 2 }}>
                  Floating Card
                </Typography>
                <Typography variant="body2" sx={{ color: 'rgba(0,0,0,0.7)' }}>
                  This card has a gentle floating animation
                </Typography>
              </FloatingBox>
            </Grid>
            <Grid item xs={12} md={4}>
              <Box sx={{ display: 'flex', justifyContent: 'center' }}>
                <BubbleFloatingBox sx={{ width: 100, height: 100 }}>
                  <Typography variant="h4">💎</Typography>
                </BubbleFloatingBox>
              </Box>
            </Grid>
            <Grid item xs={12} md={4}>
              <PulseBox color="#ff6b6b">
                <Card sx={{ p: 3, textAlign: 'center' }}>
                  <Typography variant="h6" sx={{ mb: 2 }}>
                    Pulsing Alert
                  </Typography>
                  <Typography variant="body2" sx={{ color: 'rgba(0,0,0,0.7)' }}>
                    Important notifications appear here
                  </Typography>
                </Card>
              </PulseBox>
            </Grid>
          </Grid>
        </Box>

        {/* Row 4: Loading States */}
        <Box sx={{ mb: 6 }}>
          <Typography variant="h4" sx={{ color: 'white', mb: 3, textAlign: 'center' }}>
            Loading States
          </Typography>
          <Grid container spacing={3}>
            <Grid item xs={12} md={6}>
              <GlassMorphismBox sx={{ p: 3 }}>
                <Typography variant="h6" sx={{ color: 'white', mb: 2 }}>
                  Loading Content
                </Typography>
                <ShimmerBox sx={{ height: 20, mb: 1 }} />
                <ShimmerBox sx={{ height: 20, mb: 1, width: '80%' }} />
                <ShimmerBox sx={{ height: 20, width: '60%' }} />
              </GlassMorphismBox>
            </Grid>
            <Grid item xs={12} md={6}>
              <GlassMorphismBox sx={{ p: 3 }}>
                <Typography variant="h6" sx={{ color: 'white', mb: 2 }}>
                  Quick Actions
                </Typography>
                <Box sx={{ display: 'flex', gap: 2, flexWrap: 'wrap' }}>
                  <Button 
                    variant="contained" 
                    sx={{ 
                      bgcolor: 'rgba(255,255,255,0.2)',
                      '&:hover': { bgcolor: 'rgba(255,255,255,0.3)' }
                    }}
                  >
                    Action 1
                  </Button>
                  <Button 
                    variant="contained" 
                    sx={{ 
                      bgcolor: 'rgba(255,255,255,0.2)',
                      '&:hover': { bgcolor: 'rgba(255,255,255,0.3)' }
                    }}
                  >
                    Action 2
                  </Button>
                  <Button 
                    variant="contained" 
                    sx={{ 
                      bgcolor: 'rgba(255,255,255,0.2)',
                      '&:hover': { bgcolor: 'rgba(255,255,255,0.3)' }
                    }}
                  >
                    Action 3
                  </Button>
                </Box>
              </GlassMorphismBox>
            </Grid>
          </Grid>
        </Box>

        {/* Row 5: Animated Grid */}
        <Box sx={{ mb: 6 }}>
          <Typography variant="h4" sx={{ color: 'white', mb: 3, textAlign: 'center' }}>
            Animated Grid
          </Typography>
          <AnimatedGrid columns={4} staggerDelay={0.1}>
            {[1, 2, 3, 4, 5, 6, 7, 8].map((item) => (
              <AnimatedBox key={item} direction="up">
                <GlassMorphismBox sx={{ p: 2, textAlign: 'center', minHeight: 100 }}>
                  <Typography variant="h6" sx={{ color: 'white', mb: 1 }}>
                    Item {item}
                  </Typography>
                  <Typography variant="body2" sx={{ color: 'rgba(255,255,255,0.8)' }}>
                    Grid item with staggered animation
                  </Typography>
                </GlassMorphismBox>
              </AnimatedBox>
            ))}
          </AnimatedGrid>
        </Box>

        {/* Footer */}
        <Box sx={{ textAlign: 'center', mt: 8 }}>
          <Typography variant="h6" sx={{ color: 'white', mb: 2 }}>
            🎉 Beautiful Dashboard Animations Ready!
          </Typography>
          <Typography variant="body2" sx={{ color: 'rgba(255,255,255,0.8)' }}>
            All components feature glass morphism, gradients, and smooth bubble animations
          </Typography>
        </Box>
      </Box>
    </DashboardEntrance>
  );
};

export default DashboardAnimationDemo; 