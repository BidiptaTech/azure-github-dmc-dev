import React from 'react';
import {
  Box,
  Container,
  Typography,
  Grid,
  Card,
  CardContent,
  CardMedia,
  Chip,
  Button,
  Stack,
  Avatar
} from '@mui/material';
import {
  Person,
  Category,
  ArrowForward
} from '@mui/icons-material';

const BlogNewsSection = () => {
  const blogPosts = [
    {
      id: 1,
      title: "The Surfing Man Will Blow Your Mind",
      image: "/Images/landingpage/Blogs/1.jpg",
      date: {
        day: "17",
        month: "Mar"
      },  
      author: "Admin",
      category: "Travel",
      excerpt: "Discover the ultimate surfing experience with breathtaking waves and stunning scenery.",
      slug: "surfing-man-will-blow-your-mind"
    },
    {
      id: 2,
      title: "The Surfing Man Will Blow Your Mind",
      image: "/Images/landingpage/Blogs/2.jpg",
      date: {
        day: "15",
        month: "Dec"
      },
      author: "Admin",
      category: "Travel",
      excerpt: "Adventure awaits as we explore the most exciting travel destinations around the world.",
      slug: "surfing-man-will-blow-your-mind-2"
    },
    {
      id: 3,
      title: "The Surfing Man Will Blow Your Mind",
      image: "/Images/landingpage/Blogs/3.jpg",
      date: {
        day: "20",
        month: "Feb"
      },
      author: "Admin",
      category: "Travel",
      excerpt: "Experience the thrill of adventure travel and create memories that will last a lifetime.",
      slug: "surfing-man-will-blow-your-mind-3"
    },
    {
      id: 4,
      title: "The Surfing Man Will Blow Your Mind",
      image: "/Images/landingpage/Blogs/4.jpg",
      date: {
        day: "27",
        month: "Feb"
      },
      author: "Admin",
      category: "Travel",
      excerpt: "Join us on an incredible journey through some of the most beautiful places on Earth.",
      slug: "surfing-man-will-blow-your-mind-4"
    }
  ];

  return (
    <Box sx={{ py: 10, bgcolor: '#ffffff', position: 'relative' }}>
 

      {/* Decorative Element */}
      <Box
        sx={{
          position: 'absolute',
          bottom: '20%',
          left: '5%',
          width: 50,
          height: 50,
          borderRadius: '50%',
          bgcolor: '#4CAF50',
          opacity: 0.3,
          zIndex: 1
        }}
      />

      <Container maxWidth="xl">
        {/* Header */}
        <Box sx={{ textAlign: 'center', mb: 8 }}>
          <Typography
            variant="h2"
            sx={{
              fontSize: { xs: '2.5rem', md: '3rem', lg: '3.5rem' },
              fontWeight: 'bold',
              color: '#333',
              lineHeight: 1.2
            }}
          >
            Explore Blogs <span style={{ color: '#4CAF50' }}>And News</span>
          </Typography>
        </Box>

        {/* Blog Grid */}
        <Grid container spacing={4}>
          {blogPosts.map((post) => (
            <Grid item xs={12} sm={6} lg={3} key={post.id}>
              <Card
                sx={{
                  height: '100%',
                  borderRadius: '15px',
                  overflow: 'hidden',
                  boxShadow: '0 5px 20px rgba(0,0,0,0.08)',
                  cursor: 'pointer',
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    transform: 'translateY(-5px)',
                    boxShadow: '0 15px 35px rgba(0,0,0,0.15)'
                  }
                }}
              >
                {/* Card Image */}
                <Box sx={{ position: 'relative' }}>
                  <CardMedia
                    component="img"
                    height="220"
                    image={post.image}
                    alt={post.title}
                    sx={{ objectFit: 'cover' }}
                  />
                  
                  {/* Date Badge */}
                  <Box
                    sx={{
                      position: 'absolute',
                      top: 20,
                      left: 20,
                      bgcolor: '#4CAF50',
                      borderRadius: '8px',
                      p: 1.5,
                      textAlign: 'center',
                      minWidth: 60
                    }}
                  >
                    <Typography
                      variant="h6"
                      sx={{
                        color: 'white',
                        fontWeight: 'bold',
                        fontSize: '18px',
                        lineHeight: 1,
                        mb: 0.5
                      }}
                    >
                      {post.date.day}
                    </Typography>
                    <Typography
                      variant="body2"
                      sx={{
                        color: 'white',
                        fontSize: '12px',
                        lineHeight: 1
                      }}
                    >
                      {post.date.month}
                    </Typography>
                  </Box>
                </Box>

                {/* Card Content */}
                <CardContent sx={{ p: 3 }}>
                  {/* Author and Category */}
                  <Stack direction="row" spacing={3} sx={{ mb: 2 }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                      <Person sx={{ fontSize: 16, color: '#666' }} />
                      <Typography variant="body2" sx={{ color: '#666', fontSize: '14px' }}>
                        By {post.author}
                      </Typography>
                    </Box>
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                      <Category sx={{ fontSize: 16, color: '#4CAF50' }} />
                      <Typography variant="body2" sx={{ color: '#4CAF50', fontSize: '14px' }}>
                        {post.category}
                      </Typography>
                    </Box>
                  </Stack>

                  {/* Title */}
                  <Typography
                    variant="h6"
                    sx={{
                      fontWeight: 'bold',
                      color: '#333',
                      fontSize: '18px',
                      lineHeight: 1.4,
                      mb: 2,
                      display: '-webkit-box',
                      WebkitLineClamp: 2,
                      WebkitBoxOrient: 'vertical',
                      overflow: 'hidden'
                    }}
                  >
                    {post.title}
                  </Typography>

                  {/* Read More Button */}
                  <Button
                    endIcon={<ArrowForward sx={{ fontSize: 16 }} />}
                    sx={{
                      color: '#666',
                      textTransform: 'none',
                      fontWeight: 500,
                      fontSize: '14px',
                      p: 0,
                      minHeight: 'auto',
                      '&:hover': {
                        color: '#4CAF50',
                        bgcolor: 'transparent'
                      }
                    }}
                  >
                    Read More
                  </Button>
                </CardContent>
              </Card>
            </Grid>
          ))}
        </Grid>

        {/* View All Button */}
        <Box sx={{ textAlign: 'center', mt: 6 }}>
          <Button
            variant="contained"
            sx={{
              bgcolor: '#4CAF50',
              color: 'white',
              borderRadius: '25px',
              px: 4,
              py: 1.5,
              fontWeight: 600,
              fontSize: '16px',
              textTransform: 'none',
              '&:hover': {
                bgcolor: '#388E3C'
              }
            }}
          >
            View All Articles
          </Button>
        </Box>
      </Container>
    </Box>
  );
};

export default BlogNewsSection; 