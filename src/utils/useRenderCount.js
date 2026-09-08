import { useRef, useEffect } from 'react';

/**
 * A custom hook that tracks how many times a component renders
 * @param {string} componentName - Name of the component to track
 * @param {boolean} log - Whether to log the render count to console
 * @param {number} warnThreshold - Number of renders after which to show warning
 * @return {number} The number of renders
 */
export function useRenderCount(componentName, log = true, warnThreshold = 5) {
  const renderCount = useRef(0);
  
  useEffect(() => {
    renderCount.current += 1;
    
    if (log) {
      const count = renderCount.current;
      
      // Apply different styles based on render count
      const style = count > warnThreshold 
        ? 'background: #ffcc00; color: #000; padding: 2px 4px; border-radius: 2px; font-weight: bold;' // Warning (yellow)
        : count > 10 
          ? 'background: #ff4444; color: #fff; padding: 2px 4px; border-radius: 2px; font-weight: bold;' // Danger (red)
          : 'background: #44ff44; color: #000; padding: 2px 4px; border-radius: 2px;'; // Normal (green)
      
      console.log(`%c[Render Count] ${componentName}: ${count}`, style);
    }
  });
  
  return renderCount.current;
}

/**
 * A component that wraps another component and logs render timing
 */
export function RenderProfiler({ id, children }) {
  const renderTime = useRef({
    startTime: 0,
    endTime: 0,
    duration: 0
  });
  
  useEffect(() => {
    renderTime.current.endTime = performance.now();
    renderTime.current.duration = renderTime.current.endTime - renderTime.current.startTime;
    
    // Log render time with different styles based on duration
    const duration = renderTime.current.duration;
    let style = 'background: #44ff44; color: #000; padding: 2px 4px; border-radius: 2px;'; // Fast (green)
    
    if (duration > 15) {
      style = 'background: #ffcc00; color: #000; padding: 2px 4px; border-radius: 2px; font-weight: bold;'; // Slow (yellow)
    }
    
    if (duration > 50) {
      style = 'background: #ff4444; color: #fff; padding: 2px 4px; border-radius: 2px; font-weight: bold;'; // Very slow (red)
    }
    
    console.log(`%c[Render Time] ${id}: ${duration.toFixed(2)}ms`, style);
    
    return () => {
      // Cleanup if needed
    };
  });
  
  // Set start time before render
  renderTime.current.startTime = performance.now();
  
  return children;
} 