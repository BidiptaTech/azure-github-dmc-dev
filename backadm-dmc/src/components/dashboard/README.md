# Dashboard Animation System

This dashboard animation system provides smooth, professional entrance animations that make content come from all sides when users log in. The animations are optimized for performance and include accessibility features.

## Features

- **Multi-directional animations**: Content can enter from any direction (up, down, left, right, and diagonal)
- **Staggered animations**: Elements animate in sequence with configurable delays
- **Smooth transitions**: Uses cubic-bezier easing for natural movement
- **Performance optimized**: Uses CSS transforms and opacity for smooth 60fps animations
- **Accessibility friendly**: Respects `prefers-reduced-motion` user preference
- **Responsive**: Animations adapt to mobile devices
- **Hover effects**: Interactive elements have subtle hover animations

## Components

### Core Animation Components

#### `AnimatedBox`
The main animation wrapper component that handles entrance animations.

```jsx
import { AnimatedBox } from '@/components/dashboard/DashboardAnimations';

<AnimatedBox direction="up" delay={200} duration={0.8}>
  <YourContent />
</AnimatedBox>
```

**Props:**
- `direction`: Animation direction (`'up'`, `'down'`, `'left'`, `'right'`, `'up-left'`, `'up-right'`, `'down-left'`, `'down-right'`)
- `delay`: Delay before animation starts (in milliseconds)
- `duration`: Animation duration (in seconds)
- `className`: Additional CSS classes

#### `DashboardEntrance`
Wraps the entire dashboard for a smooth entrance effect.

```jsx
import { DashboardEntrance } from '@/components/dashboard/DashboardAnimations';

<DashboardEntrance>
  <YourDashboardContent />
</DashboardEntrance>
```

#### `StaggeredContainer`
Automatically applies staggered delays to child `AnimatedBox` components.

```jsx
import { StaggeredContainer, AnimatedBox } from '@/components/dashboard/DashboardAnimations';

<StaggeredContainer staggerDelay={0.1}>
  <AnimatedBox direction="left">First item</AnimatedBox>
  <AnimatedBox direction="up">Second item</AnimatedBox>
  <AnimatedBox direction="right">Third item</AnimatedBox>
</StaggeredContainer>
```

#### `AnimatedGrid`
Creates a responsive grid with staggered animations.

```jsx
import { AnimatedGrid, AnimatedBox } from '@/components/dashboard/DashboardAnimations';

<AnimatedGrid columns={3} staggerDelay={0.1}>
  <AnimatedBox direction="up-left">Card 1</AnimatedBox>
  <AnimatedBox direction="up">Card 2</AnimatedBox>
  <AnimatedBox direction="up-right">Card 3</AnimatedBox>
</AnimatedGrid>
```

### Special Effect Components

#### `FloatingBox`
Adds a subtle floating animation to cards.

```jsx
import { FloatingBox } from '@/components/dashboard/DashboardAnimations';

<FloatingBox>
  <YourCard />
</FloatingBox>
```

#### `PulseBox`
Adds a pulsing effect for attention-grabbing elements.

```jsx
import { PulseBox } from '@/components/dashboard/DashboardAnimations';

<PulseBox>
  <ImportantNotification />
</PulseBox>
```

#### `ShimmerBox`
Creates a shimmer loading effect.

```jsx
import { ShimmerBox } from '@/components/dashboard/DashboardAnimations';

<ShimmerBox sx={{ height: 200, width: '100%' }} />
```

## Usage Examples

### Basic Dashboard Layout

```jsx
import { 
  DashboardEntrance, 
  AnimatedBox, 
  StaggeredContainer 
} from '@/components/dashboard/DashboardAnimations';

const Dashboard = () => {
  return (
    <DashboardEntrance>
      <StaggeredContainer>
        {/* Header */}
        <AnimatedBox direction="left" delay={200}>
          <DashboardHeader />
        </AnimatedBox>

        {/* Navigation */}
        <AnimatedBox direction="down" delay={400}>
          <DashboardNavigation />
        </AnimatedBox>

        {/* Content */}
        <AnimatedBox direction="up" delay={600}>
          <DashboardContent />
        </AnimatedBox>
      </StaggeredContainer>
    </DashboardEntrance>
  );
};
```

### Card Grid Layout

```jsx
import { 
  AnimatedGrid, 
  AnimatedBox, 
  FloatingBox 
} from '@/components/dashboard/DashboardAnimations';

const DashboardCards = () => {
  return (
    <AnimatedGrid columns={3} staggerDelay={0.1}>
      <AnimatedBox direction="up-left">
        <FloatingBox>
          <AnalyticsCard />
        </FloatingBox>
      </AnimatedBox>
      
      <AnimatedBox direction="up">
        <FloatingBox>
          <ReportsCard />
        </FloatingBox>
      </AnimatedBox>
      
      <AnimatedBox direction="up-right">
        <FloatingBox>
          <SettingsCard />
        </FloatingBox>
      </AnimatedBox>
    </AnimatedGrid>
  );
};
```

## Animation Directions

The system supports 8 different animation directions:

1. **`up`**: Slides up from bottom
2. **`down`**: Slides down from top
3. **`left`**: Slides in from left
4. **`right`**: Slides in from right
5. **`up-left`**: Slides from bottom-left corner
6. **`up-right`**: Slides from bottom-right corner
7. **`down-left`**: Slides from top-left corner
8. **`down-right`**: Slides from top-right corner

## CSS Classes

The system automatically applies these CSS classes:

- `.dashboard-entrance`: Main entrance wrapper
- `.dashboard-animated-box`: Individual animated elements
- `.dashboard-card`: Styled cards with hover effects
- `.dashboard-tabs`: Animated tab navigation
- `.dashboard-header`: Header animations
- `.dashboard-content`: Content area animations

## Performance Tips

1. **Use `will-change`**: The system automatically applies `will-change: transform, opacity` for optimal performance
2. **Limit concurrent animations**: Use staggered delays to avoid overwhelming the browser
3. **Mobile optimization**: Animations are automatically disabled on mobile for better performance
4. **Reduced motion**: The system respects user preferences for reduced motion

## Accessibility

- **Reduced motion support**: Animations are disabled when `prefers-reduced-motion: reduce` is set
- **Keyboard navigation**: All interactive elements remain keyboard accessible
- **Screen reader friendly**: Animations don't interfere with screen reader functionality

## Browser Support

- Modern browsers with CSS transform support
- Graceful degradation on older browsers
- Mobile-optimized animations

## Customization

You can customize animations by modifying the SCSS variables in `src/styles/index.scss`:

```scss
// Animation timing
$slide-distance: 60px;
$slide-duration: 0.8s;

// Easing functions
$easeOutCubic: cubic-bezier(0.25, 0.46, 0.45, 0.94);
```

## Demo

See `DashboardAnimationDemo.jsx` for a complete example of all animation types in action. 