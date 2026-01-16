@extends('layouts.layout')
@section('title', 'Tour Itinerary')

@section('content')
<!-- Include html2pdf and html2canvas libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    :root {
        --primary-color: #2563eb;
        --primary-light: #3b82f6;
        --primary-dark: #1d4ed8;
        --secondary-color: #64748b;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --error-color: #ef4444;
        --background-primary: #ffffff;
        --background-secondary: #f8fafc;
        --background-tertiary: #f1f5f9;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-tertiary: #64748b;
        --border-color: #e2e8f0;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        
        /* Day indicator colors */
        --day-1-color: #ff6b6b;
        --day-2-color: #4ecdc4;
        --day-3-color: #45b7d1;
        --day-4-color: #96ceb4;
        --day-5-color: #feca57;
        --day-6-color: #ff9ff3;
        --day-7-color: #54a0ff;
    }
    
    .itinerary-container {
        position: relative;
        padding: 0;
        background: var(--background-secondary);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        max-width: 1400px;
        margin: 0 auto;
        min-height: 100vh;
        line-height: 1.4;
    }
    
    /* Override Bootstrap margin utilities for compact layout */
    .itinerary-container .mt-1 { margin-top: 0.15rem !important; }
    .itinerary-container .mt-2 { margin-top: 0.25rem !important; }
    .itinerary-container .mb-1 { margin-bottom: 0.15rem !important; }
    .itinerary-container .mb-2 { margin-bottom: 0.25rem !important; }
    .itinerary-container .me-1 { margin-right: 0.15rem !important; }
    .itinerary-container .me-2 { margin-right: 0.25rem !important; }
    
    .itinerary-header {
        background: var(--background-primary);
        padding: 12px 16px;
        margin: 0 16px 12px 16px;
        border-radius: 6px;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        position: relative;
        overflow: hidden;
    }
    
    .itinerary-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--primary-light), var(--success-color));
    }
    
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 24px;
    }
    
    .header-info h4 {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 4px 0;
        letter-spacing: -0.02em;
    }
    
    .header-info h5 {
        font-size: 12px;
        font-weight: 500;
        color: var(--text-secondary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .header-actions {
        display: flex;
        gap: 12px;
        flex-shrink: 0;
    }
    
    .btn-modern {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 500;
        font-size: 13px;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        letter-spacing: 0.025em;
    }
    
    .btn-primary-modern {
        background: var(--primary-color);
        color: white;
        box-shadow: var(--shadow-sm);
    }
    
    .btn-primary-modern:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
        color: white;
        text-decoration: none;
    }
    
    .btn-secondary-modern {
        background: var(--background-primary);
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }
    
    .btn-secondary-modern:hover {
        background: var(--background-tertiary);
        color: var(--text-primary);
        text-decoration: none;
        transform: translateY(-1px);
    }
    
    .timeline-container {
        padding: 0 16px 16px 16px;
        position: relative;
        overflow: visible !important;
    }
    
    .day-indicator {
        display: flex;
        align-items: center;
        margin-bottom: 2px;
        position: relative;
        background: #ffffff;
        padding: 2px 3px;
        border-radius: 0;
        border-bottom: 1px solid #e2e8f0;
        border-left: 1px solid var(--primary-color);
        height: 20px;
    }
    
    .day-circle {
        min-width: 40px;
        height: 20px;
        border-radius: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 12px;
        margin-right: 8px;
        position: relative;
        z-index: 2;
        transition: all 0.2s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        flex-direction: column;
        line-height: 1.2;
    }
    
    .day-circle.day-1 { 
        background: #FF6B6B;
        box-shadow: none;
    }
    .day-circle.day-2 { 
        background: #4ECDC4;
        box-shadow: none;
    }
    .day-circle.day-3 { 
        background: #45B7D1;
        box-shadow: none;
    }
    .day-circle.day-4 { 
        background: #96CEB4;
        box-shadow: none;
    }
    .day-circle.day-5 { 
        background: #FECA57;
        box-shadow: none;
    }
    .day-circle.day-6 { 
        background: #FF9FF3;
        box-shadow: none;
    }
    .day-circle.day-7 { 
        background: #54A0FF;
        box-shadow: none;
    }
    .day-circle:nth-child(8n) { 
        background: #667EEA;
        box-shadow: none;
    }
    
    .day-info {
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .day-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        letter-spacing: -0.01em;
    }
    
    .day-date {
        font-size: 12px;
        color: var(--text-secondary);
        margin: 0;
        font-weight: 500;
    }
    
    .day-chevron {
        font-size: 14px;
        color: var(--text-tertiary);
        margin-left: 8px;
    }
    
        .date-container {
        position: relative;
        margin-bottom: 12px;
        overflow: visible !important;
        width: 100%;
    }
    
    .timeline-line {
        position: absolute;
        left: 43px;
        top: 50px;
        bottom: -8px;
        width: 2px;
        z-index: 0;
        border-radius: 2px;
        background: #e2e8f0;
    }
    
    /* Adjust timeline for two-column layout */
    .services-list::before {
        content: '';
        position: absolute;
        left: -36px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
        z-index: 1;
    }
    
    /* Day-specific timeline colors - simplified solid line */
    .date-container.day-1 .timeline-line {
        background: #FF6B6B;
    }
    
    .date-container.day-2 .timeline-line {
        background: #4ECDC4;
    }
    
    .date-container.day-3 .timeline-line {
        background: #45B7D1;
    }
    
    .date-container.day-4 .timeline-line {
        background: #96CEB4;
    }
    
    .date-container.day-5 .timeline-line {
        background: #FECA57;
    }
    
    .date-container.day-6 .timeline-line {
        background: #FF9FF3;
    }
    
    .date-container.day-7 .timeline-line {
        background: #54A0FF;
    }
    
    .date-container:nth-child(8n) .timeline-line {
        background: #667EEA;
    }
    
    .date-container:last-child .timeline-line {
        display: none;
    }
    
    .services-list {
        margin-left: 66px;
        margin-top: 4px;
        padding-left: 36px;
        padding-right: 16px;
        position: relative;
        overflow: visible !important;
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        grid-auto-flow: row !important; /* Normal flow: left to right, top to bottom */
        gap: 8px 12px !important;
        align-items: start !important;
        max-width: none !important;
        box-sizing: border-box !important;
        width: auto !important;
        min-width: 0 !important;
    }
    
    /* Make drop-zone-indicator not participate in grid layout */
    .services-list > .drop-zone-indicator {
        position: absolute !important;
        grid-column: none !important;
        grid-row: none !important;
        pointer-events: none !important;
        z-index: 1 !important;
    }
    
    /* Force grid items to alternate columns - pattern: drop-zone (child 1,3,5...), service-item (child 2,4,6...) */
    /* So service-items at positions 2,6,10... (4n-2) go to column 1, and 4,8,12... (4n) go to column 2 */
    .services-list > .service-item:nth-child(4n-2) {
        grid-column: 1 !important;
    }
    
    .services-list > .service-item:nth-child(4n) {
        grid-column: 2 !important;
    }
    
    .service-item {
        background: #ffffff;
        border: none;
        border-left: 2px solid #e2e8f0;
        border-radius: 0;
        margin-bottom: 0;
        padding: 0;
        box-shadow: none;
        transition: all 0.2s ease;
        position: relative;
        overflow: visible !important;
        transform: translateX(0);
        min-height: fit-content;
        min-width: 0 !important; /* Allow grid items to shrink below content size */
        display: block !important; /* Ensure it's a block element for grid */
        width: 100% !important; /* Ensure items fill their grid cell */
    }
    
    .service-type-heading {
        font-size: 9px;
        font-weight: 600;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 3px;
        letter-spacing: 0.5px;
        padding: 1px 0;
        line-height: 1.2;
    }
    
    /* Timeline service markers - black circles with white icons positioned directly on timeline */
    .service-item::before {
        content: '●'; /* default dot */
        position: absolute !important;
        left: -73px !important; /* Position to center on timeline */
        top: 8px !important; /* Position near top for grid layout */
        transform: none !important;
        width: 20px !important;
        height: 20px !important;
        border-radius: 50% !important;
        background: #000000 !important;
        border: 2px solid #ffffff !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        z-index: 20 !important; /* Higher z-index to appear above timeline */
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 10px !important;
        color: white !important;
        line-height: 1 !important;
        font-family: Arial, sans-serif !important;
        font-weight: bold !important;
    }
    
    /* Service-specific icons in timeline markers - using Unicode symbols */
    .service-item.hotel::before {
        content: '🏠' !important; /* house symbol */
        font-family: Arial, sans-serif !important;
        font-weight: normal !important;
    }
    
    .service-item.entry-port::before {
        content: '↓' !important; /* down arrow for arrival */
        font-family: Arial, sans-serif !important;
        font-weight: bold !important;
        font-size: 16px !important;
    }
    
    .service-item.exit-port::before {
        content: '↑' !important; /* up arrow for departure */
        font-family: Arial, sans-serif !important;
        font-weight: bold !important;
        font-size: 16px !important;
    }
    
    .service-item[data-service-type="guide"]::before {
        content: '👤' !important; /* person symbol */
        font-family: Arial, sans-serif !important;
        font-weight: normal !important;
    }
    
    .service-item[data-service-type*="transfer"]::before,
    .service-item[data-service-type*="travel"]::before,
    .service-item[data-service-type="shared"]::before,
    .service-item[data-service-type="private"]::before {
        content: '🚗' !important; /* car symbol */
        font-family: Arial, sans-serif !important;
        font-weight: normal !important;
    }
    
    .service-item[data-service-type="attraction"]::before {
        content: '🚗' !important; /* car for attraction transfers */
        font-family: Arial, sans-serif !important;
        font-weight: normal !important;
    }
    
    .service-item[data-service-type="restaurant"]::before {
        content: '🚗' !important; /* car for restaurant transfers */
        font-family: Arial, sans-serif !important;
        font-weight: normal !important;
    }
    
    /* Additional specific selectors for service types that might not match the class names */
    .service-item[data-service-type="arrival"]::before {
        content: '↓' !important; /* down arrow for arrival */
        font-family: Arial, sans-serif !important;
        font-weight: bold !important;
        font-size: 16px !important;
    }
    
    .service-item[data-service-type="departure"]::before {
        content: '↑' !important; /* up arrow for departure */
        font-family: Arial, sans-serif !important;
        font-weight: bold !important;
        font-size: 16px !important;
    }
    

    
    .service-item:hover {
        border-left-color: var(--primary-color);
        background: #f8f9fa;
    }
    
    .service-item:last-child {
        margin-bottom: 0;
    }
    
    .service-item-content {
        display: flex;
        align-items: flex-start;
        padding: 8px 12px;
        gap: 0;
        position: relative;
        min-width: 0 !important; /* Allow flex items to shrink */
    }
    
    .service-right-details {
        display: none; /* Hide right details to match screenshot design */
    }
    
    .service-right-image {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        object-fit: cover;
        margin-bottom: 4px;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        border: 1px solid rgba(226,232,240,0.8);
    }
    
    .service-right-name {
        font-size: 10px;
        font-weight: 600;
        color: #334155;
        line-height: 1.2;
        margin-bottom: 2px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 100%;
    }
    
    .service-right-location {
        font-size: 9px;
        color: #64748b;
        line-height: 1.2;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 100%;
    }
    
    /* Adjust main content - no margin needed since right details are hidden */
    .service-main-content {
        flex-grow: 1;
        min-width: 0 !important; /* Allow content to shrink in grid */
        overflow-wrap: break-word; /* Allow long text to wrap */
        word-wrap: break-word;
    }
    
    /* Service priority styling */
    .service-item.hotel {
        border-left: 2px solid #9c27b0;
        background: #ffffff;
    }
    
    .service-item.entry-port {
        border-left: 2px solid #2196f3;
        background: #ffffff;
    }
    
    .service-item.exit-port {
        border-left: 2px solid #f44336;
        background: #ffffff;
    }
    
    .service-item.locked {
        opacity: 0.9;
        position: relative;
    }
    
    .service-item.locked::after {
        content: '🔒';
        position: absolute;
        top: 8px;
        right: 8px;
        font-size: 10px;
        opacity: 0.6;
    }
    
    .service-left-icon {
        display: none; /* Remove redundant icon - timeline marker already shows icon */
    }
    
    .service-left-icon.flight {
        background: transparent;
        color: inherit;
    }
    
    .service-left-icon.hotel {
        background: transparent;
        color: inherit;
    }
    
    .service-left-icon.transfer {
        background: transparent;
        color: inherit;
    }
    
    .service-left-icon.guide {
        background: transparent;
        color: inherit;
    }
    
    .service-left-icon.attraction {
        background: transparent;
        color: inherit;
    }
    
    .service-left-icon.restaurant {
        background: transparent;
        color: inherit;
    }
    
    .service-left-icon.entry {
        background: transparent;
        color: inherit;
    }
    
    .service-left-icon.exit {
        background: transparent;
        color: inherit;
    }
    
    .service-left-icon.entryport {
        background: transparent;
        color: inherit;
    }
    
    .service-left-icon.exitport {
        background: transparent;
        color: inherit;
    }
    
    .service-main-content {
        flex-grow: 1;
        min-width: 0;
    }
    
    .service-header {
        margin-bottom: 3px;
        line-height: 1.2;
    }
    
    .service-time-display {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-primary);
        margin-right: 6px;
        display: inline;
    }
    
    .service-title {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        line-height: 1.2;
        letter-spacing: -0.01em;
        display: inline;
    }
    
    .service-details-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 6px;
    }
    
    .service-time-badge {
        display: none; /* Hide time badge - time is now shown in service-time-display */
    }
    
    .service-pax-badge {
        display: none; /* Hide pax badge to match screenshot design */
    }
    
    .service-description {
        color: var(--text-secondary);
        font-size: 12px;
        line-height: 1.4;
        margin: 2px 0 0 0;
        font-weight: 400;
    }
    
    .service-detail-line {
        margin: 2px 0;
        font-size: 12px;
        line-height: 1.4;
        color: var(--text-secondary);
    }
    
    .service-detail-line.compact-line {
        margin: 1px 0;
        line-height: 1.3;
    }
    
    .hotel-details-compact,
    .guide-details-compact {
        margin-top: 3px;
    }
    
    .hotel-details-compact .service-detail-line,
    .guide-details-compact .service-detail-line {
        margin: 0;
        padding: 0;
    }
    
    .hotel-details-compact .badge,
    .guide-details-compact .badge {
        display: inline-block;
        vertical-align: middle;
        margin-left: 4px;
    }
    
    .service-detail-label {
        font-weight: 600;
        color: var(--text-primary);
        margin-right: 4px;
    }
    
    .service-remark {
        margin-top: 6px;
        padding: 6px 8px;
        background: #f8f9fa;
        border-left: 2px solid var(--primary-color);
        border-radius: 3px;
        font-size: 11px;
        color: var(--text-secondary);
        line-height: 1.4;
    }
    
    .service-type-tag {
        display: none; /* Hide service type tag to match screenshot design */
    }
    
    .service-type-tag.hotel {
        background: #f3e5f5;
        color: #7b1fa2;
    }
    
    .service-type-tag.flight {
        background: #e3f2fd;
        color: #1976d2;
    }
    
    .service-type-tag.transfer {
        background: #fff3e0;
        color: #f57c00;
    }
    
    .service-type-tag.guide {
        background: #e8f5e9;
        color: #388e3c;
    }
    
    .service-type-tag.attraction {
        background: #e0f7fa;
        color: #00838f;
    }
    
    .service-type-tag.restaurant {
        background: #fff8e1;
        color: #f57f17;
    }
    
    .service-type-tag.entry {
        background: #f3e5f5;
        color: #7b1fa2;
    }
    
    .service-type-tag.exit {
        background: #ffebee;
        color: #c62828;
    }
    
    /* Hide arrows as requested */
    .service-arrow {
        display: none;
    }
    
    .no-service {
        padding: 40px;
        color: var(--text-tertiary);
        text-align: center;
        background: var(--background-tertiary);
        border-radius: 8px;
        margin: 0;
        font-size: 14px;
        border: 2px dashed var(--border-color);
        position: relative;
        grid-column: 1 / -1 !important; /* Span all columns */
    }
    
    .no-service::before {
        content: '';
        display: block;
        width: 48px;
        height: 48px;
        margin: 0 auto 16px auto;
        background: var(--border-color);
        border-radius: 50%;
        opacity: 0.3;
        position: relative;
    }
    
    .no-service::after {
        content: '📋';
        position: absolute;
        top: 52px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 20px;
        opacity: 0.4;
    }
    
    /* Drag and Drop Styles */
    .service-item.draggable {
        cursor: grab;
        position: relative;
    }
    
    .service-item.draggable:hover {
        background-color: #f0f8ff;
        border-left: 4px solid #435ebe;
    }
    
    .service-item.dragging {
        opacity: 0.5;
        transform: rotate(2deg);
        cursor: grabbing;
        z-index: 1000;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    }
    
    .service-item.non-draggable {
        opacity: 0.7;
        position: relative;
    }
    
    .service-item.non-draggable::after {
        content: "🔒";
        position: absolute;
        top: 5px;
        right: 10px;
        font-size: 12px;
        opacity: 0.6;
    }
    
    .date-container.drag-over {
        transform: scale(1.01);
    }
    
    .date-container.drag-over .day-circle {
        background: var(--primary-color) !important;
        transform: scale(1.1);
    }
    
    .date-container.drag-over .services-list,
    .services-list.drag-over {
        background: rgba(37,99,235,0.05);
        border-radius: 8px;
        border-left-color: rgba(37,99,235,0.3);
    }
    
    .drag-indicator {
        position: absolute;
        left: 1px;
        right: 1px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 16px;
        color: #666;
        cursor: grab;
    }
    
    .service-item.draggable .drag-indicator {
        display: block;
    }
    
    .service-item.non-draggable .drag-indicator {
        display: none;
    }
    
    .drop-zone-indicator {
        height: 4px;
        background: linear-gradient(90deg, #435ebe, #6c7ae0);
        border-radius: 2px;
        margin: 8px 0;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .drop-zone-indicator.active {
        opacity: 1;
    }
    
    /* Print Styles */
    @media print {
        .itinerary-container {
            background: white !important;
            box-shadow: none !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .itinerary-header {
            background: white !important;
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            margin: 0 0 20px 0 !important;
            page-break-inside: avoid;
        }
        
        .header-actions {
            display: none !important;
        }
        
        .day-indicator {
            background: white !important;
            border: 1px solid #ddd !important;
            box-shadow: none !important;
            page-break-inside: avoid;
        }
        
        .service-item {
            background: white !important;
            border: 1px solid #e0e0e0 !important;
            box-shadow: none !important;
            page-break-inside: avoid;
            margin-bottom: 6px !important;
        }
        
        .service-item.hotel {
            border-left: 3px solid #9c27b0 !important;
        }
        
        .service-item.entry-port {
            border-left: 3px solid #2196f3 !important;
        }
        
        .service-item.exit-port {
            border-left: 3px solid #f44336 !important;
        }
        
        .timeline-line {
            background: repeating-linear-gradient(
                to bottom,
                #999 0px,
                #999 6px,
                transparent 6px,
                transparent 12px
            ) !important;
        }
        
        .service-item::before {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            background: #333 !important;
            border-color: #fff !important;
            color: white !important;
        }
        
        .services-list {
            border-left: 1px solid #ddd !important;
        }
        
        .services-list::before {
            background: #ddd !important;
        }
        
        .date-container {
            page-break-inside: avoid;
        }
        
        .print-btn {
            display: none !important;
        }
        
        .no-service {
            background: #f5f5f5 !important;
            border: 1px dashed #ccc !important;
        }
        
        /* Ensure colors are preserved in print */
        .day-circle {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        
        .service-left-icon {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        
        .service-time-badge,
        .service-pax-badge {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        
        .service-right-details {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            background: rgba(248,250,252,0.6) !important;
            border: 1px solid #ddd !important;
        }
        
        .service-right-image {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
    }
    
    @media (max-width: 992px) {
        .service-item {
            gap: 15px;
            padding: 16px 20px;
        }
        
        .service-type {
            min-width: 110px;
        }
        
        .service-date {
            min-width: 180px;
        }
    }
    
    @media (max-width: 768px) {
        .itinerary-container {
            background: var(--background-secondary);
        }
        
        .itinerary-header {
            margin: 0 16px 16px 16px;
            padding: 16px;
        }
        
        .header-content {
            flex-direction: column;
            gap: 12px;
        }
        
        .header-actions {
            width: 100%;
            justify-content: space-between;
        }
        
        .timeline-container {
            padding: 0 16px 16px 16px;
        }
        
        .date-container {
            margin-bottom: 16px;
        }
        
        .timeline-line {
            left: 46px;
        }
        
        .day-circle {
            min-width: 60px;
            height: 30px;
            font-size: 10px;
        }
        
        .day-indicator {
            padding: 2px 3px;
        }
        
        .day-title {
            font-size: 14px;
        }
        
        .day-date {
            font-size: 12px;
        }
        
        .services-list {
            margin-left: 60px;
            padding-left: 36px;
            grid-template-columns: 1fr !important; /* Single column on mobile */
            gap: 8px !important;
        }
        
        .service-item::before {
            left: -70px !important; /* Position to center on mobile timeline */
            width: 24px !important;
            height: 24px !important;
            font-size: 12px !important;
            border: 2px solid #ffffff !important;
            top: 8px !important;
            transform: none !important;
            z-index: 20 !important; /* Higher z-index to appear above timeline */
        }
        
        .service-item-content {
            padding: 10px 12px;
            gap: 10px;
        }
        
        .service-left-icon {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }
        
        .service-title {
            font-size: 14px;
        }
        
        .service-description {
            font-size: 12px;
        }
        
        .service-time-badge,
        .service-pax-badge {
            font-size: 10px;
            padding: 3px 6px;
        }
        
        .service-details-row {
            gap: 6px;
        }
        
        .service-right-details {
            right: 8px;
            top: 8px;
            bottom: 8px;
            width: 80px;
            padding: 4px;
        }
        
        .service-right-image {
            width: 24px;
            height: 24px;
            font-size: 14px;
            margin-bottom: 2px;
        }
        
        .service-right-name {
            font-size: 8px;
            margin-bottom: 1px;
        }
        
        .service-right-location {
            font-size: 7px;
        }
        
        .service-main-content {
            margin-right: 90px;
        }
    }
    
    /* Hotel Details Styles - Enhanced & Compact */
    .hotel-details {
        background: #f8f9fa;
        border-radius: 4px;
        padding: 6px 8px;
        margin-top: 4px;
        border: 1px solid #e2e8f0;
    }
    
    .hotel-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px;
        margin-bottom: 4px;
    }
    
    .hotel-info-item {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 3px;
        padding: 4px 6px;
        display: flex;
        align-items: center;
        gap: 3px;
        box-shadow: none;
        font-size: 11px;
    }
    
    .hotel-info-item i {
        width: 16px;
        text-align: center;
        color: var(--primary-color);
        font-size: 12px;
    }
    
    .hotel-info-item small {
        color: #374151 !important;
        font-weight: 500;
    }
    
    .rooms-summary {
        background: rgba(255, 255, 255, 0.8);
        border-radius: 3px;
        padding: 6px 8px;
        margin-top: 4px;
        border: 1px solid #e2e8f0;
        font-size: 11px;
    }
    
    .rooms-header {
        margin-bottom: 10px;
        padding-bottom: 6px;
        border-bottom: 2px solid var(--primary-color);
    }
    
    .rooms-header small {
        color: var(--primary-color) !important;
        font-weight: 600;
        font-size: 13px;
    }
    
    .room-item {
        background: #f8f9fa;
        border-radius: 3px;
        padding: 6px 8px;
        margin-bottom: 4px;
        border-left: 2px solid #10b981;
        box-shadow: none;
        position: relative;
    }
    
    .room-item::before {
        display: none;
    }
    
    .room-basic-info {
        margin-bottom: 3px;
    }
    
    .room-basic-info small {
        color: #1f2937 !important;
        font-weight: 600;
        font-size: 11px;
    }
    
    .bed-details {
        margin-left: 4px;
        padding-left: 4px;
        border-left: 1px solid #e5e7eb;
    }
    
    .bed-info {
        margin-bottom: 3px;
        background: rgba(255, 255, 255, 0.7);
        border-radius: 3px;
        padding: 4px 6px;
        font-size: 11px;
    }
    
    .bed-info small {
        color: #4b5563 !important;
        font-weight: 500;
    }
    
    .meal-info {
        margin-left: 0;
        margin-top: 3px;
        display: flex;
        flex-wrap: wrap;
        gap: 3px;
        flex-wrap: wrap;
        gap: 4px;
    }
    
    .meal-badge {
        background: linear-gradient(135deg, #059669, #10b981) !important;
        color: white !important;
        font-size: 10px !important;
        padding: 2px 5px !important;
        margin: 0 !important;
        padding: 4px 8px !important;
        border-radius: 6px !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 4px rgba(5, 150, 105, 0.3);
        border: none !important;
        display: inline-flex;
        align-items: center;
    }
    
    .meal-badge i {
        font-size: 10px;
        margin-right: 4px;
    }
    
    .meal-price {
        background: rgba(255, 255, 255, 0.2);
        padding: 1px 4px;
        border-radius: 3px;
        margin-left: 4px;
        font-size: 10px;
        font-weight: 700;
    }
    
    .badge-sm {
        font-size: 10px;
        padding: 3px 6px;
        font-weight: 600;
    }
    
    .price-mode-badge {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        font-size: 9px;
        padding: 2px 6px;
        border-radius: 4px;
        margin-left: 4px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Attraction Details Styles - Similar to Hotel */
    .attraction-details {
        background: #f8f9fa;
        border-radius: 4px;
        padding: 6px 8px;
        margin-top: 4px;
        border: 1px solid #e2e8f0;
    }
    
    .attraction-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px;
        margin-bottom: 4px;
    }
    
    .attraction-info-item {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 3px;
        padding: 4px 6px;
        display: flex;
        align-items: center;
        gap: 3px;
        box-shadow: none;
        font-size: 11px;
    }
    
    .attraction-info-item i {
        width: 16px;
        text-align: center;
        color: #ef4444;
        font-size: 12px;
    }
    
    .attraction-info-item small {
        color: #374151 !important;
        font-weight: 500;
    }
    
    .ticket-summary {
        background: rgba(255, 255, 255, 0.8);
        border-radius: 3px;
        padding: 6px 8px;
        margin-top: 4px;
        border: 1px solid #e2e8f0;
        font-size: 11px;
    }
    
    .ticket-header {
        margin-bottom: 10px;
        padding-bottom: 6px;
        border-bottom: 2px solid #ef4444;
    }
    
    .ticket-header small {
        color: #ef4444 !important;
        font-weight: 600;
        font-size: 13px;
    }
    
    .ticket-item {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 8px;
        border-left: 4px solid #f59e0b;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        position: relative;
    }
    
    .ticket-item::before {
        content: '';
        position: absolute;
        top: 8px;
        right: 8px;
        width: 8px;
        height: 8px;
        background: #f59e0b;
        border-radius: 50%;
    }
    
    .ticket-basic-info {
        margin-bottom: 6px;
    }
    
    .ticket-basic-info small {
        color: #1f2937 !important;
        font-weight: 600;
    }
    
    .ticket-id-badge {
        background: linear-gradient(135deg, #6b7280, #9ca3af);
        color: white;
        font-size: 8px;
        padding: 1px 4px;
        border-radius: 3px;
        margin-left: 6px;
        font-weight: 600;
    }
    
    .visitor-info {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 6px;
        padding: 6px 8px;
        margin-bottom: 6px;
    }
    
    .visitor-info small {
        color: #4b5563 !important;
        font-weight: 500;
    }
    
    .pricing-info {
        margin-top: 6px;
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    
    .pricing-badge {
        background: linear-gradient(135deg, #f59e0b, #fbbf24) !important;
        color: white !important;
        font-size: 10px !important;
        padding: 3px 6px !important;
        border-radius: 5px !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
        border: none !important;
        display: inline-flex;
        align-items: center;
    }
    
    .pricing-badge i {
        font-size: 9px;
        margin-right: 3px;
    }
    
    .transport-info {
        margin-top: 6px;
    }
    
    .transport-badge {
        background: linear-gradient(135deg, #8b5cf6, #a78bfa) !important;
        color: white !important;
        font-size: 10px !important;
        padding: 3px 8px !important;
        border-radius: 5px !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 4px rgba(139, 92, 246, 0.3);
        border: none !important;
        display: inline-flex;
        align-items: center;
    }
    
    .transport-badge i {
        font-size: 9px;
        margin-right: 4px;
    }
    
    /* Restaurant Details Styles - Similar to Hotel/Attraction */
    .restaurant-details {
        background: #f8f9fa;
        border-radius: 4px;
        padding: 6px 8px;
        margin-top: 4px;
        border: 1px solid #e2e8f0;
    }
    
    .restaurant-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px;
        margin-bottom: 4px;
    }
    
    .restaurant-info-item {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 3px;
        padding: 4px 6px;
        display: flex;
        align-items: center;
        gap: 3px;
        box-shadow: none;
        font-size: 11px;
    }
    
    .restaurant-info-item i {
        width: 16px;
        text-align: center;
        color: #22c55e;
        font-size: 12px;
    }
    
    .restaurant-info-item small {
        color: #374151 !important;
        font-weight: 500;
    }
    
    .meal-summary {
        background: rgba(255, 255, 255, 0.8);
        border-radius: 3px;
        padding: 6px 8px;
        margin-top: 4px;
        border: 1px solid #e2e8f0;
        font-size: 11px;
    }
    
    .meal-header {
        margin-bottom: 10px;
        padding-bottom: 6px;
        border-bottom: 2px solid #22c55e;
    }
    
    .meal-header small {
        color: #22c55e !important;
        font-weight: 600;
        font-size: 13px;
    }
    
    .meal-item {
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 8px;
        border-left: 4px solid #f97316;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        position: relative;
    }
    
    .meal-item::before {
        content: '';
        position: absolute;
        top: 8px;
        right: 8px;
        width: 8px;
        height: 8px;
        background: #f97316;
        border-radius: 50%;
    }
    
    .meal-basic-info {
        margin-bottom: 6px;
    }
    
    .meal-basic-info small {
        color: #1f2937 !important;
        font-weight: 600;
    }
    
    .meal-price-badge {
        background: linear-gradient(135deg, #16a34a, #22c55e);
        color: white;
        font-size: 9px;
        padding: 2px 6px;
        border-radius: 4px;
        margin-left: 6px;
        font-weight: 600;
    }
    
    .guest-info {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 6px;
        padding: 6px 8px;
        margin-bottom: 6px;
    }
    
    .guest-info small {
        color: #4b5563 !important;
        font-weight: 500;
    }
    
    .meal-items-info {
        margin-top: 8px;
    }
    
    .meal-items-list {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    
    .meal-item-badge {
        background: linear-gradient(135deg, #f97316, #fb923c) !important;
        color: white !important;
        font-size: 10px !important;
        padding: 3px 6px !important;
        border-radius: 5px !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);
        border: none !important;
        display: inline-flex;
        align-items: center;
        line-height: 1.2;
    }
    
    .item-category {
        opacity: 0.8;
        font-size: 8px !important;
        margin-left: 2px;
    }
    
    .item-type {
        opacity: 0.9;
        font-size: 8px !important;
        margin-left: 2px;
    }
    
    /* Guide Details Styles - Purple Theme */
    .guide-details {
        background: linear-gradient(135deg, rgba(147, 51, 234, 0.1), rgba(168, 85, 247, 0.05));
        border-radius: 12px;
        padding: 16px;
        margin-top: 10px;
        border: 1px solid rgba(147, 51, 234, 0.2);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .guide-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .guide-info-item {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 8px;
        padding: 8px 10px;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .guide-info-item i {
        width: 16px;
        text-align: center;
        color: #9333ea;
        font-size: 12px;
    }
    
    .guide-info-item small {
        color: #374151 !important;
        font-weight: 500;
    }
    
    .guide-summary {
        background: rgba(255, 255, 255, 0.8);
        border-radius: 3px;
        padding: 6px 8px;
        margin-top: 4px;
        border: 1px solid #e2e8f0;
        font-size: 11px;
    }
    
    .guide-header {
        margin-bottom: 10px;
        padding-bottom: 6px;
        border-bottom: 2px solid #9333ea;
    }
    
    .guide-header small {
        color: #9333ea !important;
        font-weight: 600;
        font-size: 13px;
    }
    
    .guide-item {
        background: linear-gradient(135deg, #faf5ff, #f3e8ff);
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 8px;
        border-left: 4px solid #06b6d4;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        position: relative;
    }
    
    .guide-item::before {
        content: '';
        position: absolute;
        top: 8px;
        right: 8px;
        width: 8px;
        height: 8px;
        background: #06b6d4;
        border-radius: 50%;
    }
    
    .guide-basic-info {
        margin-bottom: 6px;
    }
    
    .guide-basic-info small {
        color: #1f2937 !important;
        font-weight: 600;
    }
    
    .experience-badge {
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        color: white;
        font-size: 9px;
        padding: 2px 6px;
        border-radius: 4px;
        margin-left: 6px;
        font-weight: 600;
    }
    
    .group-info {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 6px;
        padding: 6px 8px;
        margin-bottom: 6px;
    }
    
    .group-info small {
        color: #4b5563 !important;
        font-weight: 500;
    }
    
    .languages-info {
        margin-top: 8px;
    }
    
    .languages-list {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    
    .language-badge {
        background: linear-gradient(135deg, #7c3aed, #8b5cf6) !important;
        color: white !important;
        font-size: 10px !important;
        padding: 3px 6px !important;
        border-radius: 5px !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 4px rgba(124, 58, 237, 0.3);
        border: none !important;
        display: inline-flex;
        align-items: center;
        line-height: 1.2;
    }
    
    .proficiency {
        opacity: 0.8;
        font-size: 8px !important;
        margin-left: 2px;
    }
    
    /* Entry Port Details Styles - Orange/Amber Theme */
    .entry-port-details {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(251, 191, 36, 0.05));
        border-radius: 12px;
        padding: 16px;
        margin-top: 10px;
        border: 1px solid rgba(245, 158, 11, 0.2);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .entry-port-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .entry-port-info-item {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 8px;
        padding: 8px 10px;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .entry-port-info-item i {
        width: 16px;
        text-align: center;
        color: #f59e0b;
        font-size: 12px;
    }
    
    .entry-port-info-item small {
        color: #374151 !important;
        font-weight: 500;
    }
    
    .transfer-summary {
        background: rgba(255, 255, 255, 0.6);
        border-radius: 10px;
        padding: 12px;
        position: relative;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .transfer-summary::before {
        content: '';
        position: absolute;
        top: 8px;
        right: 8px;
        width: 8px;
        height: 8px;
        background: #f59e0b;
        border-radius: 50%;
    }
    
    .transfer-header {
        margin-bottom: 6px;
    }
    
    .transfer-header small {
        color: #1f2937 !important;
        font-weight: 600;
    }
    
    .transfer-item {
        margin-top: 8px;
    }
    
    .pickup-info, .dropoff-info {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 6px;
        padding: 6px 8px;
        margin-bottom: 6px;
    }
    
    .pickup-info small, .dropoff-info small {
        color: #4b5563 !important;
        font-weight: 500;
    }
    
    .vehicle-info {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 6px;
        padding: 6px 8px;
        margin-top: 6px;
    }
    
    .vehicle-info small {
        color: #4b5563 !important;
        font-weight: 500;
    }
    
    /* Exit Port Details Styles - Teal/Cyan Theme */
    .exit-port-details {
        background: linear-gradient(135deg, rgba(20, 184, 166, 0.1), rgba(45, 212, 191, 0.05));
        border-radius: 12px;
        padding: 16px;
        margin-top: 10px;
        border: 1px solid rgba(20, 184, 166, 0.2);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .exit-port-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .exit-port-info-item {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 8px;
        padding: 8px 10px;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .exit-port-info-item i {
        width: 16px;
        text-align: center;
        color: #14b8a6;
        font-size: 12px;
    }
    
    .exit-port-info-item small {
        color: #374151 !important;
        font-weight: 500;
    }
    
    .departure-summary {
        background: rgba(255, 255, 255, 0.6);
        border-radius: 10px;
        padding: 12px;
        position: relative;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .departure-summary::before {
        content: '';
        position: absolute;
        top: 8px;
        right: 8px;
        width: 8px;
        height: 8px;
        background: #14b8a6;
        border-radius: 50%;
    }
    
    .departure-header {
        margin-bottom: 6px;
    }
    
    .departure-header small {
        color: #1f2937 !important;
        font-weight: 600;
    }
    
    .departure-item {
        margin-top: 8px;
    }
    
    .departure-pickup-info, .departure-dropoff-info {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 6px;
        padding: 6px 8px;
        margin-bottom: 6px;
    }
    
    .departure-pickup-info small, .departure-dropoff-info small {
        color: #4b5563 !important;
        font-weight: 500;
    }
    
    .departure-vehicle-info {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 6px;
        padding: 6px 8px;
        margin-top: 6px;
    }
    
    .departure-vehicle-info small {
        color: #4b5563 !important;
        font-weight: 500;
    }
    
    /* Transfer Details Styles - Purple/Indigo Theme */
    .transfer-details {
        background: #f8f9fa;
        border-radius: 4px;
        padding: 6px 8px;
        margin-top: 4px;
        border: 1px solid #e2e8f0;
    }
    
    .transfer-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px;
        margin-bottom: 4px;
    }
    
    .transfer-info-item {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 3px;
        padding: 4px 6px;
        display: flex;
        align-items: center;
        gap: 3px;
        box-shadow: none;
        font-size: 11px;
    }
    
    .transfer-info-item i {
        width: 16px;
        text-align: center;
        color: #6366f1;
        font-size: 12px;
    }
    
    .transfer-info-item small {
        color: #374151 !important;
        font-weight: 500;
    }
    
    .tax-info {
        font-size: 10px;
        color: #6b7280 !important;
        margin-left: 4px;
    }
    
    .transportation-summary {
        background: rgba(255, 255, 255, 0.6);
        border-radius: 3px;
        padding: 6px 8px;
        position: relative;
        box-shadow: none;
        font-size: 11px;
    }
    
    .transportation-summary::before {
        content: '';
        position: absolute;
        top: 8px;
        right: 8px;
        width: 8px;
        height: 8px;
        background: #6366f1;
        border-radius: 50%;
    }
    
    .transportation-header {
        margin-bottom: 6px;
    }
    
    .transportation-header small {
        color: #1f2937 !important;
        font-weight: 600;
    }
    
    .service-category-badge {
        background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        color: white;
        font-size: 9px;
        padding: 2px 6px;
        border-radius: 4px;
        margin-left: 6px;
        font-weight: 600;
    }
    
    .transportation-item {
        margin-top: 8px;
    }
    
    .transport-pickup-info, .transport-dropoff-info {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 6px;
        padding: 6px 8px;
        margin-bottom: 6px;
    }
    
    .transport-pickup-info small, .transport-dropoff-info small {
        color: #4b5563 !important;
        font-weight: 500;
    }
    
    .transport-vehicle-info {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 6px;
        padding: 6px 8px;
        margin-top: 6px;
    }
    
    .transport-vehicle-info small {
        color: #4b5563 !important;
        font-weight: 500;
    }
    
    /* Responsive adjustments for hotel details */
    @media (max-width: 768px) {
        .hotel-details {
            padding: 12px;
        }
        
        .hotel-info-grid {
            grid-template-columns: 1fr;
            gap: 6px;
        }
        
        .hotel-info-item {
            padding: 6px 8px;
        }
        
        .rooms-summary {
            padding: 10px;
        }
        
        .room-item {
            padding: 8px;
        }
        
        .bed-details {
            margin-left: 6px;
            padding-left: 6px;
        }
        
        .bed-info {
            padding: 4px 6px;
        }
        
        .meal-badge {
            font-size: 10px !important;
            padding: 3px 6px !important;
        }
        
        .meal-price {
            font-size: 9px;
        }
        
        /* Attraction responsive styles */
        .attraction-details {
            padding: 12px;
        }
        
        .attraction-info-grid {
            grid-template-columns: 1fr;
            gap: 6px;
        }
        
        .attraction-info-item {
            padding: 6px 8px;
        }
        
        .ticket-summary {
            padding: 10px;
        }
        
        .ticket-item {
            padding: 8px;
        }
        
        .visitor-info {
            padding: 4px 6px;
        }
        
        .pricing-badge {
            font-size: 9px !important;
            padding: 2px 5px !important;
        }
        
        .transport-badge {
            font-size: 9px !important;
            padding: 2px 6px !important;
        }
        
        /* Restaurant responsive styles */
        .restaurant-details {
            padding: 12px;
        }
        
        .restaurant-info-grid {
            grid-template-columns: 1fr;
            gap: 6px;
        }
        
        .restaurant-info-item {
            padding: 6px 8px;
        }
        
        .meal-summary {
            padding: 10px;
        }
        
        .meal-item {
            padding: 8px;
        }
        
        .guest-info {
            padding: 4px 6px;
        }
        
        .meal-item-badge {
            font-size: 9px !important;
            padding: 2px 5px !important;
        }
        
        .item-category, .item-type {
            font-size: 7px !important;
        }
        
        /* Guide responsive styles */
        .guide-details {
            padding: 12px;
        }
        
        .guide-info-grid {
            grid-template-columns: 1fr;
            gap: 6px;
        }
        
        .guide-info-item {
            padding: 6px 8px;
        }
        
        .guide-summary {
            padding: 10px;
        }
        
        .guide-item {
            padding: 8px;
        }
        
        .group-info {
            padding: 4px 6px;
        }
        
        .language-badge {
            font-size: 9px !important;
            padding: 2px 5px !important;
        }
        
        .proficiency {
            font-size: 7px !important;
        }
        
        /* Entry Port responsive styles */
        .entry-port-details {
            padding: 12px;
        }
        
        .entry-port-info-grid {
            grid-template-columns: 1fr;
            gap: 6px;
        }
        
        .entry-port-info-item {
            padding: 6px 8px;
        }
        
        .transfer-summary {
            padding: 10px;
        }
        
        .transfer-item {
            padding: 8px;
        }
        
        .pickup-info, .dropoff-info, .vehicle-info {
            padding: 4px 6px;
        }
        
        /* Exit Port responsive styles */
        .exit-port-details {
            padding: 12px;
        }
        
        .exit-port-info-grid {
            grid-template-columns: 1fr;
            gap: 6px;
        }
        
        .exit-port-info-item {
            padding: 6px 8px;
        }
        
        .departure-summary {
            padding: 10px;
        }
        
        .departure-item {
            padding: 8px;
        }
        
        .departure-pickup-info, .departure-dropoff-info, .departure-vehicle-info {
            padding: 4px 6px;
        }
        
        /* Transfer responsive styles */
        .transfer-details {
            padding: 12px;
        }
        
        .transfer-info-grid {
            grid-template-columns: 1fr;
            gap: 6px;
        }
        
        .transfer-info-item {
            padding: 6px 8px;
        }
        
        .transportation-summary {
            padding: 10px;
        }
        
        .transportation-item {
            padding: 8px;
        }
        
        .transport-pickup-info, .transport-dropoff-info, .transport-vehicle-info {
            padding: 4px 6px;
        }
        
        .service-category-badge {
            font-size: 8px !important;
            padding: 1px 4px !important;
        }
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="itinerary-container">
            <!-- Print-only header with company logo -->
            <div class="print-header print-only">
                <div class="print-company-info">
                    <!-- <img src="{{ asset('assets/img/logo.png') }}" alt="Company Logo" class="company-logo-print"> -->
                    <!-- <h4>{{ config('app.name', 'Coactive Tours & Travel') }}</h4> -->
                </div>
                <div>
                    <h5>
                        Tour #{{ $tourId }} 
                        @if(isset($tourDetails->display_id))
                            ({{ $tourDetails->display_id }})
                        @endif
                    </h5>
                    <p>
                        @if(isset($tourDetails->destination))
                            <span class="me-3"><i class="fas fa-map-marker-alt me-1"></i>{{ $tourDetails->destination }}</span>
                        @endif
                        
                        @if(isset($tourDetails->check_in_time) && isset($tourDetails->check_out_time))
                            <span><i class="fas fa-calendar-alt me-1"></i>
                            {{ \Carbon\Carbon::parse($tourDetails->check_in_time)->format('d M Y') }} - 
                            {{ \Carbon\Carbon::parse($tourDetails->check_out_time)->format('d M Y') }}
                            </span>
                        @endif
                    </p>
                    
                    @if(isset($customerInfo))
                        <div class="customer-print-info border-top pt-2 mt-2">
                            <p class="mb-1">
                                @if(isset($customerInfo['fullName']))
                                    <strong>{{ $customerInfo['fullName'] }}</strong>
                                @endif
                                
                                @if(isset($customerInfo['email']))
                                    <span class="mx-2">|</span>{{ $customerInfo['email'] }}
                                @endif
                                
                                @if(isset($customerInfo['phone']))
                                    <span class="mx-2">|</span>{{ $customerInfo['phone'] }}
                                @endif
                            </p>
                        </div>
                    @endif
                    
                    <p class="text-muted small mt-2">Generated on {{ now()->format('d M Y') }}</p>
                </div>
            </div>
            
            <!-- Itinerary Header -->
            <div class="itinerary-header">
                <div class="header-content">
                    <div class="header-info">
                        <h4>
                            Tour #{{ $tourId }}
                            @if(isset($tourDetails->display_id))
                                ({{ $tourDetails->display_id }})
                            @endif
                        </h4>
                        @if($tourDetails)
                            <h5>
                                <i class="fas fa-map-marker-alt" style="color: var(--primary-color);"></i>
                                {{ $tourDetails->destination ?? 'Destination Not Specified' }}
                                @if(isset($tourDetails->check_in_time) && isset($tourDetails->check_out_time))
                                    @php
                                        $startDate = \Carbon\Carbon::parse($tourDetails->check_in_time);
                                        $endDate = \Carbon\Carbon::parse($tourDetails->check_out_time);
                                    @endphp
                                    <span style="margin-left: 12px;">
                                        <i class="fas fa-calendar-alt" style="color: var(--primary-color);"></i>
                                        {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
                                    </span>
                                @elseif(count($itineraryByDate) > 0)
                                    @php
                                        $dates = array_keys($itineraryByDate);
                                        $startDate = \Carbon\Carbon::parse($dates[0]);
                                        $endDate = \Carbon\Carbon::parse($dates[count($dates)-1]);
                                    @endphp
                                    <span style="margin-left: 12px;">
                                        <i class="fas fa-calendar-alt" style="color: var(--primary-color);"></i>
                                        {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
                                    </span>
                                @endif
                            </h5>
                        @endif
                    </div>
                    <div class="header-actions">
                        {{-- <a href="{{ route('bookinglist.index') }}" class="btn-modern btn-secondary-modern">
                            <i class="fas fa-arrow-left"></i> Back to Bookings
                        </a> --}}
                        <button id="downloadText" class="btn-modern btn-secondary-modern">
                            <i class="fas fa-file-alt"></i> Download Itinerary
                        </button>
                        <button id="downloadExcelFormat" class="btn-modern btn-secondary-modern">
                            <i class="fas fa-file-excel"></i> Download Excel Format
                        </button>
                        <button id="downloadPdf" class="btn-modern btn-secondary-modern">
                            <i class="fas fa-download"></i> Download PDF
                        </button>
                        <button id="printItinerary" class="btn-modern btn-primary-modern">
                            <i class="fas fa-print"></i> Print Itinerary
                        </button>
                    </div>        
                </div>
            </div>
            
            <!-- Days Timeline -->
            <div class="timeline-container">
                @php 
                    // Create a date range to display all days between start and end dates
                    $allDates = [];
                    
                    if (isset($tourDetails->check_in_time) && isset($tourDetails->check_out_time)) {
                        // Use tour start and end dates if available
                        $startDate = \Carbon\Carbon::parse($tourDetails->check_in_time);
                        $endDate = \Carbon\Carbon::parse($tourDetails->check_out_time);
                    } elseif (count($itineraryByDate) > 0) {
                        // Use dates from bookings if tour dates aren't available
                        $dateKeys = array_keys($itineraryByDate);
                        $startDate = \Carbon\Carbon::parse($dateKeys[0]);
                        $endDate = \Carbon\Carbon::parse($dateKeys[count($dateKeys)-1]);
                    } else {
                        // No dates available - will just display empty state
                        $startDate = null;
                        $endDate = null;
                    }
                    
                    // Optional debugging info - uncomment for development use only
                    /*
                    if (isset($tourDetails->booking)) {
                        echo "<div class='debug-section' style='background:#eee; padding:10px; margin-bottom:20px; font-size:12px; border-radius:5px;'>";
                        echo "<h5>Debug: Raw Booking Data</h5>";
                        echo "<p>Number of bookings: " . $tourDetails->booking->count() . "</p>";
                        foreach ($tourDetails->booking as $index => $booking) {
                            echo "<div style='margin-bottom:10px; border-bottom:1px solid #ccc;'>";
                            echo "<p><strong>Booking #" . ($index+1) . "</strong></p>";
                            echo "<p>ID: " . ($booking->booking_id ?? 'N/A') . "</p>";
                            echo "<p>Type: " . ($booking->type ?? 'N/A') . "</p>";
                            echo "<p>Data: <pre>" . json_encode(is_string($booking->data) ? json_decode($booking->data) : $booking->data, JSON_PRETTY_PRINT) . "</pre></p>";
                            echo "</div>";
                        }
                        echo "</div>";
                    }
                    */
                    
                    // Process bookings from the booking relation if available
                    if (isset($tourDetails->booking) && $tourDetails->booking->count() > 0) {
                        // Initialize or merge with existing itineraryByDate
                        if (!isset($itineraryByDate)) {
                            $itineraryByDate = [];
                        }
                        
                        foreach ($tourDetails->booking as $booking) {
                            // Extract data from the booking
                            $bookingData = null;
                            
                            // Parse data if it's JSON
                            if (is_string($booking->data)) {
                                try {
                                    $bookingData = json_decode($booking->data, true);
                                } catch (\Exception $e) {
                                    // If JSON parsing fails, try to use as is
                                    $bookingData = $booking->data;
                                }
                            } else {
                                // Data is already an array or object
                                $bookingData = $booking->data;
                            }
                            
                            // Ensure it's array of items
                            if (!is_array($bookingData)) {
                                continue;
                            }
                            
                            // If not indexed array, wrap in array
                            if (!isset($bookingData[0]) && count($bookingData) > 0) {
                                $bookingData = [$bookingData];
                            }
                            
                            // Process each item
                            foreach ($bookingData as $item) {
                                // Special handling for hotels with date ranges
                                if (strtolower($booking->type) == 'hotel' && isset($item['bookingDate']) && is_array($item['bookingDate']) && count($item['bookingDate']) >= 2) {
                                    $checkIn = \Carbon\Carbon::parse($item['bookingDate'][0]);
                                    $checkOut = \Carbon\Carbon::parse($item['bookingDate'][1]);
                                    
                                    // Create unique identifier for this hotel booking
                                    $hotelIdentifier = [
                                        'name' => $item['hotelDetails']['hotel_name'] ?? '',
                                        'check_in' => $item['bookingDate'][0] ?? '',
                                        'check_out' => $item['bookingDate'][1] ?? '',
                                        'price' => $item['totalPrice'] ?? '',
                                        'price_mode' => $item['priceMode'] ?? '',
                                        'booking_id' => $booking->id ?? ''
                                    ];
                                    $hotelId = md5(json_encode($hotelIdentifier));
                                    
                                    // Generate a booking for each day in the range
                                    $currentDay = $checkIn->copy();
                                    $totalNights = $checkIn->diffInDays($checkOut);
                                    
                                    while ($currentDay->lt($checkOut)) {
                                        $dateStr = $currentDay->format('Y-m-d');
                                        $dayInStay = $currentDay->diffInDays($checkIn) + 1;
                                        
                                        // Create formatted booking object
                                        $formattedBooking = new \stdClass();
                                        $formattedBooking->id = $booking->id ?? null;
                                        $formattedBooking->tour_id = $booking->tour_id ?? $tourDetails->tour_id;
                                        $formattedBooking->booking_id = $booking->id . '-' . uniqid();
                                        $formattedBooking->agent_id = $booking->agent_id ?? null;
                                        $formattedBooking->type = $booking->type ?? 'unknown';
                                        
                                        // Create a modified copy of the item data with stay info
                                        $itemCopy = $item;
                                        $itemCopy['day_in_stay'] = $dayInStay;
                                        $itemCopy['total_nights'] = $totalNights;
                                        $itemCopy['stay_type'] = ($dateStr == $checkIn->format('Y-m-d')) ? 'checkin' : 'stay';
                                        
                                        // Set the data_decoded with the modified item
                                        $formattedBooking->data_decoded = [$itemCopy];
                                        $formattedBooking->dmc_company = $booking->dmc_company ?? 'N/A';
                                        
                                        // Set hotel-specific properties
                                        $formattedBooking->hotel_id = $hotelId;
                                        $formattedBooking->hotel_name = $item['hotelDetails']['hotel_name'] ?? '';
                                        $formattedBooking->hotel_location = $item['hotelDetails']['location'] ?? '';
                                        $formattedBooking->price_mode = $item['priceMode'] ?? '';
                                        $formattedBooking->total_price = $item['totalPrice'] ?? '';
                                        
                                        // Extract room info
                                        if (isset($item['rooms']) && is_array($item['rooms'])) {
                                            $roomInfo = [];
                                            foreach ($item['rooms'] as $room) {
                                                $roomType = $room['room_type'] ?? 'Standard';
                                                $beds = [];
                                                
                                                if (isset($room['beds']) && is_array($room['beds'])) {
                                                    foreach ($room['beds'] as $bed) {
                                                        $beds[] = [
                                                            'type' => $bed['bed_type'] ?? '',
                                                            'meal' => isset($bed['selectedMeals']['meal_1']['type']) ? $bed['selectedMeals']['meal_1']['type'] : ''
                                                        ];
                                                    }
                                                }
                                                
                                                $roomInfo[] = [
                                                    'type' => $roomType,
                                                    'beds' => $beds
                                                ];
                                            }
                                            
                                            $formattedBooking->room_info = $roomInfo;
                                        }
                                        
                                        // Add to itineraryByDate
                                        if (!isset($itineraryByDate[$dateStr])) {
                                            $itineraryByDate[$dateStr] = [];
                                        }
                                        
                                        $itineraryByDate[$dateStr][] = $formattedBooking;
                                        
                                        // Move to next day
                                        $currentDay->addDay();
                                    }
                                    
                                    // Add checkout entry for the final day
                                    $dateStr = $checkOut->format('Y-m-d');
                                    
                                    // Create formatted booking object for checkout
                                    $formattedBooking = new \stdClass();
                                    $formattedBooking->id = $booking->id ?? null;
                                    $formattedBooking->tour_id = $booking->tour_id ?? $tourDetails->tour_id;
                                    $formattedBooking->booking_id = $booking->id . '-' . uniqid();
                                    $formattedBooking->agent_id = $booking->agent_id ?? null;
                                    $formattedBooking->type = $booking->type ?? 'unknown';
                                    
                                    // Create a modified copy of the item data with checkout info
                                    $itemCopy = $item;
                                    $itemCopy['total_nights'] = $totalNights;
                                    $itemCopy['stay_type'] = 'checkout';
                                    
                                    // Set the data_decoded with the modified item
                                    $formattedBooking->data_decoded = [$itemCopy];
                                    $formattedBooking->dmc_company = $booking->dmc_company ?? 'N/A';
                                    
                                    // Set hotel-specific properties
                                    $formattedBooking->hotel_id = $hotelId;
                                    $formattedBooking->hotel_name = $item['hotelDetails']['hotel_name'] ?? '';
                                    $formattedBooking->hotel_location = $item['hotelDetails']['location'] ?? '';
                                    $formattedBooking->price_mode = $item['priceMode'] ?? '';
                                    $formattedBooking->total_price = $item['totalPrice'] ?? '';
                                    
                                    // Extract room info
                                    if (isset($item['rooms']) && is_array($item['rooms'])) {
                                        $roomInfo = [];
                                        foreach ($item['rooms'] as $room) {
                                            $roomType = $room['room_type'] ?? 'Standard';
                                            $beds = [];
                                            
                                            if (isset($room['beds']) && is_array($room['beds'])) {
                                                foreach ($room['beds'] as $bed) {
                                                    $beds[] = [
                                                        'type' => $bed['bed_type'] ?? '',
                                                        'meal' => isset($bed['selectedMeals']['meal_1']['type']) ? $bed['selectedMeals']['meal_1']['type'] : ''
                                                    ];
                                                }
                                            }
                                            
                                            $roomInfo[] = [
                                                'type' => $roomType,
                                                'beds' => $beds
                                            ];
                                        }
                                        
                                        $formattedBooking->room_info = $roomInfo;
                                    }
                                    
                                    // Add to itineraryByDate
                                    if (!isset($itineraryByDate[$dateStr])) {
                                        $itineraryByDate[$dateStr] = [];
                                    }
                                    
                                    $itineraryByDate[$dateStr][] = $formattedBooking;
                                } else {
                                    // Process non-hotel bookings normally
                                    // Extract date from various possible fields
                                    $date = null;
                                    
                                    if (isset($item['bookingDate'])) {
                                        if (is_array($item['bookingDate'])) {
                                            $date = $item['bookingDate'][0] ?? null;
                                        } else {
                                            $date = $item['bookingDate'];
                                        }
                                    } elseif (isset($item['pickupdate'])) {
                                        $date = $item['pickupdate'];
                                    } elseif (isset($item['exitpickupdate'])) {
                                        $date = $item['exitpickupdate'];
                                    }
                                    
                                    if ($date) {
                                        $dateStr = \Carbon\Carbon::parse($date)->format('Y-m-d');
                                        
                                        // Create formatted booking object with unique identifier
                                        $formattedBooking = new \stdClass();
                                        $formattedBooking->id = $booking->id ?? null;
                                        $formattedBooking->tour_id = $booking->tour_id ?? $tourDetails->tour_id;
                                        $formattedBooking->booking_id = $booking->id . '-' . uniqid(); // Add unique ID to ensure uniqueness
                                        $formattedBooking->agent_id = $booking->agent_id ?? null;
                                        $formattedBooking->type = $booking->type ?? 'unknown';
                                        // Store the complete item data
                                        $formattedBooking->data_decoded = [$item];
                                        $formattedBooking->dmc_company = $booking->dmc_company ?? 'N/A';
                                        
                                        // Add to itineraryByDate
                                        if (!isset($itineraryByDate[$dateStr])) {
                                            $itineraryByDate[$dateStr] = [];
                                        }
                                        
                                        $itineraryByDate[$dateStr][] = $formattedBooking;
                                    }
                                }
                            }
                        }
                        
                        // Resort dates
                        ksort($itineraryByDate);
                    }
                    
                    // Generate date range from tour details
                    $allDates = [];
                    
                    if (isset($tourDetails->check_in_time) && isset($tourDetails->check_out_time)) {
                        // Use tour start and end dates if available
                        $startDate = \Carbon\Carbon::parse($tourDetails->check_in_time);
                        $endDate = \Carbon\Carbon::parse($tourDetails->check_out_time);
                        
                        // Generate all dates in range
                        $currentDate = $startDate->copy();
                        while ($currentDate->lte($endDate)) {
                            $dateStr = $currentDate->format('Y-m-d');
                            $allDates[$dateStr] = $itineraryByDate[$dateStr] ?? [];
                            $currentDate->addDay();
                        }
                    } elseif (count($itineraryByDate) > 0) {
                        // If no tour dates, use the dates from bookings
                        $dateKeys = array_keys($itineraryByDate);
                        $startDate = \Carbon\Carbon::parse($dateKeys[0]);
                        $endDate = \Carbon\Carbon::parse($dateKeys[count($dateKeys)-1]);
                        
                        // Generate all dates in range
                        $currentDate = $startDate->copy();
                        while ($currentDate->lte($endDate)) {
                            $dateStr = $currentDate->format('Y-m-d');
                            $allDates[$dateStr] = $itineraryByDate[$dateStr] ?? [];
                            $currentDate->addDay();
                        }
                    }
                    
                    $dayCount = 1;
                @endphp
                    
                @if(count($allDates) > 0)
                    @foreach($allDates as $date => $dayBookings)
                        <!-- Date Container -->
                        <div class="date-container drop-zone day-{{ $dayCount > 7 ? (($dayCount - 1) % 7) + 1 : $dayCount }}" data-date="{{ $date }}">
                            <div class="timeline-line"></div>
                            
                            <!-- Day Indicator -->
                            <div class="day-indicator">
                                <div class="day-circle day-{{ $dayCount > 7 ? (($dayCount - 1) % 7) + 1 : $dayCount }}">
                                    Day {{ $dayCount }}
                                </div>
                                <div class="day-info">
                                    <div>
                                        <h3 class="day-title">{{ \Carbon\Carbon::parse($date)->format('l') }}, {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</h3>
                                    </div>
                                    <span class="day-chevron">▼</span>
                                </div>
                            </div>
                            
                            <!-- Services List -->
                            <div class="services-list">
                            
                            @php
                                // Sort bookings for this day with proper ordering
                                $entryPorts = [];
                                $exitPorts = [];
                                $hotels = [];
                                $regularBookings = [];
                                $usedBookingIds = []; // Track already processed bookings to avoid duplicates
                                
                                foreach($dayBookings as $booking) {
                                    // Skip already processed bookings (avoid duplicates)
                                    if (in_array($booking->id, $usedBookingIds)) {
                                        continue;
                                    }
                                    $usedBookingIds[] = $booking->id;
                                    
                                    // Get data from booking - handle different structures safely
                                    $data = null;
                                    if (isset($booking->data_decoded) && is_array($booking->data_decoded) && !empty($booking->data_decoded)) {
                                        $data = $booking->data_decoded[0] ?? null;
                                    } elseif (isset($booking->data) && is_string($booking->data)) {
                                        try {
                                            $data = json_decode($booking->data, true);
                                        } catch (\Exception $e) {
                                            // JSON parsing failed, try to use as is
                                            $data = $booking->data;
                                        }
                                    } elseif (isset($booking->data) && is_array($booking->data)) {
                                        $data = $booking->data;
                                    }
                                    
                                    if (!$data || !is_array($data)) {
                                        $data = []; // Ensure we have at least an empty array
                                    }
                                    
                                    // Skip hotel checkouts - we don't want to show them in the itinerary
                                    if (strtolower($booking->type ?? '') == 'hotel' && 
                                        isset($data['stay_type']) && 
                                        (strtolower($data['stay_type']) == 'checkout' || strtolower($data['stay_type']) == 'check-out')) {
                                        continue;
                                    }
                                    
                                    // Determine time for sorting
                                    $timeSlot = null;
                                    
                                    if (isset($data['timeslot'])) {
                                        $timeSlot = $data['timeslot'];
                                    } elseif (isset($data['time'])) {
                                        $timeSlot = $data['time'];
                                    } elseif (isset($data['pickuptime'])) {
                                        $timeSlot = $data['pickuptime'];
                                    } elseif (isset($data['exitpickuptime'])) {
                                        $timeSlot = $data['exitpickuptime'];
                                    }
                                    
                                    // Default sorting for items without time
                                    $sortTime = $timeSlot ? $timeSlot : '12:00';
                                    
                                    // Simple conversion for time sorting
                                    if (strpos($sortTime, 'AM') !== false || strpos($sortTime, 'PM') !== false) {
                                        // Convert 12-hour format to 24-hour for sorting
                                        $timeParts = explode(' ', str_replace(['AM', 'PM'], '', $sortTime));
                                        $hourMin = explode(':', trim($timeParts[0]));
                                        $hour = (int)$hourMin[0];
                                        $min = isset($hourMin[1]) ? (int)$hourMin[1] : 0;
                                        
                                        if (strpos($sortTime, 'PM') !== false && $hour < 12) {
                                            $hour += 12;
                                        }
                                        if (strpos($sortTime, 'AM') !== false && $hour == 12) {
                                            $hour = 0;
                                        }
                                        
                                        $sortTime = sprintf('%02d:%02d', $hour, $min);
                                    }
                                    
                                    $bookingData = [
                                        'booking' => $booking,
                                        'data' => $data,
                                        'display_time' => $timeSlot,
                                        'sort_time' => $sortTime
                                    ];
                                    
                                    // Categorize booking based on priority order
                                    if (strtolower($booking->type) == 'entry port') {
                                        $entryPorts[] = $bookingData;
                                    } elseif (strtolower($booking->type) == 'exit port') {
                                        $exitPorts[] = $bookingData;
                                    } elseif (strtolower($booking->type) == 'hotel') {
                                        $hotels[] = $bookingData;
                                    } else {
                                        $regularBookings[] = $bookingData;
                                    }
                                }
                                
                                // Sort each category by time
                                usort($entryPorts, function($a, $b) {
                                    return $a['sort_time'] <=> $b['sort_time'];
                                });
                                
                                usort($hotels, function($a, $b) {
                                    return $a['sort_time'] <=> $b['sort_time'];
                                });
                                
                                usort($regularBookings, function($a, $b) {
                                    return $a['sort_time'] <=> $b['sort_time'];
                                });
                                
                                usort($exitPorts, function($a, $b) {
                                    return $a['sort_time'] <=> $b['sort_time'];
                                });
                                
                                // Special handling for entry/exit ports based on day
                                $isFirstDay = $dayCount == 1;
                                $isLastDay = $dayCount == count($allDates);
                                
                                // Combine all bookings in the right priority order:
                                // 1. Entry Port (first day only) 
                                // 2. Hotels (always on top)
                                // 3. Regular services (sorted by time)
                                // 4. Exit Port (last day only)
                                $sortedBookings = [];
                                
                                // Add entry ports only on first day (arrival)
                                if ($isFirstDay && count($entryPorts) > 0) {
                                    $sortedBookings = array_merge($sortedBookings, $entryPorts);
                                }
                                
                                // Add hotels (always on top after arrival)
                                $sortedBookings = array_merge($sortedBookings, $hotels);
                                
                                // Add regular services (sorted by time)
                                $sortedBookings = array_merge($sortedBookings, $regularBookings);
                                
                                // Add exit ports only on last day (departure)
                                if ($isLastDay && count($exitPorts) > 0) {
                                    $sortedBookings = array_merge($sortedBookings, $exitPorts);
                                }
                            @endphp
                            
                            @if(count($sortedBookings) > 0)
                                @foreach($sortedBookings as $bookingData)
                                    @php
                                        // Get booking object and data safely
                                        $booking = $bookingData['booking'] ?? null;
                                        $data = $bookingData['data'] ?? [];
                                        $timeSlot = $bookingData['display_time'] ?? null;
                                        
                                        if (!$booking) {
                                            continue; // Skip if booking object is missing
                                        }
                                        
                                        // Extract actual data from the booking
                                        $serviceType = $booking->type ?? 'Service';
                                        $serviceType = ucfirst(strtolower($serviceType));
                                        $serviceName = '';
                                        $pax = 3; // Default to 3 passengers
                                        
                                        // Change Entry Port to Arrival and Exit Port to Departure
                                        if (strtolower($serviceType) == 'entry_port') {
                                            $serviceType = 'Arrival';
                                        } elseif (strtolower($serviceType) == 'exit_port') {
                                            $serviceType = 'Departure';
                                        }
                                        
                                        // Get service name based on booking type
                                        if (strtolower($serviceType) == 'hotel') {
                                            // For hotels
                                            if (!empty($data['hotelname'])) {
                                                $serviceName = $data['hotelname'];
                                            } elseif (!empty($data['hotelDetails']['hotel_name'])) {
                                                $serviceName = $data['hotelDetails']['hotel_name'];
                                            } elseif (!empty($data['name'])) {
                                                $serviceName = $data['name'];
                                            } else {
                                                $serviceName = 'Hotel';
                                            }
                                        } elseif (strtolower($serviceType) == 'guide') {
                                            // For guides
                                            if (!empty($data['guidename'])) {
                                                $serviceName = $data['guidename'];
                                            } elseif (!empty($data['guide_name'])) {
                                                $serviceName = $data['guide_name'];
                                            } elseif (!empty($data['name'])) {
                                                $serviceName = $data['name'];
                                            } else {
                                                $serviceName = 'Jane Smith'; // Example guide name
                                            }
                                        } elseif (strpos(strtolower($serviceType), 'travel') !== false) {
                                            // For travel services
                                            if (!empty($data['vehicle'])) {
                                                $serviceName = $data['vehicle'];
                                            } elseif (!empty($data['name'])) {
                                                $serviceName = $data['name'];
                                            } else {
                                                $serviceName = 'Transfer';
                                            }
                                            // Convert travel_point or travel_hourly to proper format
                                            if (strpos(strtolower($serviceType), 'point') !== false) {
                                                $serviceType = 'Transfer';
                                            } elseif (strpos(strtolower($serviceType), 'hourly') !== false) {
                                                $serviceType = 'Hourly Transfer';
                                            }
                                        } elseif (strpos(strtolower($serviceType), 'port') !== false || strtolower($serviceType) == 'arrival' || strtolower($serviceType) == 'departure') {
                                            // For ports/arrival/departure
                                            if (!empty($data['name'])) {
                                                $serviceName = $data['name'];
                                            } else {
                                                $serviceName = 'Transfer Service'; // Default for ports
                                            }
                                        } elseif (strtolower($serviceType) == 'attraction') {
                                            // For attractions
                                            if (!empty($data['AttractionName'])) {
                                                $serviceName = $data['AttractionName'];
                                            } elseif (!empty($data['name'])) {
                                                $serviceName = $data['name'];
                                            } else {
                                                $serviceName = 'Attraction';
                                            }
                                            
                                            // Check if attraction has transfer - if so, show transfer service name
                                            if (isset($data['transfer_options']) && 
                                                isset($data['transfer_options']['transfer_required']) && 
                                                $data['transfer_options']['transfer_required'] === true) {
                                                // For transfers, show the activity name as service
                                                $serviceName = $data['AttractionName'] ?? $data['name'] ?? 'Attraction';
                                            }
                                        } elseif (strtolower($serviceType) == 'restaurant') {
                                            // For restaurants
                                            if (!empty($data['restaurantName'])) {
                                                $serviceName = $data['restaurantName'];
                                            } elseif (!empty($data['name'])) {
                                                $serviceName = $data['name'];
                                            } else {
                                                $serviceName = 'Restaurant';
                                            }
                                            
                                            // Check if restaurant has transfer - if so, show transfer service name
                                            if (isset($data['transfer_options']) && 
                                                isset($data['transfer_options']['transfer_required']) && 
                                                $data['transfer_options']['transfer_required'] === true) {
                                                // For transfers, show the restaurant name as service
                                                $serviceName = $data['restaurantName'] ?? $data['name'] ?? 'Restaurant';
                                            }
                                        } else {
                                            // Default fallback
                                            $serviceName = !empty($data['name']) ? $data['name'] : ucfirst(strtolower($serviceType));
                                        }
                                        
                                        // Get passenger count with a default value
                                        if (!empty($data['pax'])) {
                                            $pax = $data['pax'];
                                        } elseif (!empty($data['passengers'])) {
                                            $pax = $data['passengers'];
                                        } elseif (!empty($data['adult']) || !empty($data['child']) || !empty($data['infant'])) {
                                            $pax = ($data['adult'] ?? 0) + ($data['child'] ?? 0) + ($data['infant'] ?? 0);
                                        } elseif (isset($tourDetails->adult)) {
                                            // Fallback to tour details
                                            $pax = ($tourDetails->adult ?? 0) + ($tourDetails->child ?? 0) + ($tourDetails->infant ?? 0);
                                        }
                                        // Ensure pax is at least 1
                                        $pax = max(1, $pax);
                                        
                                        // Format date
                                        $serviceDate = \Carbon\Carbon::parse($date)->format('l, F j, Y');
                                        
                                        // Time processing removed as time badge is no longer displayed
                                        
                                        // Determine service type class for styling
                                        $serviceTypeClass = '';
                                        if (strtolower($serviceType) == 'hotel') {
                                            $serviceTypeClass = 'service-type-hotel';
                                        } elseif (strtolower($serviceType) == 'guide') {
                                            $serviceTypeClass = 'service-type-guide';
                                        } elseif (strpos(strtolower($serviceType), 'transfer') !== false) {
                                            $serviceTypeClass = 'service-type-transfer';
                                        } elseif (strpos(strtolower($serviceType), 'entry port') !== false) {
                                            $serviceTypeClass = 'service-type-entry';
                                        } elseif (strpos(strtolower($serviceType), 'exit port') !== false) {
                                            $serviceTypeClass = 'service-type-exit';
                                        } elseif (strtolower($serviceType) == 'attraction') {
                                            $serviceTypeClass = 'service-type-attraction';
                                        } elseif (strtolower($serviceType) == 'restaurant') {
                                            $serviceTypeClass = 'service-type-restaurant';
                                        }
                                        
                                        // Determine if item is draggable and add proper CSS classes
                                        $nonDraggableTypes = ['hotel', 'entry port', 'exit port', 'entry_port', 'exit_port', 'arrival', 'departure'];
                                        $isDraggable = !in_array(strtolower($serviceType), $nonDraggableTypes);
                                        $draggableClass = $isDraggable ? 'draggable' : 'non-draggable locked';
                                        $draggableAttrs = $isDraggable ? 'draggable="true"' : '';
                                        
                                        // Add service type class for styling
                                        $serviceStyleClass = '';
                                        if (strtolower($serviceType) == 'hotel') {
                                            $serviceStyleClass = 'hotel';
                                        } elseif (strpos(strtolower($serviceType), 'entry') !== false || strtolower($serviceType) == 'arrival') {
                                            $serviceStyleClass = 'entry-port';
                                        } elseif (strpos(strtolower($serviceType), 'exit') !== false || strtolower($serviceType) == 'departure') {
                                            $serviceStyleClass = 'exit-port';
                                        }
                                        
                                        // Create unique identifier for this service item
                                        $itemId = 'service-' . ($booking->id ?? '') . '-' . uniqid();
                                        
                                        // Check if attraction/restaurant has transfer - if so, mark as transfer type for icon
                                        $displayServiceType = strtolower($serviceType);
                                        if ((strtolower($serviceType) == 'attraction' || strtolower($serviceType) == 'restaurant') && 
                                            isset($data['transfer_options']) && 
                                            isset($data['transfer_options']['transfer_required']) && 
                                            $data['transfer_options']['transfer_required'] === true) {
                                            $displayServiceType = 'transfer';
                                        }
                                    @endphp
                                    
                                    <div class="drop-zone-indicator"></div>
                                    <div class="service-item {{ $draggableClass }} {{ $serviceStyleClass }}" 
                                         id="{{ $itemId }}"
                                         {!! $draggableAttrs !!}
                                         data-booking-id="{{ $booking->booking_id ?? '' }}"
                                         data-service-type="{{ $displayServiceType }}"
                                         data-current-date="{{ $date }}"
                                         data-booking-data="{{ base64_encode(json_encode($data)) }}">
                                        @if($isDraggable)
                                            <div class="drag-indicator">⋮⋮</div>
                                        @endif
                                        
                                        <div class="service-item-content">
                                            <!-- Main Content -->
                                            <div class="service-main-content">
                                                @php
                                                    // Determine service type label
                                                    $serviceTypeLabel = '';
                                                    if (strtolower($serviceType) == 'hotel') {
                                                        $serviceTypeLabel = 'Hotel';
                                                    } elseif (strpos(strtolower($serviceType), 'entry') !== false || strtolower($serviceType) == 'arrival') {
                                                        $serviceTypeLabel = 'Arrival';
                                                    } elseif (strpos(strtolower($serviceType), 'exit') !== false || strtolower($serviceType) == 'departure') {
                                                        $serviceTypeLabel = 'Departure';
                                                    } elseif (strpos(strtolower($serviceType), 'transfer') !== false || strpos(strtolower($serviceType), 'travel') !== false) {
                                                        $serviceTypeLabel = 'Local Transfer';
                                                    } elseif (strtolower($serviceType) == 'attraction') {
                                                        $serviceTypeLabel = 'Attraction';
                                                    } elseif (strtolower($serviceType) == 'restaurant') {
                                                        $serviceTypeLabel = 'Restaurant';
                                                    } elseif (strtolower($serviceType) == 'guide') {
                                                        $serviceTypeLabel = 'Guide';
                                                    } else {
                                                        $serviceTypeLabel = ucfirst($serviceType);
                                                    }
                                                @endphp
                                                
                                                <div class="service-type-heading">{{ $serviceTypeLabel }}</div>
                                                
                                                <div class="service-header">
                                                    @php
                                                        // Extract time for display
                                                        $displayTime = null;
                                                        if (isset($timeSlot) && !empty($timeSlot)) {
                                                            $displayTime = $timeSlot;
                                                        } elseif (isset($data['entrytime'])) {
                                                            $displayTime = $data['entrytime'];
                                                        } elseif (isset($data['visitTime'])) {
                                                            $displayTime = $data['visitTime'];
                                                        } elseif (isset($data['time'])) {
                                                            $displayTime = $data['time'];
                                                        }
                                                        
                                                        // Format time for display
                                                        if ($displayTime) {
                                                            // Convert to 12-hour format if needed
                                                            if (preg_match('/^(\d{1,2}):(\d{2})$/', $displayTime, $matches)) {
                                                                $hour = (int)$matches[1];
                                                                $min = $matches[2];
                                                                $ampm = $hour >= 12 ? 'PM' : 'AM';
                                                                $hour12 = $hour > 12 ? $hour - 12 : ($hour == 0 ? 12 : $hour);
                                                                $displayTime = sprintf('%d:%s %s', $hour12, $min, $ampm);
                                                            }
                                                        }
                                                    @endphp
                                                    
                                                    @if($displayTime)
                                                        <span class="service-time-display">{{ $displayTime }}</span>
                                                    @endif
                                                    
                                                    <h4 class="service-title">{{ $serviceName }}</h4>
                                                    @if(strtolower($serviceType) == 'hotel')
                                                        @php
                                                            $hotelLocation = $data['hotelDetails']['location'] ?? $data['location'] ?? '';
                                                            $checkInTime = $data['hotelDetails']['checkInTime'] ?? null;
                                                            $checkOutTime = $data['hotelDetails']['checkOutTime'] ?? null;
                                                            $confirmationNo = $data['confirmationNo'] ?? $data['confirmation_no'] ?? null;
                                                        @endphp
                                                        
                                                        @if(isset($data['day_in_stay']) && isset($data['total_nights']))
                                                            @if(isset($data['stay_type']) && $data['stay_type'] == 'checkin')
                                                                <p class="service-description">
                                                                    <span class="service-detail-label">Service:</span> {{ $serviceName }}
                                                                </p>
                                                                @if(!empty($hotelLocation))
                                                                    <p class="service-detail-line">
                                                                        <span class="service-detail-label">Location:</span> {{ $hotelLocation }}
                                                                    </p>
                                                                @endif
                                                                <p class="service-detail-line">
                                                                    <span class="service-detail-label">Duration:</span> {{ $data['total_nights'] }} {{ $data['total_nights'] > 1 ? 'Nights' : 'Night' }} (Checkout: {{ \Carbon\Carbon::parse($date)->addDays($data['total_nights'])->format('d M Y') }})
                                                                </p>
                                                                @if(!empty($confirmationNo))
                                                                    <p class="service-detail-line">
                                                                        <span class="service-detail-label">Confirmation No:</span> {{ $confirmationNo }}
                                                                    </p>
                                                                @endif
                                                            @else
                                                                <p class="service-description">
                                                                    Day {{ $data['day_in_stay'] }} of {{ $data['total_nights'] }} • {{ $serviceName }}
                                                                </p>
                                                            @endif
                                                        @else
                                                            <p class="service-description">Hotel accommodation</p>
                                                        @endif
                                                        
                                                        <!-- Compact Hotel Details - 2 lines -->
                                                        <div class="hotel-details-compact">
                                                            @php
                                                                $checkInTime = $data['hotelDetails']['checkInTime'] ?? null;
                                                                $checkOutTime = $data['hotelDetails']['checkOutTime'] ?? null;
                                                                $roomCount = isset($data['rooms']) && is_array($data['rooms']) ? count($data['rooms']) : 0;
                                                                $firstRoom = isset($data['rooms'][0]) ? $data['rooms'][0] : null;
                                                                $firstBed = isset($firstRoom['beds'][0]) ? $firstRoom['beds'][0] : null;
                                                                $meals = isset($firstBed['selectedMeals']) && is_array($firstBed['selectedMeals']) ? $firstBed['selectedMeals'] : [];
                                                            @endphp
                                                            
                                                            <!-- Line 1: Check-in/out + Room count -->
                                                            @if($checkInTime || $checkOutTime || $roomCount > 0)
                                                                <p class="service-detail-line compact-line">
                                                                    @if($checkInTime || $checkOutTime)
                                                                        <i class="fas fa-clock me-1"></i>
                                                                        Check-in: {{ $checkInTime ?? 'N/A' }} | Check-out: {{ $checkOutTime ?? 'N/A' }}
                                                                        @if($roomCount > 0)
                                                                            <span class="mx-1">•</span>
                                                                        @endif
                                                                    @endif
                                                                    @if($roomCount > 0)
                                                                        <i class="fas fa-bed me-1"></i>
                                                                        {{ $roomCount }} {{ $roomCount > 1 ? 'Rooms' : 'Room' }}
                                                                    @endif
                                                                </p>
                                                            @endif
                                                            
                                                            <!-- Line 2: Room details + bed + meals -->
                                                            @if($firstRoom)
                                                                <p class="service-detail-line compact-line">
                                                                    <strong>Room 1:</strong> {{ $firstRoom['room_type'] ?? 'Standard Room' }}
                                                                    @if($firstBed)
                                                                        <span class="mx-1">•</span>
                                                                        {{ $firstBed['bed_type'] ?? 'Standard Bed' }}
                                                                        @if(!empty($firstBed['head_count']))
                                                                            ({{ $firstBed['head_count'] }} pax)
                                                                        @endif
                                                                    @endif
                                                                    @if(count($meals) > 0)
                                                                        @foreach($meals as $meal)
                                                                            <span class="badge meal-badge">
                                                                                <i class="fas fa-utensils me-1"></i>{{ $meal['type'] ?? 'Meal' }}
                                                                            </span>
                                                                        @endforeach
                                                                    @endif
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @elseif(strtolower($serviceType) == 'guide')
                                                        <p class="service-description">Professional tour guide service</p>
                                                        
                                                        <!-- Compact Guide Details - 2 lines -->
                                                        <div class="guide-details-compact">
                                                            @php 
                                                                $guideData = $data;
                                                                $bookingDate = $guideData['bookingDate'] ?? $guideData['booking_date'] ?? $guideData['pickupdate'] ?? null;
                                                                $hours = $guideData['hours'] ?? null;
                                                                $guideName = $guideData['guide_name'] ?? $guideData['guideName'] ?? null;
                                                                $experience = $guideData['experience'] ?? null;
                                                                $languages = $guideData['languages'] ?? null;
                                                                $firstLanguage = isset($languages) && is_array($languages) && count($languages) > 0 ? $languages[0] : null;
                                                            @endphp
                                                            
                                                            <!-- Line 1: Date + hours + Guide name -->
                                                            @if($bookingDate || $guideName)
                                                                <p class="service-detail-line compact-line">
                                                                    @if($bookingDate)
                                                                        <i class="fas fa-calendar-alt me-1"></i>
                                                                        {{ date('d M Y', strtotime($bookingDate)) }}
                                                                        @if($hours)
                                                                            • {{ $hours }} hour{{ $hours > 1 ? 's' : '' }}
                                                                        @endif
                                                                        @if($guideName)
                                                                            <span class="mx-1">•</span>
                                                                        @endif
                                                                    @endif
                                                                    @if($guideName)
                                                                        <i class="fas fa-user-tie me-1"></i>
                                                                        <strong>{{ $guideName }}</strong>
                                                                        @if($firstLanguage)
                                                                            - {{ $firstLanguage['language'] ?? 'Language' }}
                                                                        @endif
                                                                    @endif
                                                                </p>
                                                            @endif
                                                            
                                                            <!-- Line 2: Guide Information + languages -->
                                                            @if($guideName || $experience || (isset($languages) && is_array($languages) && count($languages) > 0))
                                                                <p class="service-detail-line compact-line">
                                                                    @if($experience)
                                                                        <span class="experience-badge">{{ $experience }} years exp.</span>
                                                                    @endif
                                                                    @if(isset($languages) && is_array($languages) && count($languages) > 0)
                                                                        @foreach($languages as $lang)
                                                                            <span class="badge language-badge">
                                                                                {{ $lang['language'] ?? 'Language' }}
                                                                            </span>
                                                                        @endforeach
                                                                    @endif
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @elseif(strpos(strtolower($serviceType), 'transport') !== false || strpos(strtolower($serviceType), 'travel') !== false || strpos(strtolower($serviceType), 'transfer') !== false)
                                                        @php 
                                                            // $data is already the transfer item data
                                                            $transferData = $data;
                                                            $entryPickup = $transferData['entrypickup'] ?? $transferData['entry_pickup'] ?? $transferData['pickup'] ?? null;
                                                            $dropoffLocation = $transferData['entrydropoff'] ?? $transferData['dropoffLocation'] ?? $transferData['dropoff_location'] ?? $transferData['dropoff'] ?? null;
                                                            $transferType = $transferData['type'] ?? 'Shared';
                                                            $transferWay = $transferData['way'] ?? null;
                                                            $remark = $transferData['remark'] ?? $transferData['remarks'] ?? $transferData['specialRequests'] ?? null;
                                                            $vehicle = $transferData['vehicle'] ?? $transferData['vehicles_name'] ?? null;
                                                            $travel_type = $transferData['travel_type'] ?? null;
                                                            $selectedHours = $transferData['selectedHours'] ?? null;
                                                            // Determine transfer type display
                                                            $transferTypeDisplay = '';
                                                            if (strtolower($transferType) == 'shared') {
                                                                $transferTypeDisplay = 'Shared Transfer';
                                                            } elseif (strtolower($transferType) == 'private') {
                                                                $transferTypeDisplay = 'Private Transfer';
                                                            } else {
                                                                $transferTypeDisplay = ucfirst($transferType) . ' Transfer';
                                                            }
                                                            
                                                            // Build service name with transfer type
                                                            $fullServiceName = $serviceName;
                                                            if ($transferTypeDisplay) {
                                                                $fullServiceName = $serviceName . ' (' . $transferTypeDisplay . ')';
                                                            }
                                                        @endphp
                                                        
                                                        <p class="service-description">
                                                            <span class="service-detail-label">Service:</span> {{ $fullServiceName }}
                                                        </p>
                                                        
                                                        @if(!empty($entryPickup) || !empty($dropoffLocation))
                                                            <p class="service-detail-line">
                                                                @if(!empty($entryPickup))
                                                                    <span class="service-detail-label">From:</span> {{ $entryPickup }}
                                                                @endif
                                                                @if(!empty($entryPickup) && !empty($dropoffLocation))
                                                                    <span class="mx-1">•</span>
                                                                @endif
                                                                @if(!empty($dropoffLocation))
                                                                    <span class="service-detail-label">To:</span> {{ $dropoffLocation }}
                                                                @endif
                                                                @if(!empty($vehicle))
                                                                    <span class="mx-1">•</span>
                                                                    <span class="service-detail-label">Vehicle:</span> {{ $vehicle }}
                                                                @endif
                                                                @if(!empty($travel_type) && $travel_type == 'travel_hourly')
                                                                    <span class="mx-1">•</span>
                                                                    <span class="service-detail-label">Travel Type:</span> Hourly
                                                                    <span class="mx-1">•</span>
                                                                    <span class="service-detail-label">Selected Hours:</span> {{ $selectedHours }} Hour(s)
                                                                @endif
                                                            </p>
                                                        @endif
                                                        
                                                        @if(!empty($remark))
                                                            <div class="service-remark">
                                                                <span class="service-detail-label">Remark:</span> {{ $remark }}
                                                            </div>
                                                        @endif
                                                    @elseif(strpos(strtolower($serviceType), 'entry') !== false || strtolower($serviceType) == 'arrival')
                                                        @php 
                                                            $entryPortData = $data;
                                                            $entryPickup = $entryPortData['entrypickup'] ?? $entryPortData['entry_pickup'] ?? $entryPortData['pickup'] ?? null;
                                                            $entryDropoff = $entryPortData['entrydropoff'] ?? $entryPortData['entry_dropoff'] ?? $entryPortData['dropoff'] ?? null;
                                                            $remark = $entryPortData['remark'] ?? $entryPortData['remarks'] ?? $entryPortData['specialRequests'] ?? null;
                                                            $transferType = 'Shared';
                                                            if (isset($entryPortData['transfer_options']['type'])) {
                                                                $transferType = $entryPortData['transfer_options']['type'];
                                                            }
                                                            $transferTypeDisplay = ucfirst($transferType) . ' Transfer';
                                                        @endphp
                                                        
                                                        <p class="service-description">
                                                            <span class="service-detail-label">Service:</span> {{ $serviceName }} ({{ $transferTypeDisplay }})
                                                        </p>
                                                        
                                                        @if(!empty($entryPickup) || !empty($entryDropoff))
                                                            <p class="service-detail-line">
                                                                @if(!empty($entryPickup))
                                                                    <span class="service-detail-label">From:</span> {{ $entryPickup }}
                                                                @endif
                                                                @if(!empty($entryPickup) && !empty($entryDropoff))
                                                                    <span class="mx-1">•</span>
                                                                @endif
                                                                @if(!empty($entryDropoff))
                                                                    <span class="service-detail-label">To:</span> {{ $entryDropoff }}
                                                                @endif
                                                            </p>
                                                        @endif
                                                        
                                                        @if(!empty($remark))
                                                            <div class="service-remark">
                                                                <span class="service-detail-label">Remark:</span> {{ $remark }}
                                                            </div>
                                                        @endif
                                                    @elseif(strpos(strtolower($serviceType), 'exit') !== false || strtolower($serviceType) == 'departure')
                                                        @php 
                                                            $exitPortData = $data;
                                                            $entryPickup = $exitPortData['exitpickup'] ?? $exitPortData['entry_pickup'] ?? $exitPortData['pickup'] ?? null;
                                                            $entryDropoff = $exitPortData['exitdropoff'] ?? $exitPortData['entry_dropoff'] ?? $exitPortData['dropoff'] ?? null;
                                                            $remark = $exitPortData['remark'] ?? $exitPortData['remarks'] ?? $exitPortData['specialRequests'] ?? null;
                                                            $transferType = $exitPortData['type'] ?? null;
                                                            $vehicle = $exitPortData['vehicles_name'] ?? null;

                                                            $transferTypeDisplay = ucfirst($transferType) . ' Transfer';
                                                        @endphp
                                                        
                                                        <p class="service-description">
                                                            <span class="service-detail-label">Service:</span> {{ $serviceName }} ({{ $transferTypeDisplay }})
                                                        </p>
                                                        
                                                        @if(!empty($entryPickup) || !empty($entryDropoff))
                                                            <p class="service-detail-line">
                                                                @if(!empty($entryPickup))
                                                                    <span class="service-detail-label">From:</span> {{ $entryPickup }}
                                                                @endif
                                                                @if(!empty($entryPickup) && !empty($entryDropoff))
                                                                    <span class="mx-1"></span>
                                                                @endif
                                                                @if(!empty($entryDropoff))
                                                                    <span class="service-detail-label">To:</span> {{ $entryDropoff }}
                                                                @endif
                                                                @if(!empty($vehicle))
                                                                    <span class="mx-1">•</span>
                                                                    <span class="service-detail-label">Vehicle:</span> {{ $vehicle }}
                                                                @endif
                                                                @if(!empty($transferType))
                                                                    <span class="mx-1">•</span>
                                                                    <span class="service-detail-label">Travel Type:</span> {{ $transferType }}
                                                                @endif
                                                            </p>
                                                        @endif
                                                        
                                                        @if(!empty($remark))
                                                            <div class="service-remark">
                                                                <span class="service-detail-label">Remark:</span> {{ $remark }}
                                                            </div>
                                                        @endif
                                                    @elseif(strtolower($serviceType) == 'attraction')
                                                        @php
                                                            // Check if attraction has transfer
                                                            $hasTransfer = isset($data['transfer_options']) && 
                                                                          isset($data['transfer_options']['transfer_required']) && 
                                                                          $data['transfer_options']['transfer_required'] === true;
                                                            
                                                            if ($hasTransfer) {
                                                                $transferOptions = $data['transfer_options'];
                                                                $pickupLocation = $transferOptions['pickup_location_name'] ?? '';
                                                                $transferType = $transferOptions['type'] ?? 'Shared';
                                                                $transferTypeDisplay = ucfirst($transferType) . ' Transfer';
                                                                $remark = $data['specialRequests'] ?? null;
                                                            }
                                                        @endphp
                                                        
                                                        @if($hasTransfer)
                                                            <p class="service-description">
                                                                <span class="service-detail-label">Service:</span> {{ $serviceName }} ({{ $transferTypeDisplay }})
                                                            </p>
                                                            
                                                            <p class="service-detail-line">
                                                                @if(!empty($pickupLocation))
                                                                    <span class="service-detail-label">From:</span> {{ $pickupLocation }}
                                                                    <span class="mx-1">•</span>
                                                                @endif
                                                                <span class="service-detail-label">To:</span> {{ $serviceName }}
                                                            </p>
                                                            
                                                            @if(!empty($remark))
                                                                <div class="service-remark">
                                                                    <span class="service-detail-label">Remark:</span> {{ $remark }}
                                                                </div>
                                                            @endif
                                                        @else
                                                            <p class="service-description">Sightseeing and attraction visit</p>
                                                            
                                                            <!-- Enhanced Attraction Details -->
                                                            <div class="attraction-details mt-2">
                                                            @php 
                                                                // $data is already the attraction item data (not an array of items)
                                                                $attractionData = $data;
                                                            @endphp
                                                            
                                                            @if($attractionData && is_array($attractionData))
                                                                
                                                                <div class="attraction-info-grid">
                                                                    @php
                                                                        // Check for different possible field names
                                                                        $bookingDate = $attractionData['bookingDate'] ?? $attractionData['booking_date'] ?? $attractionData['date'] ?? null;
                                                                        $visitTime = $attractionData['visitTime'] ?? $attractionData['visit_time'] ?? $attractionData['time'] ?? null;
                                                                        $totalPrice = $attractionData['totalPrice'] ?? $attractionData['total_price'] ?? $attractionData['price'] ?? null;
                                                                        $mode = $attractionData['mode'] ?? $attractionData['price_mode'] ?? null;
                                                                    @endphp
                                                                    
                                                                    @if(!empty($bookingDate) || !empty($visitTime))
                                                                        <div class="attraction-info-item">
                                                                            <small class="text-muted">
                                                                                <i class="fas fa-calendar-alt me-1"></i>
                                                                                @if(!empty($bookingDate))
                                                                                    {{ date('d M Y', strtotime($bookingDate)) }}
                                                                                @endif
                                                                                @if(!empty($visitTime))
                                                                                    • {{ $visitTime }}
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                    @endif
                                                                    
                                                                    @if(!empty($totalPrice) && $priceHide == 0)
                                                                        <div class="attraction-info-item">
                                                                            <small class="text-success fw-bold">
                                                                                <i class="fas fa-dollar-sign me-1"></i>
                                                                                SGD {{ number_format($totalPrice, 2) }}
                                                                                @if(!empty($mode))
                                                                                    <span class="price-mode-badge">{{ ucfirst($mode) }}</span>
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                
                                                                <!-- Ticket Information -->
                                                                @php
                                                                    $ticketName = $attractionData['ticketName'] ?? $attractionData['ticket_name'] ?? null;
                                                                    $ticketId = $attractionData['ticketId'] ?? $attractionData['ticket_id'] ?? null;
                                                                    $ticketDetails = $attractionData['ticket_details'] ?? null;
                                                                    $adultCount = $attractionData['adultCount'] ?? $attractionData['adult_count'] ?? null;
                                                                    $childCount = $attractionData['childCount'] ?? $attractionData['child_count'] ?? null;
                                                                    $seniorCount = $attractionData['seniorCount'] ?? $attractionData['senior_count'] ?? null;
                                                                    $selection = $attractionData['Selection'] ?? $attractionData['selection'] ?? $attractionData['transport'] ?? null;
                                                                @endphp
                                                                
                                                                @if(!empty($ticketName) || !empty($ticketDetails) || !empty($adultCount) || !empty($childCount) || !empty($seniorCount))
                                                                    <div class="ticket-summary mt-2">
                                                                        <div class="ticket-header">
                                                                            <small class="text-primary fw-semibold">
                                                                                <i class="fas fa-ticket-alt me-1"></i>
                                                                                Ticket Details
                                                                            </small>
                                                                        </div>
                                                                        
                                                                        <div class="ticket-item mt-1">
                                                                            @if(!empty($ticketName))
                                                                                <div class="ticket-basic-info">
                                                                                    <small class="text-dark">
                                                                                        <strong>{{ $ticketName }}</strong>
                                                                                        @if(!empty($ticketId))
                                                                                            <span class="ticket-id-badge">#{{ $ticketId }}</span>
                                                                                        @endif
                                                                                    </small>
                                                                                </div>
                                                                            @endif
                                                                            
                                                                            <!-- Visitor Count -->
                                                                            @if(!empty($adultCount) || !empty($childCount) || !empty($seniorCount))
                                                                                <div class="visitor-info mt-1">
                                                                                    <small class="text-muted">
                                                                                        <i class="fas fa-users me-1"></i>
                                                                                        @if(!empty($adultCount))
                                                                                            {{ $adultCount }} Adult{{ $adultCount > 1 ? 's' : '' }}
                                                                                        @endif
                                                                                        @if(!empty($childCount))
                                                                                            @if(!empty($adultCount)) • @endif
                                                                                            {{ $childCount }} Child{{ $childCount > 1 ? 'ren' : '' }}
                                                                                        @endif
                                                                                        @if(!empty($seniorCount))
                                                                                            @if(!empty($adultCount) || !empty($childCount)) • @endif
                                                                                            {{ $seniorCount }} Senior{{ $seniorCount > 1 ? 's' : '' }}
                                                                                        @endif
                                                                                    </small>
                                                                                </div>
                                                                            @endif
                                                                            
                                                                            <!-- Ticket Pricing -->
                                                                            @if(isset($ticketDetails) && is_array($ticketDetails) && $priceHide == 0)
                                                                                <div class="pricing-info mt-1">
                                                                                    @if(!empty($ticketDetails['adult_price']))
                                                                                        <span class="badge pricing-badge me-1 mb-1">
                                                                                            <i class="fas fa-user me-1"></i>Adult: ${{ number_format($ticketDetails['adult_price'], 2) }}
                                                                                        </span>
                                                                                    @endif
                                                                                    @if(!empty($ticketDetails['child_price']))
                                                                                        <span class="badge pricing-badge me-1 mb-1">
                                                                                            <i class="fas fa-child me-1"></i>Child: ${{ number_format($ticketDetails['child_price'], 2) }}
                                                                                        </span>
                                                                                    @endif
                                                                                    @if(!empty($ticketDetails['senior_price']))
                                                                                        <span class="badge pricing-badge me-1 mb-1">
                                                                                            <i class="fas fa-user-plus me-1"></i>Senior: ${{ number_format($ticketDetails['senior_price'], 2) }}
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                            @endif
                                                                            
                                                                            <!-- Transport Information -->
                                                                            @if(!empty($selection))
                                                                                <div class="transport-info mt-1">
                                                                                    <span class="badge transport-badge">
                                                                                        <i class="fas fa-car me-1"></i>
                                                                                        {{ $selection == 'withoutTransport' ? 'Without Transport' : 'With Transport' }}
                                                                                    </span>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                            </div>
                                                        @endif
                                                    @elseif(strtolower($serviceType) == 'restaurant')
                                                        @php
                                                            // Check if restaurant has transfer
                                                            $hasTransfer = isset($data['transfer_options']) && 
                                                                          isset($data['transfer_options']['transfer_required']) && 
                                                                          $data['transfer_options']['transfer_required'] === true;
                                                            
                                                            if ($hasTransfer) {
                                                                $transferOptions = $data['transfer_options'];
                                                                $pickupLocation = $transferOptions['pickup_location_name'] ?? '';
                                                                $transferType = $transferOptions['type'] ?? 'Shared';
                                                                $transferTypeDisplay = ucfirst($transferType) . ' Transfer';
                                                                $remark = $data['specialRequests'] ?? null;
                                                            }
                                                        @endphp
                                                        
                                                        @if($hasTransfer)
                                                            <p class="service-description">
                                                                <span class="service-detail-label">Service:</span> {{ $serviceName }} ({{ $transferTypeDisplay }})
                                                            </p>
                                                            
                                                            <p class="service-detail-line">
                                                                @if(!empty($pickupLocation))
                                                                    <span class="service-detail-label">From:</span> {{ $pickupLocation }}
                                                                    <span class="mx-1">•</span>
                                                                @endif
                                                                <span class="service-detail-label">To:</span> {{ $serviceName }}
                                                            </p>
                                                            
                                                            @if(!empty($remark))
                                                                <div class="service-remark">
                                                                    <span class="service-detail-label">Remark:</span> {{ $remark }}
                                                                </div>
                                                            @endif
                                                        @else
                                                            <p class="service-description">Dining experience</p>
                                                            
                                                            <!-- Enhanced Restaurant Details -->
                                                            <div class="restaurant-details mt-2">
                                                            @php 
                                                                // $data is already the restaurant item data
                                                                $restaurantData = $data;
                                                            @endphp
                                                            
                                                            @if($restaurantData && is_array($restaurantData))
                                                                @php
                                                                    // Check for different possible field names
                                                                    $bookingDate = $restaurantData['bookingDate'] ?? $restaurantData['booking_date'] ?? $restaurantData['date'] ?? null;
                                                                    $visitTime = $restaurantData['visitTime'] ?? $restaurantData['visit_time'] ?? $restaurantData['time'] ?? null;
                                                                    $totalPrice = $restaurantData['totalPrice'] ?? $restaurantData['total_price'] ?? $restaurantData['price'] ?? null;
                                                                    $mealPrice = $restaurantData['mealPrice'] ?? $restaurantData['meal_price'] ?? null;
                                                                    $transportPrice = $restaurantData['transportPrice'] ?? $restaurantData['transport_price'] ?? 0;
                                                                    $mealType = $restaurantData['mealType'] ?? $restaurantData['meal_type'] ?? null;
                                                                    $mealSpecificType = $restaurantData['mealSpecificType'] ?? $restaurantData['meal_specific_type'] ?? null;
                                                                    $adultCount = $restaurantData['adultCount'] ?? $restaurantData['adult_count'] ?? null;
                                                                    $childCount = $restaurantData['childCount'] ?? $restaurantData['child_count'] ?? null;
                                                                    $mealDescription = $restaurantData['MealDescription'] ?? $restaurantData['meal_description'] ?? null;
                                                                    $transport = $restaurantData['transport'] ?? null;
                                                                    $priceTypes = $restaurantData['priceTypes'] ?? $restaurantData['price_types'] ?? null;
                                                                @endphp
                                                                
                                                                <div class="restaurant-info-grid">
                                                                    @if(!empty($bookingDate) || !empty($visitTime))
                                                                        <div class="restaurant-info-item">
                                                                            <small class="text-muted">
                                                                                <i class="fas fa-calendar-alt me-1"></i>
                                                                                @if(!empty($bookingDate))
                                                                                    {{ date('d M Y', strtotime($bookingDate)) }}
                                                                                @endif
                                                                                @if(!empty($visitTime))
                                                                                    • {{ $visitTime }}
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                    @endif
                                                                    
                                                                    @if(!empty($totalPrice) && $priceHide == 0)
                                                                        <div class="restaurant-info-item">
                                                                            <small class="text-success fw-bold">
                                                                                <i class="fas fa-dollar-sign me-1"></i>
                                                                                SGD {{ number_format($totalPrice, 2) }}
                                                                                @if(!empty($priceTypes) && is_array($priceTypes))
                                                                                    <span class="price-mode-badge">{{ ucfirst($priceTypes[0]) }}</span>
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                
                                                                <!-- Meal Information -->
                                                                @if(!empty($mealType) || !empty($mealSpecificType) || !empty($adultCount) || !empty($childCount) || !empty($mealDescription))
                                                                    <div class="meal-summary mt-2">
                                                                        <div class="meal-header">
                                                                            <small class="text-primary fw-semibold">
                                                                                <i class="fas fa-utensils me-1"></i>
                                                                                Meal Details
                                                                            </small>
                                                                        </div>
                                                                        
                                                                        <div class="meal-item mt-1">
                                                                            @if(!empty($mealType) || !empty($mealSpecificType))
                                                                                <div class="meal-basic-info">
                                                                                    <small class="text-dark">
                                                                                        <strong>
                                                                                            @if(!empty($mealType))
                                                                                                {{ ucfirst($mealType) }}
                                                                                            @endif
                                                                                            @if(!empty($mealSpecificType))
                                                                                                @if(!empty($mealType)) - @endif
                                                                                                {{ $mealSpecificType }}
                                                                                            @endif
                                                                                        </strong>
                                                                                        @if(!empty($mealPrice) && $priceHide == 0)
                                                                                            <span class="meal-price-badge">SGD {{ number_format($mealPrice, 2) }}</span>
                                                                                        @endif
                                                                                    </small>
                                                                                </div>
                                                                            @endif
                                                                            
                                                                            <!-- Guest Count -->
                                                                            @if(!empty($adultCount) || !empty($childCount))
                                                                                <div class="guest-info mt-1">
                                                                                    <small class="text-muted">
                                                                                        <i class="fas fa-users me-1"></i>
                                                                                        @if(!empty($adultCount))
                                                                                            {{ $adultCount }} Adult{{ $adultCount > 1 ? 's' : '' }}
                                                                                        @endif
                                                                                        @if(!empty($childCount))
                                                                                            @if(!empty($adultCount)) • @endif
                                                                                            {{ $childCount }} Child{{ $childCount > 1 ? 'ren' : '' }}
                                                                                        @endif
                                                                                    </small>
                                                                                </div>
                                                                            @endif
                                                                            
                                                                            <!-- Meal Items -->
                                                                            @if(isset($mealDescription) && is_array($mealDescription) && count($mealDescription) > 0)
                                                                                <div class="meal-items-info mt-1">
                                                                                    <small class="text-muted mb-1 d-block">
                                                                                        <i class="fas fa-list me-1"></i>Menu Items:
                                                                                    </small>
                                                                                    <div class="meal-items-list">
                                                                                        @foreach($mealDescription as $item)
                                                                                            <span class="badge meal-item-badge me-1 mb-1">
                                                                                                {{ $item['name'] ?? $item['item_name'] ?? 'Menu Item' }}
                                                                                                @if(!empty($item['category']))
                                                                                                    <small class="item-category">({{ $item['category'] }})</small>
                                                                                                @endif
                                                                                                @if(!empty($item['item_type']))
                                                                                                    <small class="item-type">• {{ $item['item_type'] }}</small>
                                                                                                @endif
                                                                                            </span>
                                                                                        @endforeach
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                            
                                                                            <!-- Transport Information -->
                                                                            @if(!empty($transport) || !empty($transportPrice))
                                                                                <div class="transport-info mt-1">
                                                                                    <span class="badge transport-badge">
                                                                                        <i class="fas fa-car me-1"></i>
                                                                                        @if(!empty($transport))
                                                                                            {{ $transport }}
                                                                                        @else
                                                                                            @if($priceHide == 0 && $transportPrice > 0)
                                                                                                Transport Included (+SGD {{ number_format($transportPrice, 2) }})
                                                                                            @elseif($priceHide == 1)
                                                                                                @if(!empty($transport))
                                                                                                    {{ $transport }}
                                                                                                @else
                                                                                                    No Transport
                                                                                                @endif
                                                                                            @else
                                                                                                No Transport
                                                                                            @endif
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                                
                                            </div>
                                            
                                            <!-- Right Side Details - Hidden to match screenshot design -->
                                            <div class="service-right-details" style="display: none;">
                                                @php
                                                    // Extract right-side details based on service type
                                                    $rightImage = '';
                                                    $rightName = '';
                                                    $rightLocation = '';
                                                    
                                                    if (strtolower($serviceType) == 'hotel') {
                                                        $rightImage = '🏨';
                                                        $rightName = $serviceName;
                                                        $rightLocation = $data['hotelDetails']['location'] ?? $data['location'] ?? 'City Center';
                                                    } elseif (strtolower($serviceType) == 'guide') {
                                                        $rightImage = '👨‍🏫';
                                                        $rightName = $serviceName;
                                                        $rightLocation = $data['location'] ?? $data['city'] ?? 'Local Guide';
                                                    } elseif (strtolower($serviceType) == 'attraction') {
                                                        $rightImage = '🎯';
                                                        $rightName = $serviceName;
                                                        $rightLocation = $data['location'] ?? $data['address'] ?? 'Tourist Spot';
                                                    } elseif (strtolower($serviceType) == 'restaurant') {
                                                        $rightImage = '🍽️';
                                                        $rightName = $serviceName;
                                                        $rightLocation = $data['location'] ?? $data['address'] ?? 'Restaurant';
                                                    } elseif (strpos(strtolower($serviceType), 'transfer') !== false || strpos(strtolower($serviceType), 'travel') !== false) {
                                                        $rightImage = '🚗';
                                                        $rightName = $data['vehicle'] ?? $data['name'] ?? 'Vehicle';
                                                        $rightLocation = 'Transport Service';
                                                    } elseif (strtolower($serviceType) == 'arrival') {
                                                        $rightImage = '✈️';
                                                        $rightName = $data['vehicle'] ?? $data['name'] ?? 'Airport Transfer';
                                                        $rightLocation = 'Arrival Service';
                                                    } elseif (strtolower($serviceType) == 'departure') {
                                                        $rightImage = '🛫';
                                                        $rightName = $data['vehicle'] ?? $data['name'] ?? 'Airport Transfer';
                                                        $rightLocation = 'Departure Service';
                                                    } else {
                                                        $rightImage = '⚙️';
                                                        $rightName = $serviceName;
                                                        $rightLocation = 'Service';
                                                    }
                                                @endphp
                                                
                                                <div class="service-right-image">{{ $rightImage }}</div>
                                                <div class="service-right-name">{{ $rightName }}</div>
                                                <div class="service-right-location">{{ $rightLocation }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="no-service">
                                    No services booked for this day
                                </div>
                            @endif
                            
                            </div> <!-- Close services-list -->
                        </div> <!-- Close date-container -->
                        
                        @php $dayCount++; @endphp
                    @endforeach
                @else
                    <div class="no-service">
                        No itinerary available for this tour.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Print button for mobile -->
<button id="printItineraryMobile" class="btn-modern btn-primary-modern print-btn d-md-none" style="position: fixed; bottom: 24px; right: 24px; z-index: 1000; border-radius: 50%; width: 56px; height: 56px; padding: 0; box-shadow: var(--shadow-lg);">
    <i class="fas fa-print"></i>
</button>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Debug: Check if service items are being rendered correctly
        console.log('Service items found:', document.querySelectorAll('.service-item').length);
        document.querySelectorAll('.service-item').forEach((item, index) => {
            console.log(`Service item ${index}:`, {
                classes: item.className,
                serviceType: item.getAttribute('data-service-type'),
                id: item.id
            });
        });
        
        // Download Text Itinerary functionality
        // Extract customer info and hotel info from bookings BEFORE the function
        // This ensures data is available when the function is called
        @php
            $pdfCustomerInfo = [];
            $pdfAllServices = []; // Array to store ALL services organized by date: [date => [services]]
            $pdfDebugItineraryCount = 0;
            $pdfDebugBookingCount = 0;
            $addedHotels = []; // Track hotels already added to avoid duplicates
            $addedServices = []; // Track all services (restaurants, attractions, etc.) already added to avoid duplicates
            
            // Check if itineraryByDate exists and has data
            if (isset($itineraryByDate) && is_array($itineraryByDate) && count($itineraryByDate) > 0) {
                $pdfDebugItineraryCount = count($itineraryByDate);
                
                // Sort dates
                ksort($itineraryByDate);
                
                // Loop through all dates and bookings
                foreach ($itineraryByDate as $date => $bookings) {
                    if (!is_array($bookings) || empty($bookings)) {
                        continue;
                    }
                    
                    // Initialize services array for this date
                    if (!isset($pdfAllServices[$date])) {
                        $pdfAllServices[$date] = [];
                    }
                    
                    foreach ($bookings as $booking) {
                        if (!$booking) {
                            continue;
                        }
                        
                        $pdfDebugBookingCount++;
                        
                        // Get booking type - handle both object and array access
                        $bookingType = null;
                        if (is_object($booking) && isset($booking->type)) {
                            $bookingType = $booking->type;
                        } elseif (is_array($booking) && isset($booking['type'])) {
                            $bookingType = $booking['type'];
                        }
                        
                        $data = null;
                        
                        // Extract data - try data_decoded first (already processed by controller/blade)
                        if (is_object($booking) && isset($booking->data_decoded) && is_array($booking->data_decoded) && !empty($booking->data_decoded)) {
                            $data = $booking->data_decoded[0] ?? null;
                        } elseif (is_array($booking) && isset($booking['data_decoded']) && is_array($booking['data_decoded']) && !empty($booking['data_decoded'])) {
                            $data = $booking['data_decoded'][0] ?? null;
                        }
                        // If data_decoded not available, try raw data
                        elseif (is_object($booking) && isset($booking->data)) {
                            if (is_string($booking->data)) {
                                try {
                                    $decoded = json_decode($booking->data, true);
                                    // Booking data structure: [ { ... } ]
                                    if (is_array($decoded) && isset($decoded[0])) {
                                        $data = $decoded[0];
                                    } elseif (is_array($decoded) && !isset($decoded[0]) && count($decoded) > 0) {
                                        // Single object in array
                                        $data = $decoded;
                                    }
                                } catch (\Exception $e) {
                                    // JSON decode failed
                                    continue;
                                }
                            } elseif (is_array($booking->data)) {
                                $data = isset($booking->data[0]) ? $booking->data[0] : $booking->data;
                            }
                        } elseif (is_array($booking) && isset($booking['data'])) {
                            if (is_string($booking['data'])) {
                                try {
                                    $decoded = json_decode($booking['data'], true);
                                    if (is_array($decoded) && isset($decoded[0])) {
                                        $data = $decoded[0];
                                    } elseif (is_array($decoded) && !isset($decoded[0]) && count($decoded) > 0) {
                                        $data = $decoded;
                                    }
                                } catch (\Exception $e) {
                                    continue;
                                }
                            } elseif (is_array($booking['data'])) {
                                $data = isset($booking['data'][0]) ? $booking['data'][0] : $booking['data'];
                            }
                        }
                        
                        if (!$data || !is_array($data)) {
                            continue;
                        }
                        
                        // Extract customer info from any booking (all booking types have fullName, email, phone)
                        if (empty($pdfCustomerInfo) && isset($data['fullName']) && !empty($data['fullName'])) {
                            $pdfCustomerInfo = [
                                'fullName' => $data['fullName'] ?? '',
                                'email' => $data['email'] ?? '',
                                'phone' => $data['phone'] ?? '',
                                'address' => $data['address'] ?? $data['address1'] ?? $data['address2'] ?? 'N/A',
                                'state' => $data['state'] ?? $data['city'] ?? 'N/A',
                                'zip' => $data['zip'] ?? $data['postal_code'] ?? 'N/A',
                            ];
                        }
                        
                        $bookingTypeLower = strtolower($bookingType ?? '');
                        
                        // Process ALL service types including hotels - organize by date
                        if ($bookingTypeLower == 'hotel') {
                            // Skip checkout entries
                            $stayType = $data['stay_type'] ?? null;
                            if ($stayType && strtolower($stayType) == 'checkout') {
                                continue;
                            }
                            
                            $hotelName = $data['hotelDetails']['hotel_name'] ?? $data['hotelname'] ?? $data['name'] ?? null;
                            
                            if ($hotelName) {
                                // bookingDate is array: ["2026-01-08", "2026-01-13"]
                                $bookingDate = $data['bookingDate'] ?? null;
                                $checkInDate = null;
                                $checkOutDate = null;
                                
                                if (is_array($bookingDate) && count($bookingDate) >= 2) {
                                    $checkInDate = $bookingDate[0];
                                    $checkOutDate = $bookingDate[1];
                                } elseif (is_array($bookingDate) && count($bookingDate) == 1) {
                                    $checkInDate = $bookingDate[0];
                                } elseif ($bookingDate) {
                                    $checkInDate = $bookingDate;
                                }
                                
                                // Adjust hotel date to match tour year if needed
                                $hotelDate = $checkInDate ?? $date;
                                if ($hotelDate && isset($tourDetails->check_in_time)) {
                                    try {
                                        $tourCheckIn = \Carbon\Carbon::parse($tourDetails->check_in_time);
                                        $hotelCheckIn = \Carbon\Carbon::parse($hotelDate);
                                        
                                        // If hotel year differs from tour year, adjust to tour year
                                        if ($hotelCheckIn->year != $tourCheckIn->year) {
                                            $hotelDate = $tourCheckIn->year . '-' . 
                                                       str_pad($hotelCheckIn->month, 2, '0', STR_PAD_LEFT) . '-' . 
                                                       str_pad($hotelCheckIn->day, 2, '0', STR_PAD_LEFT);
                                        }
                                    } catch (\Exception $e) {
                                        // If date parsing fails, use original date
                                    }
                                }
                                
                                // Only add hotel on check-in date (not on every day of stay)
                                // This ensures hotels appear together with other services on the same date
                                
                                // Check if we already added this hotel for this check-in date
                                $hotelKey = $hotelName . '_' . $hotelDate;
                                
                                if (isset($addedHotels[$hotelKey])) {
                                    continue; // Skip if already added
                                }
                                
                                // Only add on check-in day (stay_type is 'checkin' or first occurrence)
                                $shouldAdd = false;
                                if ($stayType && strtolower($stayType) == 'checkin') {
                                    // This is the check-in day, add it
                                    $shouldAdd = true;
                                } elseif (!$stayType || strtolower($stayType) == 'stay') {
                                    // If no stay_type or it's a stay day, check if this is the first occurrence
                                    // by comparing the current date with check-in date
                                    if ($date == $hotelDate) {
                                        $shouldAdd = true; // This is the check-in date
                                    }
                                }
                                
                                if (!$shouldAdd) {
                                    continue; // Not the check-in date, skip
                                }
                                
                                // Calculate nights from dates
                                $nights = 1;
                                if ($checkInDate && $checkOutDate) {
                                    try {
                                        $checkIn = \Carbon\Carbon::parse($checkInDate);
                                        $checkOut = \Carbon\Carbon::parse($checkOutDate);
                                        $nights = $checkIn->diffInDays($checkOut);
                                    } catch (\Exception $e) {
                                        $nights = $data['total_nights'] ?? $data['nights'] ?? 1;
                                    }
                                } else {
                                    $nights = $data['total_nights'] ?? $data['nights'] ?? 1;
                                }
                                
                                // Get room info - rooms is array: [ { "room_type": "...", "beds": [...] } ]
                                $roomType = 'Standard';
                                $roomCount = 1;
                                if (isset($data['rooms']) && is_array($data['rooms']) && count($data['rooms']) > 0) {
                                    $firstRoom = $data['rooms'][0];
                                    $roomType = $firstRoom['room_type'] ?? 'Standard';
                                    $roomCount = count($data['rooms']);
                                }
                                
                                $serviceInfo = [
                                    'type' => $bookingType,
                                    'date' => $hotelDate, // Use check-in date, not the loop date
                                    'name' => $hotelName,
                                    'checkIn' => $checkInDate ?? ($tourDetails->check_in_time ?? null),
                                    'checkOut' => $checkOutDate ?? ($tourDetails->check_out_time ?? null),
                                    'roomType' => $roomType,
                                    'roomCount' => $roomCount,
                                    'nights' => $nights,
                                    'confirmationNo' => $data['confirmationNo'] ?? $data['confirmation_no'] ?? $data['confirmation_number'] ?? null,
                                    'stayType' => $stayType,
                                    'data' => $data
                                ];
                                
                                // Add hotel to services for check-in date (so it appears with other services)
                                if (!isset($pdfAllServices[$hotelDate])) {
                                    $pdfAllServices[$hotelDate] = [];
                                }
                                $pdfAllServices[$hotelDate][] = $serviceInfo;
                                
                                // Mark this hotel as added
                                $addedHotels[$hotelKey] = true;
                            }
                        }
                        // Process OTHER service types (restaurant, attraction, guide, transport, entry_port, exit_port)
                        else {
                            // Create a unique key for deduplication based on booking ID or service details
                            $bookingId = null;
                            if (is_object($booking) && isset($booking->id)) {
                                $bookingId = $booking->id;
                            } elseif (is_array($booking) && isset($booking['id'])) {
                                $bookingId = $booking['id'];
                            }
                            
                            // Normalize booking type for consistent deduplication
                            // Convert "Exit Port" to "exit_port" and "Entry Port" to "entry_port" for consistency
                            $normalizedType = $bookingTypeLower;
                            if ($bookingTypeLower == 'exit port') {
                                $normalizedType = 'exit_port';
                            } elseif ($bookingTypeLower == 'entry port') {
                                $normalizedType = 'entry_port';
                            }
                            
                            // For restaurants and attractions, create a unique key
                            $serviceKey = null;
                            if ($bookingTypeLower == 'restaurant') {
                                $serviceName = $data['restaurantName'] ?? $data['name'] ?? '';
                                $visitTime = $data['visitTime'] ?? $data['time'] ?? '';
                                $serviceKey = $bookingId ? "restaurant_{$bookingId}_{$date}" : "restaurant_{$serviceName}_{$visitTime}_{$date}";
                            } elseif ($bookingTypeLower == 'attraction') {
                                $serviceName = $data['AttractionName'] ?? $data['name'] ?? '';
                                $visitTime = $data['visitTime'] ?? $data['time'] ?? '';
                                $serviceKey = $bookingId ? "attraction_{$bookingId}_{$date}" : "attraction_{$serviceName}_{$visitTime}_{$date}";
                            } else {
                                // For other services, use normalized type and booking ID if available
                                // Also include a unique identifier from the data to prevent duplicates
                                $dataId = $data['id'] ?? $data['booking_id'] ?? null;
                                if ($bookingId && $dataId) {
                                    $serviceKey = "{$normalizedType}_{$bookingId}_{$dataId}_{$date}";
                                } elseif ($bookingId) {
                                    $serviceKey = "{$normalizedType}_{$bookingId}_{$date}";
                                } elseif ($dataId) {
                                    $serviceKey = "{$normalizedType}_{$dataId}_{$date}";
                                } else {
                                    $serviceKey = null;
                                }
                            }
                            
                            // Skip if this service was already added
                            if ($serviceKey && isset($addedServices[$serviceKey])) {
                                continue; // Skip duplicate
                            }
                            
                            // Normalize the type in serviceInfo to prevent duplicates with different case
                            $normalizedServiceType = $normalizedType;
                            
                            $serviceInfo = [
                                'type' => $normalizedServiceType,
                                'date' => $date,
                                'data' => $data
                            ];
                            
                            // Extract service-specific information
                            if ($bookingTypeLower == 'restaurant') {
                                $serviceInfo['name'] = $data['restaurantName'] ?? $data['name'] ?? 'N/A';
                                $serviceInfo['visitTime'] = $data['visitTime'] ?? $data['time'] ?? null;
                                $serviceInfo['mealType'] = $data['mealType'] ?? null;
                                $serviceInfo['mealSpecificType'] = $data['mealSpecificType'] ?? null;
                                $serviceInfo['transferType'] = $data['transfer_options']['type'] ?? null;
                                $serviceInfo['way'] = $data['transfer_options']['way'] ?? null;
                                $serviceInfo['pickupLocation'] = $data['transfer_options']['pickup_location_name'] ?? null;
                                
                                // Get vehicle and driver data from booking (populated in controller)
                                // Access vehicle_driver_data from Eloquent model attributes
                                // Since vehicle_driver_data is in #attributes array, access it directly
                                $vehicleDriverData = null;
                                if (is_object($booking)) {
                                    // Try multiple access methods to get vehicle_driver_data
                                    // Method 1: Direct property access (should work for attributes)
                                    if (isset($booking->vehicle_driver_data)) {
                                        $vehicleDriverData = $booking->vehicle_driver_data;
                                    }
                                    // Method 2: getAttribute method
                                    elseif (method_exists($booking, 'getAttribute')) {
                                        $vehicleDriverData = $booking->getAttribute('vehicle_driver_data');
                                    }
                                    // Method 3: Direct access to attributes array
                                    elseif (property_exists($booking, 'attributes') && isset($booking->attributes['vehicle_driver_data'])) {
                                        $vehicleDriverData = $booking->attributes['vehicle_driver_data'];
                                    }
                                } elseif (is_array($booking) && isset($booking['vehicle_driver_data'])) {
                                    $vehicleDriverData = $booking['vehicle_driver_data'];
                                }
                                
                                // Set vehicle and driver data to serviceInfo - always set these properties
                                // This ensures they're available in JavaScript even if data is missing
                                $serviceInfo['vehicleNumber'] = ($vehicleDriverData && is_array($vehicleDriverData) && isset($vehicleDriverData['vehicleNumber'])) ? $vehicleDriverData['vehicleNumber'] : 'N/A';
                                $serviceInfo['vehicleName'] = ($vehicleDriverData && is_array($vehicleDriverData) && isset($vehicleDriverData['vehicleName'])) ? $vehicleDriverData['vehicleName'] : 'N/A';
                                $serviceInfo['maxPassengerCapacity'] = ($vehicleDriverData && is_array($vehicleDriverData) && isset($vehicleDriverData['maxPassengerCapacity'])) ? $vehicleDriverData['maxPassengerCapacity'] : 'N/A';
                                $serviceInfo['driverName'] = ($vehicleDriverData && is_array($vehicleDriverData) && isset($vehicleDriverData['driverName'])) ? $vehicleDriverData['driverName'] : 'N/A';
                                $serviceInfo['driverPhone'] = ($vehicleDriverData && is_array($vehicleDriverData) && isset($vehicleDriverData['driverPhone'])) ? $vehicleDriverData['driverPhone'] : 'N/A';
                            } elseif ($bookingTypeLower == 'attraction') {
                                $serviceInfo['name'] = $data['AttractionName'] ?? $data['name'] ?? 'N/A';
                                $serviceInfo['visitTime'] = $data['visitTime'] ?? $data['time'] ?? null;
                                $serviceInfo['ticketName'] = $data['ticketName'] ?? null;
                                $serviceInfo['transferType'] = $data['transfer_options']['type'] ?? null;
                                $serviceInfo['pickupLocation'] = $data['transfer_options']['pickup_location_name'] ?? null;
                                
                                // Get vehicle and driver data from booking (populated in controller)
                                // Access vehicle_driver_data from Eloquent model attributes
                                // Since vehicle_driver_data is in #attributes array, access it directly
                                $vehicleDriverData = is_object($booking) ? ($booking->vehicle_driver_data ?? null) : ($booking['vehicle_driver_data'] ?? null);
                                
                                // Set vehicle and driver data to serviceInfo
                                if ($vehicleDriverData && is_array($vehicleDriverData) && !empty($vehicleDriverData)) {
                                    $serviceInfo['vehicleNumber'] = $vehicleDriverData['vehicleNumber'] ?? 'N/A';
                                    $serviceInfo['vehicleName'] = $vehicleDriverData['vehicleName'] ?? 'N/A';
                                    $serviceInfo['maxPassengerCapacity'] = $vehicleDriverData['maxPassengerCapacity'] ?? 'N/A';
                                    $serviceInfo['driverName'] = $vehicleDriverData['driverName'] ?? 'N/A';
                                    $serviceInfo['driverPhone'] = $vehicleDriverData['driverPhone'] ?? 'N/A';
                                } else {
                                    // Fallback to N/A if data not available
                                    $serviceInfo['vehicleNumber'] = 'N/A';
                                    $serviceInfo['maxPassengerCapacity'] = 'N/A';
                                    $serviceInfo['driverName'] = 'N/A';
                                    $serviceInfo['driverPhone'] = 'N/A';
                                }
                            } elseif ($bookingTypeLower == 'guide') {
                                $serviceInfo['name'] = $data['guide_name'] ?? $data['guideName'] ?? 'N/A';
                                $serviceInfo['entryTime'] = $data['entrytime'] ?? $data['entryTime'] ?? null;
                                $serviceInfo['hours'] = $data['hours'] ?? null;
                                $serviceInfo['languages'] = $data['languages'] ?? [];
                            } elseif ($bookingTypeLower == 'travel_point' || $bookingTypeLower == 'travel_hourly' || $bookingTypeLower == 'point to point' || $bookingTypeLower == 'hourly' || $bookingTypeLower == 'local_transport') {
                                $serviceInfo['pickup'] = $data['entrypickup'] ?? $data['pickup'] ?? null;
                                $serviceInfo['dropoff'] = $data['entrydropoff'] ?? $data['dropoff'] ?? $data['dropoffLocation'] ?? null;
                                $serviceInfo['pickupTime'] = $data['pickupdate'] ?? $data['pickupTime'] ?? null;
                                $serviceInfo['vehicle'] = $data['vehicle'] ?? $data['vehicles_name'] ?? null;
                                $serviceInfo['transferType'] = $data['type'] ?? null;
                                $serviceInfo['selectedHours'] = $data['selectedHours'] ?? null;
                            } elseif ($bookingTypeLower == 'entry_port' || $bookingTypeLower == 'entry port') {
                                $serviceInfo['pickup'] = $data['entrypickup'] ?? $data['entry_pickup'] ?? $data['pickup'] ?? null;
                                $serviceInfo['dropoff'] = $data['entrydropoff'] ?? $data['entry_dropoff'] ?? $data['dropoff'] ?? null;
                                $serviceInfo['pickupTime'] = $data['pickupdate'] ?? $data['entrytime'] ?? null;
                                $serviceInfo['vehicle'] = $data['vehicles_name'] ?? $data['vehicle'] ?? null;
                                $serviceInfo['transferType'] = $data['type'] ?? $data['transfer_options']['type'] ?? null;
                            
                                // Get vehicle and driver data from booking (populated in controller)
                                // Access vehicle_driver_data from Eloquent model attributes
                                // Since vehicle_driver_data is in #attributes array, access it directly
                                $vehicleDriverData = null;
                                if (is_object($booking)) {
                                    // Try multiple access methods to get vehicle_driver_data
                                    // Method 1: Direct property access (should work for attributes)
                                    if (isset($booking->vehicle_driver_data)) {
                                        $vehicleDriverData = $booking->vehicle_driver_data;
                                    }
                                    // Method 2: getAttribute method
                                    elseif (method_exists($booking, 'getAttribute')) {
                                        $vehicleDriverData = $booking->getAttribute('vehicle_driver_data');
                                    }
                                    // Method 3: Direct access to attributes array
                                    elseif (property_exists($booking, 'attributes') && isset($booking->attributes['vehicle_driver_data'])) {
                                        $vehicleDriverData = $booking->attributes['vehicle_driver_data'];
                                    }
                                } elseif (is_array($booking) && isset($booking['vehicle_driver_data'])) {
                                    $vehicleDriverData = $booking['vehicle_driver_data'];
                                }
                                // Set vehicle and driver data to serviceInfo - always set these properties
                                // This ensures they're available in JavaScript even if data is missing
                                $serviceInfo['vehicleNumber'] = ($vehicleDriverData && is_array($vehicleDriverData) && isset($vehicleDriverData['vehicleNumber'])) ? $vehicleDriverData['vehicleNumber'] : 'N/A';
                                $serviceInfo['vehicleName'] = ($vehicleDriverData && is_array($vehicleDriverData) && isset($vehicleDriverData['vehicleName'])) ? $vehicleDriverData['vehicleName'] : 'N/A';
                                $serviceInfo['maxPassengerCapacity'] = ($vehicleDriverData && is_array($vehicleDriverData) && isset($vehicleDriverData['maxPassengerCapacity'])) ? $vehicleDriverData['maxPassengerCapacity'] : 'N/A';
                                $serviceInfo['driverName'] = ($vehicleDriverData && is_array($vehicleDriverData) && isset($vehicleDriverData['driverName'])) ? $vehicleDriverData['driverName'] : 'N/A';
                                $serviceInfo['driverPhone'] = ($vehicleDriverData && is_array($vehicleDriverData) && isset($vehicleDriverData['driverPhone'])) ? $vehicleDriverData['driverPhone'] : 'N/A';
                                
                            } elseif ($bookingTypeLower == 'exit_port' || $bookingTypeLower == 'exit port') {
                                $serviceInfo['pickup'] = $data['exitpickup'] ?? $data['exit_pickup'] ?? $data['pickup'] ?? null;
                                $serviceInfo['dropoff'] = $data['exitdropoff'] ?? $data['exit_dropoff'] ?? $data['dropoff'] ?? null;
                                $serviceInfo['pickupTime'] = $data['exitpickupdate'] ?? $data['exitpickuptime'] ?? null;
                                $serviceInfo['vehicle'] = $data['vehicles_name'] ?? $data['vehicle'] ?? null;
                                $serviceInfo['transferType'] = $data['type'] ?? null;
                                
                                // Get vehicle and driver data from booking (populated in controller)
                                // Access vehicle_driver_data from Eloquent model attributes
                                // Since vehicle_driver_data is in #attributes array, access it directly
                                $vehicleDriverData = null;
                                if (is_object($booking)) {
                                    // Try multiple access methods to get vehicle_driver_data
                                    // Method 1: Direct property access (should work for attributes)
                                    if (isset($booking->vehicle_driver_data)) {
                                        $vehicleDriverData = $booking->vehicle_driver_data;
                                    }
                                    // Method 2: getAttribute method
                                    elseif (method_exists($booking, 'getAttribute')) {
                                        $vehicleDriverData = $booking->getAttribute('vehicle_driver_data');
                                    }
                                    // Method 3: Direct access to attributes array
                                    elseif (property_exists($booking, 'attributes') && isset($booking->attributes['vehicle_driver_data'])) {
                                        $vehicleDriverData = $booking->attributes['vehicle_driver_data'];
                                    }
                                } elseif (is_array($booking) && isset($booking['vehicle_driver_data'])) {
                                    $vehicleDriverData = $booking['vehicle_driver_data'];
                                }
                                
                                // Set vehicle and driver data to serviceInfo - always set these properties
                                // This ensures they're available in JavaScript even if data is missing
                                $serviceInfo['vehicleNumber'] = ($vehicleDriverData && is_array($vehicleDriverData) && isset($vehicleDriverData['vehicleNumber'])) ? $vehicleDriverData['vehicleNumber'] : 'N/A';
                                $serviceInfo['vehicleName'] = ($vehicleDriverData && is_array($vehicleDriverData) && isset($vehicleDriverData['vehicleName'])) ? $vehicleDriverData['vehicleName'] : 'N/A';
                                $serviceInfo['maxPassengerCapacity'] = ($vehicleDriverData && is_array($vehicleDriverData) && isset($vehicleDriverData['maxPassengerCapacity'])) ? $vehicleDriverData['maxPassengerCapacity'] : 'N/A';
                                $serviceInfo['driverName'] = ($vehicleDriverData && is_array($vehicleDriverData) && isset($vehicleDriverData['driverName'])) ? $vehicleDriverData['driverName'] : 'N/A';
                                $serviceInfo['driverPhone'] = ($vehicleDriverData && is_array($vehicleDriverData) && isset($vehicleDriverData['driverPhone'])) ? $vehicleDriverData['driverPhone'] : 'N/A';
                                
                            }
                            
                            $pdfAllServices[$date][] = $serviceInfo;
                            
                            // Mark this service as added
                            if ($serviceKey) {
                                $addedServices[$serviceKey] = true;
                            }
                        }
                    }
                }
            }
        @endphp
        
        // Store extracted data in JavaScript variables (available globally)
        const pdfCustomerInfo = @json($pdfCustomerInfo ?? []);
        const pdfAllServices = @json($pdfAllServices ?? []);
        console.log('pdfAllServices', pdfAllServices);
        const pdfDebugInfo = {
            itineraryCount: @json($pdfDebugItineraryCount ?? 0),
            bookingCount: @json($pdfDebugBookingCount ?? 0)
        };
        
        // Download Text Itinerary functionality (only if button exists)
        const downloadTextBtn = document.getElementById('downloadText');
        if (downloadTextBtn) {
            downloadTextBtn.addEventListener('click', function() {
                downloadAsText();
            });
        }
        
        // Download Excel Format PDF functionality (only if button exists)
        const downloadExcelFormatBtn = document.getElementById('downloadExcelFormat');
        if (downloadExcelFormatBtn) {
            downloadExcelFormatBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Download Excel Format button clicked');
                try {
                    downloadAsExcelFormat();
                } catch (error) {
                    console.error('Error in downloadAsExcelFormat:', error);
                    showErrorMessage('Failed to download Excel format: ' + error.message);
                }
            });
        }
        
        // Download PDF functionality (only if button exists)
        const downloadPdfBtn = document.getElementById('downloadPdf');
        if (downloadPdfBtn) {
            downloadPdfBtn.addEventListener('click', function() {
                downloadAsPDF();
            });
        }
        
        // Print functionality (only if button exists)
        const printItineraryBtn = document.getElementById('printItinerary');
        if (printItineraryBtn) {
            printItineraryBtn.addEventListener('click', function() {
                preparePrint();
                setTimeout(function() {
                    window.print();
                }, 300);
            });
        }
        
        // Print functionality for mobile (only if button exists)
        const printItineraryMobileBtn = document.getElementById('printItineraryMobile');
        if (printItineraryMobileBtn) {
            printItineraryMobileBtn.addEventListener('click', function() {
                preparePrint();
                setTimeout(function() {
                    window.print();
                }, 300);
            });
        }
        
        function downloadAsPDF() {
            // Check if libraries are loaded
            if (typeof html2pdf === 'undefined' || typeof html2canvas === 'undefined') {
                showErrorMessage('PDF library not loaded. Please refresh the page and try again.');
                return;
            }
            
            // Show loading message
            const loadingMsg = showMessage('Generating PDF...', 'info');
            
            // Get the itinerary container (this already excludes sidebar, navigation, etc.)
            const itineraryContainer = document.querySelector('.itinerary-container');
            
            if (!itineraryContainer) {
                loadingMsg.remove();
                showErrorMessage('Itinerary content not found');
                return;
            }
            
            // Check if container has content
            const hasContent = itineraryContainer.querySelector('.timeline-container, .itinerary-header, .date-container');
            if (!hasContent) {
                loadingMsg.remove();
                showErrorMessage('Itinerary content is empty');
                return;
            }
            
            // Scroll to top to ensure content is visible
            window.scrollTo({ top: 0, behavior: 'instant' });
            
            // Store original display states of buttons to restore later
            const buttonsToHide = itineraryContainer.querySelectorAll(
                '.header-actions, .print-btn, #printItineraryMobile, button[id*="print"], button[id*="download"]'
            );
            const originalDisplayStates = [];
            buttonsToHide.forEach(btn => {
                originalDisplayStates.push({
                    element: btn,
                    display: btn.style.display
                });
                btn.style.display = 'none';
            });
            
            // Wait for scroll and rendering
            setTimeout(function() {
                // Scroll element into view
                itineraryContainer.scrollIntoView({ behavior: 'instant', block: 'start' });
                
                // Wait a bit more for rendering
                setTimeout(function() {
                    // Use html2canvas to capture the content
                    html2canvas(itineraryContainer, {
                        scale: 2,
                        useCORS: true,
                        logging: false,
                        backgroundColor: '#ffffff',
                        windowWidth: itineraryContainer.scrollWidth || 1400,
                        windowHeight: itineraryContainer.scrollHeight || window.innerHeight,
                        allowTaint: true,
                        letterRendering: true,
                        scrollX: 0,
                        scrollY: -window.scrollY,
                        onclone: function(clonedDoc) {
                            // Ensure all styles are visible in the clone
                            const clonedContainer = clonedDoc.querySelector('.itinerary-container');
                            if (clonedContainer) {
                                clonedContainer.style.visibility = 'visible';
                                clonedContainer.style.opacity = '1';
                                clonedContainer.style.display = 'block';
                            }
                        }
                    }).then(function(canvas) {
                        // Check if canvas has content (not all white)
                        const ctx = canvas.getContext('2d');
                        const imageData = ctx.getImageData(0, 0, Math.min(canvas.width, 100), Math.min(canvas.height, 100));
                        let hasContent = false;
                        for (let i = 0; i < imageData.data.length; i += 4) {
                            // Check if pixel is not white (255, 255, 255)
                            if (imageData.data[i] < 250 || imageData.data[i + 1] < 250 || imageData.data[i + 2] < 250) {
                                hasContent = true;
                                break;
                            }
                        }
                        
                        if (!hasContent) {
                            loadingMsg.remove();
                            showErrorMessage('Content not captured. Please ensure the itinerary is visible on screen.');
                            // Restore buttons
                            originalDisplayStates.forEach(item => {
                                item.element.style.display = item.display;
                            });
                            return;
                        }
                        
                        // Convert canvas to image
                        const imgData = canvas.toDataURL('image/jpeg', 0.98);
                        
                        // Calculate PDF dimensions
                        const imgWidth = 210; // A4 width in mm
                        const imgHeight = (canvas.height * imgWidth) / canvas.width;
                        
                        // Create PDF using jsPDF
                        const { jsPDF } = window.jspdf;
                        const pdf = new jsPDF('p', 'mm', 'a4');
                        
                        // Add image to PDF
                        const pageHeight = pdf.internal.pageSize.height;
                        let heightLeft = imgHeight;
                        let position = 10; // Start position
                        
                        pdf.addImage(imgData, 'JPEG', 10, position, imgWidth, imgHeight);
                        heightLeft -= pageHeight - 20;
                        
                        // Add additional pages if needed
                        while (heightLeft > 0) {
                            position = heightLeft - imgHeight + 10;
                            pdf.addPage();
                            pdf.addImage(imgData, 'JPEG', 10, position, imgWidth, imgHeight);
                            heightLeft -= pageHeight - 20;
                        }
                        
                        // Save PDF
                        pdf.save('Tour_Itinerary_{{ $tourId }}.pdf');
                        
                        // Restore button visibility
                        originalDisplayStates.forEach(item => {
                            item.element.style.display = item.display;
                        });
                        loadingMsg.remove();
                        showSuccessMessage('PDF downloaded successfully!');
                    }).catch(function(error) {
                        console.error('Canvas generation error:', error);
                        // Restore button visibility
                        originalDisplayStates.forEach(item => {
                            item.element.style.display = item.display;
                        });
                        loadingMsg.remove();
                        showErrorMessage('Failed to capture content: ' + (error.message || 'Unknown error. Please check console.'));
                    });
                }, 300);
            }, 200);
        }
        
        function downloadAsText() {
            /**
             * DATA STRUCTURE FOR ALL SERVICE TYPES:
             * 
             * IMPORTANT: booking->data is a JSON string that decodes to an ARRAY: [ { ... } ]
             * Each booking contains an array with one (or more) objects
             * 
             * Data is fetched from $itineraryByDate which is organized by date (Y-m-d format)
             * Each date contains an array of bookings, where each booking has:
             * - booking->type: 'hotel', 'restaurant', 'attraction', 'guide', 'travel_point', 'travel_hourly', 'entry_port', 'exit_port'
             * - booking->data_decoded: Array of booking items (usually [0] contains the main data)
             * 
             * NOTE: stay_type is NOT in original data - it's added during processing in blade template
             * 
             * HOTEL SERVICE - Actual JSON structure:
             * [{
             *   "fullName": "", "email": "", "phone": "",
             *   "bookingDate": ["2026-01-08", "2026-01-13"],  // ARRAY with [checkIn, checkOut]
             *   "hotelDetails": { "hotel_name": "...", "location": "...", "checkInTime": "...", "checkOutTime": "..." },
             *   "rooms": [{ "room_type": "...", "beds": [...] }],
             *   "totalPrice": 44000,
             *   "confirmationNo": "..." (may not exist)
             * }]
             * 
             * RESTAURANT SERVICE - Actual JSON structure:
             * [{
             *   "fullName": "", "email": "", "phone": "",
             *   "bookingDate": "2026-01-01",  // STRING
             *   "visitTime": "2:00 PM",
             *   "restaurantName": "...",
             *   "mealType": "Breakfast", "mealSpecificType": "...",
             *   "adultCount": 5, "childCount": 0,
             *   "totalPrice": 3500,
             *   "transfer_options": { "transfer_required": true, "type": "Private", "pickup_location_name": "..." }
             * }]
             * 
             * ATTRACTION SERVICE - Actual JSON structure:
             * [{
             *   "fullName": "", "email": "", "phone": "",
             *   "bookingDate": "2026-01-08",  // STRING
             *   "visitTime": "15:00 - 17:00",
             *   "AttractionName": "...",
             *   "ticketName": "...", "ticketId": 10000027,
             *   "adultCount": 5, "childCount": 0, "seniorCount": 0,
             *   "totalPrice": 3500,
             *   "transfer_options": { "transfer_required": true, "type": "Shared", "pickup_location_name": "..." }
             * }]
             * 
             * GUIDE SERVICE - Actual JSON structure:
             * [{
             *   "fullName": "", "email": "", "phone": "",
             *   "pickupdate": "2025-12-31", "bookingDate": "2025-12-31",  // STRING
             *   "guide_name": "...",
             *   "entrytime": "1:00 AM",
             *   "hours": 6,
             *   "experience": 0,
             *   "languages": ["Chinese"]
             * }]
             * 
             * LOCAL TRANSPORT - Actual JSON structure:
             * [{
             *   "fullName": "", "email": "", "phone": "",
             *   "pickupdate": "2025-12-30", "bookingDate": "2025-12-30",  // STRING
             *   "entrypickup": "...", "entrydropoff": "...",
             *   "vehicles_name": "...",
             *   "type": "local_transfer",
             *   "travel_type": "local_transfer",
             *   "totalPrice": 70
             * }]
             * 
             * ENTRY PORT - Actual JSON structure:
             * [{
             *   "fullName": "", "email": "", "phone": "",
             *   "pickupdate": "2025-12-30", "bookingDate": "2025-12-30",  // STRING
             *   "entrypickup": "...", "entrydropoff": "...",
             *   "entrytime": "01:00 PM",
             *   "vehicles_name": "...",
             *   "type": "Private",
             *   "travel_type": "entry_port",
             *   "totalPrice": 80
             * }]
             */
            
            // Check if jsPDF is loaded
            if (typeof window.jspdf === 'undefined') {
                showErrorMessage('PDF library not loaded. Please refresh the page and try again.');
                return;
            }
            
            // Show loading message
            const loadingMsg = showMessage('Generating text itinerary...', 'info');
            
            try {
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');
                
                // Get data from PHP variables (passed to JavaScript)
                const tourId = @json($tourId ?? '');
                const displayId = @json($tourDetails->display_id ?? '');
                const destination = @json($tourDetails->destination ?? '');
                const checkInTime = @json($tourDetails->check_in_time ?? null);
                const checkOutTime = @json($tourDetails->check_out_time ?? null);
                // Check for PNR number - could be in tourDetails or use tourId
                const pnrNumber = @json($tourDetails->pnr ?? $tourDetails->pnr_number ?? $tourId ?? '');
                
                // Use the pre-extracted data (defined outside function)
                const customerInfo = pdfCustomerInfo || [];
                const allServices = pdfAllServices || {};
                
                // Debug: Log extracted data
                console.log('=== PDF Data Extraction Debug ===');
                console.log('ItineraryByDate dates count:', pdfDebugInfo.itineraryCount);
                console.log('Total bookings processed:', pdfDebugInfo.bookingCount);
                console.log('Customer Info:', customerInfo);
                console.log('All Services (by date):', allServices);
                console.log('Tour ID:', tourId);
                console.log('Display ID:', displayId);
                console.log('Destination:', destination);
                console.log('Check In Time:', checkInTime);
                console.log('Check Out Time:', checkOutTime);
                console.log('===================================');
                
                // Validate that we have at least some data
                if (!customerInfo || Object.keys(customerInfo).length === 0) {
                    console.warn('Warning: No customer info found!');
                }
                if (Object.keys(allServices).length === 0) {
                    console.warn('Warning: No services found!');
                }
                
                // Format dates - match screenshot format: "23 Dec 2025" (no leading zero on day)
                let travelDate = '';
                const formatDate = (date) => {
                    const day = date.getDate(); // No padding - remove leading zero
                    const month = date.toLocaleString('en-US', { month: 'short' });
                    const year = date.getFullYear();
                    return `${day} ${month} ${year}`;
                };
                
                if (checkInTime && checkOutTime) {
                    const startDate = new Date(checkInTime);
                    const endDate = new Date(checkOutTime);
                    travelDate = `${formatDate(startDate)} - ${formatDate(endDate)}`;
                }
                
                // Company Information
                const companyName = 'TravelBullz (HK) Limited';
                const companyAddress = '701, 7th Floor, Wing Kwok Centre, 182, Woosung Street, Jordon, Kowloon, Hong Kong';
                const companyTel = '+852-23752321';
                const companyFax = '85223752369';
                const companyEmail = 'online@travelbullz.com';
                
                // Starting position
                let yPos = 20;
                const leftMargin = 20;
                const rightMargin = 190;
                const lineHeight = 7;
                const pageHeight = 297; // A4 height in mm
                const bottomMargin = 20; // Bottom margin
                const maxY = pageHeight - bottomMargin; // Maximum y position before new page
                
                // Helper function to check and add new page if needed
                const checkPageBreak = (requiredSpace = lineHeight) => {
                    if (yPos + requiredSpace > maxY) {
                        pdf.addPage();
                        yPos = 20; // Reset to top of new page
                        return true;
                    }
                    return false;
                };
                
                // Company Header
                pdf.setFontSize(14);
                pdf.setFont('helvetica', 'bold');
                pdf.text(companyName, leftMargin, yPos);
                yPos += lineHeight;
                
                pdf.setFontSize(10);
                pdf.setFont('helvetica', 'normal');
                pdf.text(companyAddress, leftMargin, yPos);
                yPos += lineHeight;
                pdf.text(`Tel: ${companyTel}`, leftMargin, yPos);
                yPos += lineHeight;
                pdf.text(`Fax: ${companyFax}`, leftMargin, yPos);
                yPos += lineHeight;
                pdf.text(`Email: ${companyEmail}`, leftMargin, yPos);
                yPos += lineHeight + 5;
                
                // Draw line
                pdf.setDrawColor(0, 150, 136); // Teal color
                pdf.setLineWidth(0.5);
                pdf.line(leftMargin, yPos, rightMargin, yPos);
                yPos += lineHeight;
                
                // YOUR TRAVEL ITINERARY
                pdf.setFontSize(12);
                pdf.setFont('helvetica', 'bold');
                pdf.text('YOUR TRAVEL ITINERARY', leftMargin, yPos);
                yPos += lineHeight;
                
                // Draw double underline
                pdf.setLineWidth(0.3);
                pdf.line(leftMargin, yPos, rightMargin, yPos);
                yPos += 2;
                pdf.line(leftMargin, yPos, rightMargin, yPos);
                yPos += lineHeight + 2;
                
                // PNR Number
                pdf.setFontSize(10);
                pdf.setFont('helvetica', 'normal');
                const pnrDisplay = pnrNumber || tourId || 'N/A';
                pdf.text(`PNR Number: ${pnrDisplay}`, leftMargin, yPos);
                yPos += lineHeight;
                
                // Reference Number - always show if available
                if (displayId && displayId !== '') {
                    pdf.text(`Reference Number: ${displayId}`, leftMargin, yPos);
                    yPos += lineHeight;
                }
                
                // Leading Guest Name
                if (customerInfo && customerInfo.fullName) {
                    const guestName = customerInfo.fullName;
                    const adultCount = @json($tourDetails->adult ?? 1);
                    const childCount = @json($tourDetails->child ?? 0);
                    const infantCount = @json($tourDetails->infant ?? 0);
                    const totalPax = adultCount + childCount + infantCount;
                    pdf.text(`Leading Guest Name: ${guestName} (${totalPax} ${totalPax > 1 ? 'Passenger(s)' : 'Passenger'})`, leftMargin, yPos);
                    yPos += lineHeight;
                } else {
                    // Fallback if no customer info
                    pdf.text(`Leading Guest Name: N/A`, leftMargin, yPos);
                    yPos += lineHeight;
                }
                
                // Travel Date
                if (travelDate) {
                    pdf.text(`Travel Date: ${travelDate}`, leftMargin, yPos);
                    yPos += lineHeight + 5;
                } else {
                    // Fallback if no travel date
                    pdf.text(`Travel Date: N/A`, leftMargin, yPos);
                    yPos += lineHeight + 5;
                }
                
                // TRIP DETAIL
                pdf.setFontSize(12);
                pdf.setFont('helvetica', 'bold');
                pdf.text('TRIP DETAIL', leftMargin, yPos);
                yPos += lineHeight;
                
                // Draw double underline
                pdf.setLineWidth(0.3);
                pdf.line(leftMargin, yPos, rightMargin, yPos);
                yPos += 2;
                pdf.line(leftMargin, yPos, rightMargin, yPos);
                yPos += lineHeight + 2;
                
                // Date - use same formatDate function
                pdf.setFontSize(10);
                pdf.setFont('helvetica', 'normal');
                if (checkInTime) {
                    try {
                        const date = new Date(checkInTime);
                        if (!isNaN(date.getTime())) {
                            pdf.text(`Date: ${formatDate(date)}`, leftMargin, yPos);
                            yPos += lineHeight;
                        } else {
                            pdf.text(`Date: N/A`, leftMargin, yPos);
                            yPos += lineHeight;
                        }
                    } catch (e) {
                        pdf.text(`Date: N/A`, leftMargin, yPos);
                        yPos += lineHeight;
                    }
                } else {
                    pdf.text(`Date: N/A`, leftMargin, yPos);
                    yPos += lineHeight;
                }
                
                // Destination
                if (destination) {
                    pdf.text(`Destination: ${destination}`, leftMargin, yPos);
                    yPos += lineHeight + 5;
                } else {
                    pdf.text(`Destination: N/A`, leftMargin, yPos);
                    yPos += lineHeight + 5;
                }
                
                // Display all services organized by date (including hotels)
                const sortedDates = Object.keys(allServices).sort();
                
                if (sortedDates.length > 0) {
                    let dayNumber = 1; // Start with Day 1
                    let previousDateKey = null; // Track previous date key (MM-DD format) to detect date changes
                    
                    sortedDates.forEach(date => {
                        const services = allServices[date];
                        if (!services || services.length === 0) return;
                        
                        // Only increment day number if the date actually changed
                        // Compare dates by month and day only (ignore year) so same calendar date stays same day
                        let currentDateKey = date; // Default to original string
                        try {
                            const dateObj = new Date(date);
                            if (!isNaN(dateObj.getTime())) {
                                // Format as MM-DD for comparison (ignore year)
                                const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                                const day = String(dateObj.getDate()).padStart(2, '0');
                                currentDateKey = `${month}-${day}`;
                            }
                        } catch (e) {
                            // If date parsing fails, try to extract MM-DD from string
                            // Try to match patterns like "2025-01-15" or "15 Jan 2025"
                            const dateMatch = date.match(/(\d{1,2})[-\s](\d{1,2})/);
                            if (dateMatch) {
                                const month = dateMatch[1].padStart(2, '0');
                                const day = dateMatch[2].padStart(2, '0');
                                currentDateKey = `${month}-${day}`;
                            }
                        }
                        
                        // Increment day number only if date changed (same month-day = same day number)
                        if (previousDateKey !== null && previousDateKey !== currentDateKey) {
                            dayNumber++;
                        }
                        previousDateKey = currentDateKey;
                        
                        // Date header - make it prominent
                        checkPageBreak(20); // Check if we need new page for date header
                        yPos += 5; // Add spacing before date section
                        pdf.setFontSize(12);
                        pdf.setFont('helvetica', 'bold');
                        try {
                            const serviceDate = new Date(date);
                            if (!isNaN(serviceDate.getTime())) {
                                pdf.text(`Day ${dayNumber} Date: ${formatDate(serviceDate)}`, leftMargin, yPos);
                                yPos += lineHeight;
                                
                                // Draw underline for date
                                pdf.setLineWidth(0.3);
                                pdf.line(leftMargin, yPos, rightMargin, yPos);
                                yPos += 3;
                            }
                        } catch (e) {
                            pdf.text(`Day ${dayNumber} Date: ${date}`, leftMargin, yPos);
                            yPos += lineHeight;
                            pdf.setLineWidth(0.3);
                            pdf.line(leftMargin, yPos, rightMargin, yPos);
                            yPos += 3;
                        }
                        
                        pdf.setFontSize(10);
                        pdf.setFont('helvetica', 'normal');
                        
                        // Display each service for this date
                        services.forEach((service, serviceIndex) => {
                            if (serviceIndex > 0) {
                                yPos += 3; // Spacing between services
                            }
                            
                            const serviceType = (service.type || '').toLowerCase();
                            
                            // Process HOTEL service - display like other services
                            if (serviceType === 'hotel') {
                                checkPageBreak(15);
                                pdf.setFont('helvetica', 'normal');
                                
                                // Hotel name
                                checkPageBreak();
                                pdf.setFont('helvetica', 'bold');
                                pdf.text('Hotel:', leftMargin, yPos);
                                pdf.setFont('helvetica', 'normal');
                                const hotelNameText = service.name || 'N/A';
                                const hotelNameWidth = pdf.getTextWidth('Hotel:');
                                pdf.text(hotelNameText, leftMargin + hotelNameWidth + 2, yPos); // Add 2mm spacing
                                yPos += lineHeight;
                                
                                // Check-in/Check-out
                                if (service.checkIn && service.checkOut) {
                                    try {
                                        const checkIn = new Date(service.checkIn);
                                        const checkOut = new Date(service.checkOut);
                                        if (!isNaN(checkIn.getTime()) && !isNaN(checkOut.getTime())) {
                                            checkPageBreak();
                                            pdf.text(`Check in ${formatDate(checkIn)} - ${formatDate(checkOut)}`, leftMargin, yPos);
                                            yPos += lineHeight;
                                        }
                                    } catch (e) {
                                        // Date parsing failed, skip
                                    }
                                }
                                
                                // Room Type & Quantity
                                checkPageBreak();
                                const roomType = service.roomType || 'Standard';
                                const roomCount = service.roomCount || 1;
                                const roomTypeDisplay = roomType.includes('/') ? roomType.split('/')[0].trim() : roomType;
                                const bedType = roomType.includes('/') ? roomType.split('/')[1]?.trim() : (roomCount > 1 ? 'Double' : 'Single');
                                pdf.text(`Room Type & Quantity: ${roomTypeDisplay} / ${bedType} x ${roomCount} Room(s)`, leftMargin, yPos);
                                yPos += lineHeight;
                                
                                // Nights
                                checkPageBreak();
                                const nights = service.nights || 1;
                                pdf.text(`Nights: ${nights} ${nights > 1 ? 'Night(s)' : 'Night(s)'}`, leftMargin, yPos);
                                yPos += lineHeight;
                                
                                // Confirmation No
                                if (service.confirmationNo) {
                                    checkPageBreak();
                                    pdf.text(`Confirmation No: ${service.confirmationNo}`, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                            }
                            // Process RESTAURANT service
                            else if (serviceType === 'restaurant') {
                                checkPageBreak();
                                pdf.setFont('helvetica', 'bold');
                                pdf.text('Restaurant:', leftMargin, yPos);
                                pdf.setFont('helvetica', 'normal');
                                const restaurantNameText = service.name || 'N/A';
                                const restaurantNameWidth = pdf.getTextWidth('Restaurant:');
                                pdf.text(restaurantNameText, leftMargin + restaurantNameWidth + 2, yPos); // Add 2mm spacing
                                yPos += lineHeight;
                                if (service.visitTime) {
                                    checkPageBreak();
                                    pdf.text(`Time: ${service.visitTime}`, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                                if (service.mealType) {
                                    checkPageBreak();
                                    pdf.text(`Meal: ${service.mealType}${service.mealSpecificType ? ' - ' + service.mealSpecificType : ''}`, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                                if (service.transferType) {
                                    checkPageBreak();
                                    let transferText = `Transfer: ${service.transferType}`;
                                    if (service.pickupLocation) {
                                        transferText += ` from ${service.pickupLocation}`;
                                    }
                                    if (service.way) {
                                        transferText += `, Way: ${service.way}`;
                                    }
                                    pdf.text(transferText, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                            } else if (serviceType === 'attraction') {
                                checkPageBreak();
                                pdf.setFont('helvetica', 'bold');
                                pdf.text('Attraction:', leftMargin, yPos);
                                pdf.setFont('helvetica', 'normal');
                                const attractionNameText = service.name || 'N/A';
                                const attractionNameWidth = pdf.getTextWidth('Attraction:');
                                pdf.text(attractionNameText, leftMargin + attractionNameWidth + 2, yPos); // Add 2mm spacing
                                yPos += lineHeight;
                                if (service.visitTime) {
                                    checkPageBreak();
                                    pdf.text(`Time: ${service.visitTime}`, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                                if (service.ticketName) {
                                    checkPageBreak();
                                    pdf.text(`Ticket: ${service.ticketName}`, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                                if (service.transferType) {
                                    checkPageBreak();
                                    pdf.text(`Transfer: ${service.transferType}${service.pickupLocation ? ' from ' + service.pickupLocation : ''}`, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                            } else if (serviceType === 'guide') {
                                checkPageBreak();
                                pdf.setFont('helvetica', 'bold');
                                pdf.text('Guide:', leftMargin, yPos);
                                pdf.setFont('helvetica', 'normal');
                                const guideNameText = service.name || 'N/A';
                                const guideNameWidth = pdf.getTextWidth('Guide:');
                                pdf.text(guideNameText, leftMargin + guideNameWidth + 2, yPos); // Add 2mm spacing
                                yPos += lineHeight;
                                if (service.entryTime) {
                                    checkPageBreak();
                                    pdf.text(`Time: ${service.entryTime}`, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                                if (service.hours) {
                                    checkPageBreak();
                                    pdf.text(`Duration: ${service.hours} hour(s)`, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                                if (service.languages && service.languages.length > 0) {
                                    checkPageBreak();
                                    pdf.text(`Languages: ${service.languages.join(', ')}`, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                            } else if (serviceType === 'travel_point' || serviceType === 'travel_hourly') {
                                checkPageBreak();
                                pdf.setFont('helvetica', 'bold');
                                pdf.text('Local Transport:', leftMargin, yPos);
                                pdf.setFont('helvetica', 'normal');
                                const localTransportText = service.transferType || 'Transfer';
                                const localTransportWidth = pdf.getTextWidth('Local Transport:');
                                pdf.text(localTransportText, leftMargin + localTransportWidth + 4, yPos); // Add 4mm spacing
                                yPos += lineHeight;
                                // Combine pickup and dropoff in one line
                                if (service.pickup || service.dropoff) {
                                    checkPageBreak();
                                    let fromToText = '';
                                    if (service.pickup) {
                                        fromToText = `From: ${service.pickup}`;
                                    }
                                    if (service.dropoff) {
                                        if (fromToText) {
                                            fromToText += ` To: ${service.dropoff}`;
                                        } else {
                                            fromToText = `To: ${service.dropoff}`;
                                        }
                                    }
                                    pdf.text(fromToText, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                                if (service.pickupTime) {
                                    checkPageBreak();
                                    pdf.text(`Time: ${service.pickupTime}`, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                                if (service.vehicle) {
                                    checkPageBreak();
                                    pdf.text(`Vehicle: ${service.vehicle}`, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                                if (service.selectedHours) {
                                    checkPageBreak();
                                    pdf.text(`Hours: ${service.selectedHours}`, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                            } else if (serviceType === 'entry_port') {
                                checkPageBreak();
                                pdf.setFont('helvetica', 'bold');
                                pdf.text('Arrival Transfer:', leftMargin, yPos);
                                pdf.setFont('helvetica', 'normal');
                                const arrivalTransferText = service.transferType || 'Transfer';
                                const arrivalTransferWidth = pdf.getTextWidth('Arrival Transfer:');
                                pdf.text(arrivalTransferText, leftMargin + arrivalTransferWidth + 4, yPos); // Add 4mm spacing
                                yPos += lineHeight;
                                // Combine pickup and dropoff in one line
                                if (service.pickup || service.dropoff) {
                                    checkPageBreak();
                                    let fromToText = '';
                                    if (service.pickup) {
                                        fromToText = `From: ${service.pickup}`;
                                    }
                                    if (service.dropoff) {
                                        if (fromToText) {
                                            fromToText += ` To: ${service.dropoff}`;
                                        } else {
                                            fromToText = `To: ${service.dropoff}`;
                                        }
                                    }
                                    pdf.text(fromToText, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                                if (service.pickupTime) {
                                    checkPageBreak();
                                    pdf.text(`Time: ${service.pickupTime}`, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                                if (service.vehicle) {
                                    checkPageBreak();
                                    pdf.text(`Vehicle: ${service.vehicle}`, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                            } else if (serviceType === 'exit_port') {
                                checkPageBreak();
                                pdf.setFont('helvetica', 'bold');
                                pdf.text('Departure Transfer:', leftMargin, yPos);
                                pdf.setFont('helvetica', 'normal');
                                const departureTransferText = service.transferType || 'Transfer';
                                const departureTransferWidth = pdf.getTextWidth('Departure Transfer:');
                                pdf.text(departureTransferText, leftMargin + departureTransferWidth + 4, yPos); // Add 4mm spacing
                                yPos += lineHeight;
                                // Combine pickup and dropoff in one line
                                if (service.pickup || service.dropoff) {
                                    checkPageBreak();
                                    let fromToText = '';
                                    if (service.pickup) {
                                        fromToText = `From: ${service.pickup}`;
                                    }
                                    if (service.dropoff) {
                                        if (fromToText) {
                                            fromToText += ` To: ${service.dropoff}`;
                                        } else {
                                            fromToText = `To: ${service.dropoff}`;
                                        }
                                    }
                                    pdf.text(fromToText, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                                if (service.pickupTime) {
                                    checkPageBreak();
                                    pdf.text(`Time: ${service.pickupTime}`, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                                if (service.vehicle) {
                                    checkPageBreak();
                                    pdf.text(`Vehicle: ${service.vehicle}`, leftMargin, yPos);
                                    yPos += lineHeight;
                                }
                            }
                            
                            yPos += 2; // Spacing between services
                        });
                        
                        yPos += 3; // Spacing between dates
                    });
                }
                
                yPos += 10;
                
                // Draw line
                checkPageBreak(15); // Check if we need new page for footer
                pdf.setDrawColor(0, 150, 136); // Teal color
                pdf.setLineWidth(0.5);
                pdf.line(leftMargin, yPos, rightMargin, yPos);
                yPos += lineHeight;
                
                // END OF SERVICE
                checkPageBreak();
                pdf.setFontSize(10);
                pdf.setFont('helvetica', 'normal');
                pdf.setTextColor(0, 150, 136); // Teal color
                pdf.text('END OF SERVICE', rightMargin - 40, yPos);
                yPos += lineHeight;
                
                // Reset text color
                pdf.setTextColor(0, 0, 0);
                
                // Remark
                checkPageBreak();
                pdf.text('Remark:', leftMargin, yPos);
                yPos += lineHeight;
                checkPageBreak();
                pdf.text('-', leftMargin, yPos);
                
                // Save PDF
                pdf.save(`Tour_Itinerary_${tourId || 'text'}.pdf`);
                
                loadingMsg.remove();
                showSuccessMessage('Text itinerary downloaded successfully!');
            } catch (error) {
                console.error('Error generating text PDF:', error);
                loadingMsg.remove();
                showErrorMessage('Failed to generate text itinerary: ' + (error.message || 'Unknown error'));
            }
        }
        
        async function downloadAsExcelFormat() {
            // Check if jsPDF is loaded
            if (typeof window.jspdf === 'undefined') {
                showErrorMessage('PDF library not loaded. Please refresh the page and try again.');
                return;
            }
            
            // Show loading message
            const loadingMsg = showMessage('Generating Excel format itinerary...', 'info');
            
            try {
                const { jsPDF } = window.jspdf;
                
                // Get data from PHP variables
                const tourId = @json($tourId ?? '');
                const tourDetails = @json($tourDetails ?? null);
                const displayId = @json($tourDetails->display_id ?? '');
                const destination = @json($tourDetails->destination ?? '');
                const checkInTime = @json($tourDetails->check_in_time ?? null);
                const checkOutTime = @json($tourDetails->check_out_time ?? null);
                const pnrNumber = @json($tourDetails->pnr ?? $tourDetails->pnr_number ?? $tourId ?? '');
                const customerInfo = pdfCustomerInfo || [];
                const allServices = pdfAllServices || {};
                const userDmc = @json($user_dmc ?? null);
                const agentInfo = @json($agent_info ?? null);
                // Debug logging
                console.log('DMC Info:', userDmc);
                console.log('Agent Info:', agentInfo);
                
                // Logo is already converted to base64 data URL server-side (no CORS issues)
                const logoDataUrl = (userDmc && userDmc.logo) ? userDmc.logo : null;
                
                const pdf = new jsPDF('l', 'mm', 'a4'); // Landscape orientation for Excel-like format
                
                // Check if autotable is available (it should be if script is loaded)
                if (typeof pdf.autoTable === 'undefined') {
                    loadingMsg.remove();
                    showErrorMessage('Table library not loaded. Please refresh the page and try again.');
                    return;
                }
                
                // Page dimensions (landscape A4)
                const pageWidth = 297; // A4 landscape width in mm
                const pageHeight = 210; // A4 landscape height in mm
                const leftMargin = 10; // Increased from 5 to reduce usable width
                const rightMarginValue = 10; // Increased from 5 to reduce usable width
                const rightMargin = pageWidth - rightMarginValue; // Right edge position
                const topMargin = 5;
                const bottomMargin = pageHeight - 5;
                let yPos = topMargin;
                const lineHeight = 5;
                const cellPadding = 2;
                
                // Helper function to check and add new page if needed
                const checkPageBreak = (requiredSpace = lineHeight) => {
                    if (yPos + requiredSpace > bottomMargin) {
                        pdf.addPage();
                        yPos = topMargin;
                        return true;
                    }
                    return false;
                };
                
                // Helper function to safely draw rectangles with validation
                const safeRect = (x, y, width, height) => {
                    try {
                        const validX = (typeof x === 'number' && !isNaN(x) && isFinite(x)) ? Math.max(0, x) : 0;
                        const validY = (typeof y === 'number' && !isNaN(y) && isFinite(y)) ? Math.max(0, y) : 0;
                        const validWidth = (typeof width === 'number' && !isNaN(width) && isFinite(width) && width > 0) ? width : 1;
                        const validHeight = (typeof height === 'number' && !isNaN(height) && isFinite(height) && height > 0) ? height : 1;
                        pdf.rect(validX, validY, validWidth, validHeight);
                    } catch (error) {
                        console.error('Error drawing rect:', error, { x, y, width, height });
                    }
                };
                
                // Helper function to safely draw rounded rectangles with validation
                const safeRoundedRect = (x, y, width, height, radius = 2) => {
                    try {
                        const validX = (typeof x === 'number' && !isNaN(x) && isFinite(x)) ? Math.max(0, x) : 0;
                        const validY = (typeof y === 'number' && !isNaN(y) && isFinite(y)) ? Math.max(0, y) : 0;
                        const validWidth = (typeof width === 'number' && !isNaN(width) && isFinite(width) && width > 0) ? width : 1;
                        const validHeight = (typeof height === 'number' && !isNaN(height) && isFinite(height) && height > 0) ? height : 1;
                        const validRadius = (typeof radius === 'number' && !isNaN(radius) && isFinite(radius) && radius > 0) ? Math.min(radius, Math.min(validWidth, validHeight) / 2) : 2;
                        if (typeof pdf.roundedRect === 'function') {
                            pdf.roundedRect(validX, validY, validWidth, validHeight, validRadius, validRadius);
                        } else {
                            // Fallback to regular rect if roundedRect is not available
                            pdf.rect(validX, validY, validWidth, validHeight);
                        }
                    } catch (error) {
                        console.error('Error drawing rounded rect:', error, { x, y, width, height, radius });
                        // Fallback to regular rect
                        safeRect(x, y, width, height);
                    }
                };
                
                // Helper function to draw a cell with border and text (Excel-like)
                const drawCell = (x, y, width, height, text, options = {}) => {
                    const {
                        align = 'left',
                        bold = false,
                        fillColor = null,
                        fontSize = 9,
                        border = true,
                        cellPadding = 2
                    } = options;
                    
                    // Draw border (Excel-like grid lines)
                    if (border) {
                        pdf.setDrawColor(0, 0, 0);
                        pdf.setLineWidth(0.1);
                        // Draw all four sides
                        pdf.line(x, y, x + width, y); // Top
                        pdf.line(x + width, y, x + width, y + height); // Right
                        pdf.line(x + width, y + height, x, y + height); // Bottom
                        pdf.line(x, y + height, x, y); // Left
                    }
                    
                    // Fill background color if specified
                    if (fillColor) {
                        pdf.setFillColor(fillColor.r, fillColor.g, fillColor.b);
                        pdf.rect(x, y, width, height, 'F');
                    }
                    
                    // Draw text
                    if (text) {
                        const textStr = String(text || '');
                        pdf.setFontSize(fontSize);
                        pdf.setFont('helvetica', bold ? 'bold' : 'normal');
                        pdf.setTextColor(0, 0, 0);
                        
                        const textX = align === 'center' ? x + width / 2 : 
                                     align === 'right' ? x + width - cellPadding : 
                                     x + cellPadding;
                        const textY = y + height / 2 + fontSize / 3;
                        
                        // Simple text rendering - no rotation, no splitting
                        pdf.text(textStr, textX, textY, {
                            align: align === 'center' ? 'center' : align === 'right' ? 'right' : 'left',
                            maxWidth: width - (cellPadding * 2)
                        });
                    }
                };
                
                // Helper function to format date
                const formatDate = (date) => {
                    if (!date) return '';
                    try {
                        let d;
                        if (typeof date === 'string') {
                            // Handle YYYY-MM-DD format
                            if (date.match(/^\d{4}-\d{2}-\d{2}$/)) {
                                const parts = date.split('-');
                                d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                            } else {
                                d = new Date(date);
                            }
                        } else {
                            d = new Date(date);
                        }
                        if (isNaN(d.getTime())) return date.toString();
                        const day = d.getDate();
                        const month = d.toLocaleString('en-US', { month: 'short' });
                        const year = d.getFullYear();
                        return `${day} ${month} ${year}`;
                    } catch (e) {
                        return date.toString();
                    }
                };
                
                // HEADER SECTION - Matching Quotation Format
                // Logo on left, Title in center
                const logoSize = 15; // mm
                const logoX = leftMargin;
                const logoY = yPos;
                
                if (logoDataUrl) {
                    try {
                        pdf.addImage(logoDataUrl, 'PNG', logoX, logoY, logoSize, logoSize, undefined, 'FAST');
                    } catch (logoError) {
                        console.warn('Could not add logo to PDF:', logoError);
                    }
                }
                
                // Title: TOUR ITINERARY (centered)
                pdf.setFontSize(22);
                pdf.setFont('helvetica', 'bold');
                pdf.setTextColor(44, 62, 80);
                pdf.text('TOUR ITINERARY', pageWidth / 2, logoY + logoSize / 2 + 2, { align: 'center' });
                
                yPos += logoSize + 5; // Spacing after header
                
                // Draw border line below header
                pdf.setDrawColor(221, 221, 221);
                pdf.setLineWidth(0.5);
                pdf.line(leftMargin, yPos, pageWidth - rightMarginValue, yPos);
                yPos += 10; // Increased padding after header border
                
                // Company Contact Information Section
                const dmcName = (userDmc && userDmc.company_name) ? userDmc.company_name : 
                               (userDmc && userDmc.name) ? userDmc.name : 
                               'DMC Name';
                const dmcAddress = (userDmc && userDmc.address) ? userDmc.address : 'N/A';
                const dmcTel = (userDmc && userDmc.tel) ? userDmc.tel : 
                              (userDmc && userDmc.telephone) ? userDmc.telephone :
                              (userDmc && userDmc.phone) ? userDmc.phone : 'N/A';
                const dmcFax = (userDmc && userDmc.fax) ? userDmc.fax : 'N/A';
                const dmcEmail = (userDmc && userDmc.email) ? userDmc.email : 
                                (userDmc && userDmc.company_email) ? userDmc.company_email : 'N/A';
                
                pdf.setFontSize(12);
                pdf.setFont('helvetica', 'bold');
                pdf.setTextColor(44, 62, 80);
                pdf.text(dmcName, leftMargin, yPos);
                yPos += 5;
                
                pdf.setFontSize(10);
                pdf.setFont('helvetica', 'normal');
                pdf.setTextColor(33, 37, 41);
                let companyDetailsY = yPos;
                pdf.text(dmcAddress, leftMargin, companyDetailsY);
                companyDetailsY += 5;
                pdf.text('Tel: ' + dmcTel, leftMargin, companyDetailsY);
                if (dmcFax !== 'N/A') {
                    companyDetailsY += 5;
                    pdf.text('Fax: ' + dmcFax, leftMargin, companyDetailsY);
                }
                companyDetailsY += 5;
                pdf.text('Email: ' + dmcEmail, leftMargin, companyDetailsY);
                yPos = companyDetailsY + 5; // Increased padding after company info
                
                // Two Side-by-Side Panels - Matching Quotation Format
                const panelStartY = yPos;
                const gapBetweenPanels = 6;
                const availableWidth = pageWidth - leftMargin - rightMarginValue - gapBetweenPanels;
                const leftPanelWidth = availableWidth * 0.52; // 52% for left panel (matching quotation)
                const rightPanelWidth = availableWidth * 0.48; // 48% for right panel (matching quotation)
                
                // Panel styling constants (shared by both panels)
                const cornerRadius = 2.5; // mm - matching quotation border-radius: 10px
                const headerHeight = 8;
                
                // Left Panel: BOOKING & PROPOSAL DETAILS
                // Matching quotation format: Two internal columns (Left: Booking fields, Right: Proposal fields)
                const leftPanelX = leftMargin;
                
                // Build left panel body with 4 columns (2 internal columns, each with label and value)
                const leftPanelBody = [
                    [{content: 'BOOKING & PROPOSAL DETAILS', colSpan: 4, styles: {fontStyle: 'bold', fontSize: 10, textColor: [255, 255, 255], fillColor: [160, 174, 192], halign: 'center', cellPadding: {top: 3, bottom: 3, left: 2, right: 2}}}],
                    // Row 1: Booking ID (left) | Value | Proposal Date (right) | Value
                    [
                        {content: 'Booking ID:', styles: {fontStyle: 'normal', fontSize: 10, textColor: [100, 116, 139], fillColor: [245, 247, 248], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                        {content: (displayId || tourId || 'N/A'), styles: {fontStyle: 'bold', fontSize: 10, textColor: [15, 23, 42], fillColor: [255, 255, 255], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                        {content: 'Proposal Date:', styles: {fontStyle: 'normal', fontSize: 10, textColor: [100, 116, 139], fillColor: [245, 247, 248], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                        {content: formatDate(new Date()), styles: {fontStyle: 'bold', fontSize: 10, textColor: [15, 23, 42], fillColor: [255, 255, 255], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}}
                    ],
                    // Row 2: Lead Guest (left) | Value | Proposal Validity (right) | Value
                    [
                        {content: 'Lead Guest:', styles: {fontStyle: 'normal', fontSize: 10, textColor: [100, 116, 139], fillColor: [245, 247, 248], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                        {content: (customerInfo.fullName || 'N/A'), styles: {fontStyle: 'bold', fontSize: 10, textColor: [15, 23, 42], fillColor: [255, 255, 255], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                        {content: 'Proposal Validity:', styles: {fontStyle: 'normal', fontSize: 10, textColor: [100, 116, 139], fillColor: [245, 247, 248], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                        {content: 'N/A', styles: {fontStyle: 'bold', fontSize: 10, textColor: [15, 23, 42], fillColor: [255, 255, 255], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}}
                    ],
                    // Row 3: Adults (left) | Value | Proposal Sent By (right) | Value
                    [
                        {content: 'Adults:', styles: {fontStyle: 'normal', fontSize: 10, textColor: [100, 116, 139], fillColor: [245, 247, 248], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                        {content: (tourDetails && tourDetails.adult ? tourDetails.adult : (customerInfo.adults || customerInfo.adultCount || 0)).toString(), styles: {fontStyle: 'bold', fontSize: 12, textColor: [15, 23, 42], fillColor: [255, 255, 255], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                        {content: 'Proposal Sent By:', styles: {fontStyle: 'normal', fontSize: 10, textColor: [100, 116, 139], fillColor: [245, 247, 248], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                        {content: (userDmc && userDmc.name) ? userDmc.name : ((userDmc && userDmc.company_name) ? userDmc.company_name : 'N/A'), styles: {fontStyle: 'bold', fontSize: 10, textColor: [15, 23, 42], fillColor: [255, 255, 255], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}}
                    ],
                    // Row 4: Children (left) | Value | Empty | Empty
                    [
                        {content: 'Children:', styles: {fontStyle: 'normal', fontSize: 10, textColor: [100, 116, 139], fillColor: [245, 247, 248], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                        {content: (tourDetails && tourDetails.child ? tourDetails.child : (customerInfo.children || customerInfo.childCount || 0)).toString(), styles: {fontStyle: 'bold', fontSize: 12, textColor: [15, 23, 42], fillColor: [255, 255, 255], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                        {content: '', styles: {fontStyle: 'normal', fontSize: 10, textColor: [15, 23, 42], fillColor: [255, 255, 255], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                        {content: '', styles: {fontStyle: 'normal', fontSize: 10, textColor: [15, 23, 42], fillColor: [255, 255, 255], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}}
                    ],
                    // Row 5: Infants (left) | Value | Empty | Empty
                    [
                        {content: 'Infants:', styles: {fontStyle: 'normal', fontSize: 10, textColor: [100, 116, 139], fillColor: [245, 247, 248], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                        {content: (tourDetails && tourDetails.infant ? tourDetails.infant : (customerInfo.infants || customerInfo.infantCount || 0)).toString(), styles: {fontStyle: 'bold', fontSize: 12, textColor: [15, 23, 42], fillColor: [255, 255, 255], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                        {content: '', styles: {fontStyle: 'normal', fontSize: 10, textColor: [15, 23, 42], fillColor: [255, 255, 255], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                        {content: '', styles: {fontStyle: 'normal', fontSize: 10, textColor: [15, 23, 42], fillColor: [255, 255, 255], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}}
                    ]
                ];
                
                // Draw left panel background first (estimate height) with rounded corners
                const estimatedLeftHeight = 50;
                
                // Draw panel background with rounded corners
                pdf.setFillColor(245, 247, 248);
                pdf.setDrawColor(224, 224, 224);
                pdf.setLineWidth(0.1);
                safeRoundedRect(leftPanelX, panelStartY, leftPanelWidth, estimatedLeftHeight, cornerRadius);
                pdf.fillStroke();
                
                // Draw header background with rounded top corners
                pdf.setFillColor(160, 174, 192); // #a0aec0
                pdf.setDrawColor(160, 174, 192);
                pdf.setLineWidth(0.1);
                // Draw rounded rectangle for header (only top corners rounded)
                if (typeof pdf.roundedRect === 'function') {
                    pdf.roundedRect(leftPanelX, panelStartY, leftPanelWidth, headerHeight, cornerRadius, cornerRadius);
                    pdf.fillStroke();
                } else {
                    safeRect(leftPanelX, panelStartY, leftPanelWidth, headerHeight);
                    pdf.fillStroke();
                }
                
                pdf.autoTable({
                    startY: panelStartY,
                    margin: { left: leftPanelX, right: pageWidth - leftPanelX - leftPanelWidth },
                    theme: 'plain',
                    tableLineWidth: 0,
                    headStyles: { fillColor: [160, 174, 192], textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 10, lineWidth: 0 },
                    bodyStyles: { fillColor: false, textColor: [33, 37, 41], fontSize: 12, lineWidth: 0, cellPadding: {top: 3, bottom: 3, left: 2, right: 2} },
                    columnStyles: {
                        0: { cellWidth: leftPanelWidth * 0.22, halign: 'left' }, // Label column 1 (22%)
                        1: { cellWidth: leftPanelWidth * 0.28, halign: 'left' }, // Value column 1 (28%)
                        2: { cellWidth: leftPanelWidth * 0.22, halign: 'left' }, // Label column 2 (22%)
                        3: { cellWidth: leftPanelWidth * 0.28, halign: 'left' }  // Value column 2 (28%)
                    },
                    styles: { lineColor: [255, 255, 255], lineWidth: 0 },
                    body: leftPanelBody
                });
                
                const leftPanelEndY = pdf.lastAutoTable ? pdf.lastAutoTable.finalY : panelStartY;
                
                // Right Panel: TRAVEL COMPANY / AGENT
                // Matching quotation format: Two columns (Left: Company/Agency details, Right: Agent details)
                // Direct display without key-value pairs, matching quotation format
                const rightPanelX = leftPanelX + leftPanelWidth + gapBetweenPanels;
                
                // Build right panel body with 2 columns (matching quotation format)
                const rightPanelBody = [
                    [{content: 'TRAVEL COMPANY / AGENT', colSpan: 2, styles: {fontStyle: 'bold', fontSize: 10, textColor: [255, 255, 255], fillColor: [160, 174, 192], halign: 'center', cellPadding: {top: 3, bottom: 3, left: 2, right: 2}}}],
                ];
                
                if (agentInfo) {
                    const companyName = (agentInfo.company_name || agentInfo.name || '');
                    const agentName = (agentInfo.agent_name || '');
                    
                    // Row 1: Company name (bold, larger) | Agent name (bold, larger)
                    if (companyName || agentName) {
                        rightPanelBody.push([
                            {content: companyName || '', styles: {fontStyle: 'bold', fontSize: 12, textColor: [44, 62, 80], fillColor: [255, 255, 255], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                            {content: agentName || '', styles: {fontStyle: 'bold', fontSize: 12, textColor: [44, 62, 80], fillColor: [255, 255, 255], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}}
                        ]);
                    }
                    
                    // Build company details with inline labels (matching quotation format)
                    const companyDetails = [];
                    if (agentInfo.address) {
                        companyDetails.push(agentInfo.address);
                    }
                    if (agentInfo.contact_person) {
                        companyDetails.push('Contact Person: ' + agentInfo.contact_person);
                    }
                    if (agentInfo.phone) {
                        companyDetails.push('Tel: ' + agentInfo.phone);
                    }
                    if (agentInfo.email) {
                        companyDetails.push('Email: ' + agentInfo.email);
                    }
                    
                    // Build agent details with inline labels
                    const agentDetails = [];
                    if (agentInfo.agent_address) {
                        agentDetails.push(agentInfo.agent_address);
                    }
                    if (agentInfo.agent_phone) {
                        agentDetails.push('Tel: ' + agentInfo.agent_phone);
                    }
                    if (agentInfo.agent_email) {
                        agentDetails.push('Email: ' + agentInfo.agent_email);
                    }
                    
                    // Add details rows
                    const maxDetails = Math.max(companyDetails.length, agentDetails.length);
                    for (let i = 0; i < maxDetails; i++) {
                        rightPanelBody.push([
                            {content: companyDetails[i] || '', styles: {fontStyle: 'normal', fontSize: 10, textColor: [33, 37, 41], fillColor: [255, 255, 255], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}},
                            {content: agentDetails[i] || '', styles: {fontStyle: 'normal', fontSize: 10, textColor: [33, 37, 41], fillColor: [255, 255, 255], cellPadding: {top: 2, bottom: 2, left: 1, right: 1}}}
                        ]);
                    }
                } else {
                    rightPanelBody.push([
                        {content: 'N/A', colSpan: 2, styles: {fontStyle: 'normal', fontSize: 10, textColor: [33, 37, 41], fillColor: [255, 255, 255], cellPadding: {top: 3, bottom: 3, left: 2, right: 2}}}
                    ]);
                }
                
                // Draw right panel background first (estimate height) with rounded corners
                const estimatedRightHeight = 50;
                
                // Draw panel background with rounded corners
                pdf.setFillColor(245, 247, 248);
                pdf.setDrawColor(224, 224, 224);
                pdf.setLineWidth(0.1);
                safeRoundedRect(rightPanelX, panelStartY, rightPanelWidth, estimatedRightHeight, cornerRadius);
                pdf.fillStroke();
                
                // Draw header background with rounded top corners
                pdf.setFillColor(160, 174, 192); // #a0aec0
                pdf.setDrawColor(160, 174, 192);
                pdf.setLineWidth(0.1);
                // Draw rounded rectangle for header (only top corners rounded)
                if (typeof pdf.roundedRect === 'function') {
                    pdf.roundedRect(rightPanelX, panelStartY, rightPanelWidth, headerHeight, cornerRadius, cornerRadius);
                    pdf.fillStroke();
                } else {
                    safeRect(rightPanelX, panelStartY, rightPanelWidth, headerHeight);
                    pdf.fillStroke();
                }
                
                pdf.autoTable({
                    startY: panelStartY,
                    margin: { left: rightPanelX, right: rightMarginValue },
                    theme: 'plain',
                    tableLineWidth: 0,
                    headStyles: { fillColor: [160, 174, 192], textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 10, lineWidth: 0 },
                    bodyStyles: { fillColor: false, textColor: [33, 37, 41], fontSize: 12, lineWidth: 0, cellPadding: {top: 2, bottom: 2, left: 1, right: 1} },
                    columnStyles: {
                        0: { cellWidth: rightPanelWidth * 0.5, halign: 'left' }, // Left column (50%)
                        1: { cellWidth: rightPanelWidth * 0.5, halign: 'left' }  // Right column (50%)
                    },
                    styles: { lineColor: [255, 255, 255], lineWidth: 0 },
                    body: rightPanelBody
                });
                
                yPos = Math.max(leftPanelEndY, pdf.lastAutoTable ? pdf.lastAutoTable.finalY : panelStartY) + 8; // Increased padding after panels
                
                // Travel Summary Section - Matching Quotation Format
                const travelSummaryY = yPos;
                const travelSummaryHeight = 20; // Increased height for better padding
                const travelSummaryWidth = pageWidth - leftMargin - rightMarginValue;
                
                // Draw travel summary background container (Fill and Draw)
                pdf.setDrawColor(224, 224, 224);
                pdf.setFillColor(255, 255, 255);
                pdf.setLineWidth(0.1);
                pdf.rect(leftMargin, travelSummaryY, travelSummaryWidth, travelSummaryHeight, 'FD');
                
                // Travel Summary Header (Fill only, drawn AFTER container)
                pdf.setFillColor(160, 174, 192); // #a0aec0
                pdf.rect(leftMargin, travelSummaryY, travelSummaryWidth, 6, 'F');
                
                pdf.setFontSize(12);
                pdf.setFont('helvetica', 'bold');
                pdf.setTextColor(44, 62, 80);
                pdf.text('TRAVEL SUMMARY', leftMargin + travelSummaryWidth / 2, travelSummaryY + 4, { align: 'center' });
                
                // Calculate duration
                let duration = 'N/A';
                if (checkInTime && checkOutTime) {
                    try {
                        const start = new Date(checkInTime);
                        const end = new Date(checkOutTime);
                        if (!isNaN(start.getTime()) && !isNaN(end.getTime())) {
                            const diffTime = Math.abs(end - start);
                            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                            duration = diffDays.toString() + ' Days';
                        }
                    } catch (e) {
                        duration = 'N/A';
                    }
                }
                
                // Format dates with day names (e.g., "Tuesday- 30/12/2025")
                const formatDateWithDay = (dateStr) => {
                    if (!dateStr) return 'N/A';
                    try {
                        const date = new Date(dateStr);
                        if (isNaN(date.getTime())) return dateStr;
                        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        const dayName = dayNames[date.getDay()];
                        const day = String(date.getDate()).padStart(2, '0');
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const year = date.getFullYear();
                        return `${dayName}- ${day}/${month}/${year}`;
                    } catch (e) {
                        return dateStr;
                    }
                };
                
                const travelDateFromFormatted = checkInTime ? formatDateWithDay(checkInTime) : 'N/A';
                const travelDateToFormatted = checkOutTime ? formatDateWithDay(checkOutTime) : 'N/A';
                
                // Travel Summary Content - Three columns
                const travelContentY = travelSummaryY + 12; // Increased top padding
                const itemWidth = travelSummaryWidth / 3;
                
                pdf.setFontSize(10);
                pdf.setFont('helvetica', 'bold');
                pdf.setTextColor(44, 62, 80);
                
                // Destination
                pdf.text('Destination: ' + (destination || 'N/A'), leftMargin + itemWidth / 2, travelContentY, { align: 'center' });
                
                // Travel Dates
                pdf.text(travelDateFromFormatted + ' - ' + travelDateToFormatted, leftMargin + itemWidth + itemWidth / 2, travelContentY, { align: 'center' });
                
                // Duration
                pdf.text('Duration: ' + duration, leftMargin + itemWidth * 2 + itemWidth / 2, travelContentY, { align: 'center' });
                
                yPos = travelSummaryY + travelSummaryHeight + 8; // Increased padding after travel summary
                
                // Passenger Details Table - Matching Quotation Format
                const passengerData = customerInfo.passengers || [];
                const leadGuestName = customerInfo.fullName || customerInfo.name || '';
                
                // Create passenger array if not available
                let passengers = passengerData;
                if (!passengers || passengers.length === 0) {
                    if (leadGuestName) {
                        passengers = [{
                            salutation: customerInfo.salutation || 'Mr',
                            first_name: leadGuestName,
                            passenger_type: customerInfo.passenger_type || 'Adult',
                            gender: customerInfo.gender || 'M',
                            mobile_phone: customerInfo.phone || '—',
                            email: customerInfo.email || '—'
                        }];
                    }
                }
                
                if (passengers && passengers.length > 0) {
                    // Check if we're on the first page and ensure passenger table fits
                    // Use getNumberOfPages() to check if we're still on page 1
                    const currentPageCount = pdf.internal.getNumberOfPages();
                    if (currentPageCount === 1) {
                        // Estimate passenger table height: header (8mm) + rows (8mm per row) + padding (8mm)
                        const estimatedTableHeight = 8 + (passengers.length * 8) + 8;
                        const spaceNeeded = estimatedTableHeight + 6; // Add some buffer
                        
                        // If not enough space on first page, move to next page
                        // This ensures passenger details is the last content on first page
                        if (yPos + spaceNeeded > bottomMargin) {
                            pdf.addPage();
                            yPos = topMargin;
                        }
                    }
                    
                    // Calculate passenger table width to match travel summary width
                    const passengerTableWidth = pageWidth - leftMargin - rightMarginValue;
                    
                    const passengerTableBody = [];
                    
                    // Header row
                    passengerTableBody.push([
                        {content: 'Salutation', styles: {fontStyle: 'bold', fillColor: [160, 174, 192], textColor: [44, 62, 80], fontSize: 11}},
                        {content: 'Name', styles: {fontStyle: 'bold', fillColor: [160, 174, 192], textColor: [44, 62, 80], fontSize: 11}},
                        {content: 'Passenger Type', styles: {fontStyle: 'bold', fillColor: [160, 174, 192], textColor: [44, 62, 80], fontSize: 11}},
                        {content: 'Gender', styles: {fontStyle: 'bold', fillColor: [160, 174, 192], textColor: [44, 62, 80], fontSize: 11}},
                        {content: 'Mobile Phone', styles: {fontStyle: 'bold', fillColor: [160, 174, 192], textColor: [44, 62, 80], fontSize: 11}},
                        {content: 'Email', styles: {fontStyle: 'bold', fillColor: [160, 174, 192], textColor: [44, 62, 80], fontSize: 11}}
                    ]);
                    
                    // Passenger rows
                    passengers.forEach(passenger => {
                        passengerTableBody.push([
                            {content: (passenger.salutation || 'Mr'), styles: {fillColor: [255, 255, 255], textColor: [33, 37, 41], fontSize: 11}},
                            {content: (passenger.first_name || passenger.name || '—'), styles: {fillColor: [255, 255, 255], textColor: [33, 37, 41], fontSize: 11}},
                            {content: (passenger.passenger_type || 'Adult'), styles: {fillColor: [255, 255, 255], textColor: [33, 37, 41], fontSize: 11}},
                            {content: (passenger.gender || 'M'), styles: {fillColor: [255, 255, 255], textColor: [33, 37, 41], fontSize: 11}},
                            {content: (passenger.mobile_phone || passenger.phone || '—'), styles: {fillColor: [255, 255, 255], textColor: [33, 37, 41], fontSize: 11}},
                            {content: (passenger.email || '—'), styles: {fillColor: [255, 255, 255], textColor: [33, 37, 41], fontSize: 11}}
                        ]);
                    });
                    
                    // Calculate column widths as percentages of passengerTableWidth
                    // Original proportions: 25, 60, 35, 25, 50, 60 (total: 255)
                    // Convert to percentages and apply to passengerTableWidth
                    const totalOriginalWidth = 255;
                    pdf.autoTable({
                        startY: yPos,
                        margin: { left: leftMargin, right: rightMarginValue },
                        theme: 'grid',
                        headStyles: { 
                            fillColor: [160, 174, 192], 
                            textColor: [44, 62, 80], 
                            fontStyle: 'bold', 
                            fontSize: 11,
                            lineWidth: 0.1
                        },
                        bodyStyles: { 
                            fillColor: false, 
                            textColor: [33, 37, 41], 
                            fontSize: 11,
                            lineWidth: 0.1,
                            cellPadding: 2.5
                        },
                        columnStyles: {
                            0: { cellWidth: (25 / totalOriginalWidth) * passengerTableWidth, halign: 'center' },
                            1: { cellWidth: (60 / totalOriginalWidth) * passengerTableWidth, halign: 'left' },
                            2: { cellWidth: (35 / totalOriginalWidth) * passengerTableWidth, halign: 'center' },
                            3: { cellWidth: (25 / totalOriginalWidth) * passengerTableWidth, halign: 'center' },
                            4: { cellWidth: (50 / totalOriginalWidth) * passengerTableWidth, halign: 'center' },
                            5: { cellWidth: (60 / totalOriginalWidth) * passengerTableWidth, halign: 'left' }
                        },
                        styles: { lineColor: [224, 224, 224], lineWidth: 0.1 },
                        head: [],
                        body: passengerTableBody
                    });
                    
                    yPos = pdf.lastAutoTable.finalY + 4;
                }
                
                // Ensure itinerary starts on page 2 - add new page if still on page 1
                const currentPageCount = pdf.internal.getNumberOfPages();
                if (currentPageCount === 1) {
                    // Add a new page so itinerary starts on page 2
                    pdf.addPage();
                    yPos = topMargin;
                }
                
                // Generate all dates between check-in and check-out
                let allTourDates = [];
                if (checkInTime && checkOutTime) {
                    const startDate = new Date(checkInTime);
                    const endDate = new Date(checkOutTime);
                    if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
                        const currentDate = new Date(startDate);
                        while (currentDate <= endDate) {
                            const year = currentDate.getFullYear();
                            const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                            const day = String(currentDate.getDate()).padStart(2, '0');
                            allTourDates.push(`${year}-${month}-${day}`);
                            currentDate.setDate(currentDate.getDate() + 1);
                        }
                    }
                } else {
                    allTourDates = Object.keys(allServices).sort();
                }
                
                // Normalize service dates
                const normalizeDate = (dateStr) => {
                    if (!dateStr) return null;
                    try {
                        const dateObj = new Date(dateStr);
                        if (!isNaN(dateObj.getTime())) {
                            const year = dateObj.getFullYear();
                            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                            const day = String(dateObj.getDate()).padStart(2, '0');
                            return `${year}-${month}-${day}`;
                        }
                    } catch (e) {
                        const match = dateStr.match(/(\d{4})[-\s](\d{1,2})[-\s](\d{1,2})/);
                        if (match) {
                            return `${match[1]}-${match[2].padStart(2, '0')}-${match[3].padStart(2, '0')}`;
                        }
                    }
                    return dateStr;
                };
                
                const normalizedServices = {};
                Object.keys(allServices).forEach(serviceDate => {
                    const normalizedDate = normalizeDate(serviceDate);
                    if (normalizedDate) {
                        if (!normalizedServices[normalizedDate]) {
                            normalizedServices[normalizedDate] = [];
                        }
                        normalizedServices[normalizedDate].push(...allServices[serviceDate]);
                    }
                });
                
                // Debug: Log services by date
                console.log('Normalized services:', normalizedServices);
                console.log('All tour dates:', allTourDates);
                
                // Process each day
                let dayNumber = 1;
                allTourDates.forEach(date => {
                    const normalizedDate = normalizeDate(date) || date;
                    const services = normalizedServices[normalizedDate] || [];
                    // Debug: Log services for this date
                    if (services.length > 0) {
                        console.log(`Date ${date} (normalized: ${normalizedDate}) has ${services.length} services:`, services.map(s => s.type || 'unknown'));
                    }
                    
                    // Day Header using autotable (Premium Light Blue Colors)
                    const dateText = formatDate(date);
                    pdf.autoTable({
                        startY: yPos,
                        margin: { left: leftMargin, right: pageWidth - rightMargin },
                        theme: 'grid',
                        headStyles: { 
                            fillColor: [108, 117, 125], 
                            textColor: [255, 255, 255], 
                            fontStyle: 'bold', 
                            fontSize: 11 
                        },
                        bodyStyles: { fillColor: false, textColor: [33, 37, 41], fontSize: 10 },
                        columnStyles: {
                            0: { cellWidth: 30 },
                            1: { cellWidth: pageWidth - leftMargin - rightMargin - 30, halign: 'right' }
                        },
                        head: [['Day ' + dayNumber, `Date: ${dateText}`]],
                        body: []
                    });
                    
                    yPos = pdf.lastAutoTable.finalY + 3;
                    
                    // Increment day number after rendering header
                    dayNumber++;
                    
                    // Group services by type for this day
                    const servicesByType = {
                        entry_port: [],
                        hotel: [],
                        breakfast: [],
                        lunch: [],
                        dinner: [],
                        city_tour: [],
                        attraction: [],
                        local_transfer: [],
                        guide: [],
                        exit_port: []
                    };
                    services.forEach(service => {
                        const serviceType = (service.type || '').toLowerCase();
                        if (serviceType === 'entry_port' || serviceType === 'entry port') {
                            servicesByType.entry_port.push(service);
                        } else if (serviceType === 'hotel') {
                            servicesByType.hotel.push(service);
                        } else if (serviceType === 'restaurant') {
                            const mealType = (service.mealType || '').toLowerCase();
                            if (mealType.includes('breakfast')) {
                                servicesByType.breakfast.push(service);
                            } else if (mealType.includes('lunch')) {
                                servicesByType.lunch.push(service);
                            } else if (mealType.includes('dinner')) {
                                servicesByType.dinner.push(service);
                            } else {
                                servicesByType.breakfast.push(service); // Default to breakfast
                            }
                        } else if (serviceType === 'attraction') {
                            servicesByType.attraction.push(service);
                        } else if (serviceType === 'travel_point' || serviceType === 'point to point' || serviceType === 'travel_hourly' || serviceType === 'local_transport') {
                            servicesByType.local_transfer.push(service);
                        } else if (serviceType === 'guide') {
                            servicesByType.guide.push(service);
                        } else if (serviceType === 'exit_port' || serviceType === 'exit port') {
                            servicesByType.exit_port.push(service);
                            console.log('service in exit port condition-------->>>', service);
                        }
                    });
                    
                    // Render Airport Arrival Transfer (only for first day)
                    if (dayNumber === 2 && servicesByType.entry_port.length > 0) {
                        yPos = renderAirportArrivalTransfer(pdf, servicesByType.entry_port, leftMargin, yPos, pageWidth, rightMargin);
                    }
                    
                    // Render Accommodation
                    if (servicesByType.hotel.length > 0) {
                        yPos = renderAccommodation(pdf, servicesByType.hotel, leftMargin, yPos, pageWidth, rightMargin);
                    }
                    
                    // Render Breakfast
                    if (servicesByType.breakfast.length > 0) {
                        yPos = renderMealService(pdf, 'Breakfast', servicesByType.breakfast, leftMargin, yPos, pageWidth, rightMargin);
                    }
                    
                    // Render Lunch
                    if (servicesByType.lunch.length > 0) {
                        yPos = renderMealService(pdf, 'Lunch', servicesByType.lunch, leftMargin, yPos, pageWidth, rightMargin);
                    }
                    
                    // Render City Tour (if any guide services)
                    if (servicesByType.guide.length > 0) {
                        yPos = renderCityTour(pdf, servicesByType.guide, leftMargin, yPos, pageWidth, rightMargin);
                    }
                    
                    // Render Attractions
                    servicesByType.attraction.forEach((attraction, idx) => {
                        yPos = renderAttraction(pdf, `Attraction ${idx + 1}`, attraction, leftMargin, yPos, pageWidth, rightMargin);
                    });
                    
                    // Render Dinner
                    if (servicesByType.dinner.length > 0) {
                        yPos = renderMealService(pdf, 'Dinner', servicesByType.dinner, leftMargin, yPos, pageWidth, rightMargin);
                    }
                    
                    // Render Local Transfer
                    if (servicesByType.local_transfer.length > 0) {
                        yPos = renderLocalTransfer(pdf, servicesByType.local_transfer, leftMargin, yPos, pageWidth, rightMargin);
                    }
                    
                    // Render Airport Departure Transfer (only for last day)
                    if (date === allTourDates[allTourDates.length - 1] && servicesByType.exit_port.length > 0) {
                        console.log('servicesByType.exit_port-------->>>', servicesByType.exit_port);
                        yPos = renderAirportDepartureTransfer(pdf, servicesByType.exit_port, leftMargin, yPos, pageWidth, rightMargin);
                    }
                    
                    yPos += 5; // Spacing between days
                });
                
                // Exclusions Section (Pink header with black border, matching quotation format)
                checkPageBreak(35);
                const exclusionsTableBody = [
                    [{content: 'Exclusions :', colSpan: 2, styles: {fontStyle: 'bold', fontSize: 12, textColor: [0, 0, 0], fillColor: [255, 182, 193], cellPadding: {top: 3, bottom: 3, left: 5, right: 5}, minCellHeight: 8}}],
                    [{content: ' ', colSpan: 2, styles: {fillColor: [255, 255, 255], textColor: [0, 0, 0], fontSize: 11, minCellHeight: 35, cellPadding: 20}}]
                ];
                
                pdf.autoTable({
                    startY: yPos,
                    margin: { left: leftMargin, right: rightMarginValue },
                    theme: 'grid',
                    tableLineWidth: 0.7, // 2px border (0.7mm)
                    bodyStyles: { 
                        fillColor: [255, 255, 255], // White
                        textColor: [0, 0, 0], 
                        fontSize: 11,
                        lineWidth: 0.7,
                        lineColor: [0, 0, 0], // Black border
                        cellPadding: 20,
                        minCellHeight: 35
                    },
                    columnStyles: {
                        0: { cellWidth: (pageWidth - leftMargin - rightMarginValue) / 2 },
                        1: { cellWidth: (pageWidth - leftMargin - rightMarginValue) / 2 }
                    },
                    styles: { lineColor: [0, 0, 0], lineWidth: 0.7 },
                    body: exclusionsTableBody
                });
                
                yPos = pdf.lastAutoTable.finalY + 10;
                
                // Terms & Conditions Section (Pink header with black border, matching quotation format)
                checkPageBreak(35);
                const termsTableBody = [
                    [{content: 'Terms & Conditions :', colSpan: 2, styles: {fontStyle: 'bold', fontSize: 12, textColor: [0, 0, 0], fillColor: [255, 182, 193], cellPadding: {top: 3, bottom: 3, left: 5, right: 5}, minCellHeight: 8}}],
                    [{content: ' ', colSpan: 2, styles: {fillColor: [255, 255, 255], textColor: [0, 0, 0], fontSize: 11, minCellHeight: 35, cellPadding: 20}}]
                ];
                
                pdf.autoTable({
                    startY: yPos,
                    margin: { left: leftMargin, right: rightMarginValue },
                    theme: 'grid',
                    tableLineWidth: 0.7, // 2px border (0.7mm)
                    bodyStyles: { 
                        fillColor: [255, 255, 255], // White
                        textColor: [0, 0, 0], 
                        fontSize: 11,
                        lineWidth: 0.7,
                        lineColor: [0, 0, 0], // Black border
                        cellPadding: 20,
                        minCellHeight: 35
                    },
                    columnStyles: {
                        0: { cellWidth: (pageWidth - leftMargin - rightMarginValue) / 2 },
                        1: { cellWidth: (pageWidth - leftMargin - rightMarginValue) / 2 }
                    },
                    styles: { lineColor: [0, 0, 0], lineWidth: 0.7 },
                    body: termsTableBody
                });
                
                yPos = pdf.lastAutoTable.finalY + 5;
                
                // Save PDF
                pdf.save(`Tour_Itinerary_Excel_${tourId || 'format'}.pdf`);
                
                loadingMsg.remove();
                showSuccessMessage('Excel format itinerary downloaded successfully!');
            } catch (error) {
                console.error('Error generating Excel format PDF:', error);
                loadingMsg.remove();
                showErrorMessage('Failed to generate Excel format itinerary: ' + (error.message || 'Unknown error'));
            }
        }
        
        // Helper functions to render different service sections using autotable
        function renderAirportArrivalTransfer(pdf, services, leftMargin, yPos, pageWidth, rightMargin) {
            services.forEach(service => {
                console.log("service data in renderAirportArrivalTransfer: ", service);
                // Extract all possible fields from data
                const pickup = service.pickup || service.data?.entrypickup || service.data?.entry_pickup || service.data?.pickup || '';
                const dropoff = service.dropoff || service.data?.entrydropoff || service.data?.entry_dropoff || service.data?.dropoff || '';
                // Get vehicle name from vehicle_driver_data (from jobsheet) first, then fallback to other sources
                const vehicle = service.vehicleName || service.vehicle || service.data?.vehicles_name || service.data?.vehicle || '';
                const transferType = service.transferType || service.data?.type || service.data?.transfer_options?.type || '';
                const pickupTime = service.data?.entrytime || service.pickupTime || service.data?.pickupdate || '';
                
                // Flight Information (if available)
                const originCity = service.data?.originCity || service.data?.origin_city || '';
                const departureTime = service.data?.departureTime || service.data?.departure_time || '';
                const originAirport = service.data?.originAirport || service.data?.origin_airport || '';
                const originFlightNumber = service.data?.originFlightNumber || service.data?.origin_flight_number || '';
                const originPNR = service.data?.originPNR || service.data?.origin_pnr || '';
                
                const arrivalCity = service.data?.arrivalCity || service.data?.arrival_city || service.data?.city || '';
                const arrivalTime = service.data?.arrivalTime || service.data?.arrival_time || '';
                const arrivalAirport = service.data?.arrivalAirport || service.data?.arrival_airport || '';
                const arrivalFlightNumber = service.data?.arrivalFlightNumber || service.data?.arrival_flight_number || '';
                const arrivalPNR = service.data?.arrivalPNR || service.data?.arrival_pnr || '';
                
                // Transfer Details - Get from serviceInfo first (populated from jobsheet/vehicles/drivers), then fallback to data
                const terminal = service.data?.terminal || '';
                const travelTime = service.data?.travelTime || service.data?.travel_time || '';
                const driverName = service.driverName || service.data?.driverName || service.data?.driver_name || '';
                const driverPhone = service.driverPhone || service.data?.driverPhone || service.data?.driver_phone || '';
                const vehicleNumber = service.vehicleNumber || service.data?.vehicleNumber || service.data?.vehicle_number || '';
                
                // Capacity Details - Get from serviceInfo first (populated from jobsheet/vehicles/drivers), then fallback to data
                const maxPassengerCapacity = service.maxPassengerCapacity || service.data?.maxPassengerCapacity || service.data?.max_passenger_capacity || service.data?.seating_capacity || '';
                const maxLuggageCapacity = service.data?.maxLuggageCapacity || service.data?.max_luggage_capacity || '';
                const childSeatAvailable = service.data?.childSeatAvailable || service.data?.child_seat_available || '';
                
                // Additional fields
                const city = service.data?.city || '';
                const country = service.data?.country || '';
                const adults = service.data?.adults || '';
                const children = service.data?.children || '';
                const bookingDate = service.data?.bookingDate || service.date || '';
                const remarks = service.data?.remarks || service.data?.specialRequests || '';
                
                // Format date
                const formatDateForDisplay = (dateStr) => {
                    if (!dateStr) return '';
                    try {
                        const date = new Date(dateStr);
                        const day = date.getDate();
                        const month = date.toLocaleString('en-US', { month: 'short' });
                        const year = date.getFullYear();
                        return `${day} ${month} ${year}`;
                    } catch (e) {
                        return dateStr;
                    }
                };
                
                // Format time (remove seconds if present, handle AM/PM format)
                const formatTime = (timeStr) => {
                    if (!timeStr) return '';
                    // If it's already in HH:MM format, return as is
                    if (timeStr.match(/^\d{2}:\d{2}$/)) {
                        return timeStr.substring(0, 5);
                    }
                    // If it's in "04:00 AM" format, return as is
                    if (timeStr.match(/\d{1,2}:\d{2}\s*(AM|PM)/i)) {
                        return timeStr;
                    }
                    return timeStr;
                };
                
                // Build table body - 8 columns (4 parameter-value pairs per row)
                const tableBody = [];
                
                // Flight Information - Origin Row
                tableBody.push([
                    'Origin City (from) :', originCity || 'N/A',
                    'Departure Time :', formatTime(departureTime) || 'N/A',
                    'Origin Airport Name & Code :', originAirport || 'N/A',
                    'Origin Flight Number :', originFlightNumber || 'N/A'
                ]);
                
                // Flight Information - Arrival Row (Arrival Time same as Pick up time)
                tableBody.push([
                    'Arrival City (to) :', arrivalCity || 'N/A',
                    'Arrival Time :', formatTime(pickupTime) || 'N/A',
                    'Arrival Airport Name & Code :', arrivalAirport || 'N/A',
                    'Arrival Flight Number :', arrivalFlightNumber || 'N/A'
                ]);
                
                // PNR and Transfer Details Row
                tableBody.push([
                    'PNR :', originPNR || arrivalPNR || 'N/A',
                    'Pick up time :', formatTime(pickupTime) || 'N/A',
                    'Transfer Type :', transferType || 'N/A',
                    'Vehicle Type :', vehicle || 'N/A'
                ]);
                
                // Transfer Details - Second Row
                tableBody.push([
                    'Pick up from :', pickup || 'N/A',
                    'Terminal :', terminal || 'N/A',
                    'Vehicle Number :', vehicleNumber || 'N/A',
                    'Max Passenger Capacity :', maxPassengerCapacity ? maxPassengerCapacity.toString() : 'N/A'
                ]);
                
                // Transfer Details - Third Row
                tableBody.push([
                    'Drop off to :', dropoff || 'N/A',
                    'Travel Time :', travelTime || 'N/A',
                    'Max Luggage Capacity :', maxLuggageCapacity ? maxLuggageCapacity.toString() : 'N/A',
                    'Child Seat Available :', childSeatAvailable ? childSeatAvailable.toString() : 'N/A'
                ]);
                
                // Driver and Passenger Details Row
                tableBody.push([
                    'Driver Name :', driverName || 'N/A',
                    'Driver Phone :', driverPhone || 'N/A',
                    'Adults :', adults ? adults.toString() : '0',
                    'Children :', children ? children.toString() : '0'
                ]);
                
                // Location and Booking Details Row
                tableBody.push([
                    'City :', city || 'N/A',
                    'Country :', country || 'N/A',
                    'Booking Date :', bookingDate ? formatDateForDisplay(bookingDate) : 'N/A',
                    '', ''
                ]);
                
                // Remarks Row - Last row, full width (spans all 8 columns)
                tableBody.push([
                    {content: 'Remarks :', colSpan: 1, styles: {fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80]}},
                    {content: remarks || 'N/A', colSpan: 7, styles: {fillColor: [255, 255, 255], textColor: [33, 37, 41]}}
                ]);
                
                // Calculate column widths
                const availableWidth = pageWidth - leftMargin - (pageWidth - rightMargin);
                const columnWidth = availableWidth / 8;
                
                pdf.autoTable({
                    startY: yPos,
                    margin: { left: leftMargin, right: pageWidth - rightMargin },
                    theme: 'grid',
                    headStyles: { 
                        fillColor: [108, 117, 125], 
                        textColor: [255, 255, 255], 
                        fontStyle: 'bold', 
                        fontSize: 10 
                    },
                    bodyStyles: { 
                        fillColor: false, 
                        textColor: [33, 37, 41], 
                        fontSize: 8 
                    },
                    columnStyles: {
                        0: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        1: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                        2: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        3: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                        4: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        5: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                        6: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        7: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth }
                    },
                    head: [[{content: 'Airport Arrival Transfer', colSpan: 8, styles: {halign: 'left', textColor: [255, 255, 255]}}]],
                    body: tableBody
                });
                yPos = pdf.lastAutoTable.finalY + 3;
            });
            return yPos;
        }
        
        function renderAccommodation(pdf, hotels, leftMargin, yPos, pageWidth, rightMargin) {
            
            hotels.forEach((hotel) => {
                // Extract hotel data - handle multiple possible field paths
                const hotelName = hotel.name || 
                                 hotel.data?.hotelDetails?.hotel_name || 
                                 hotel.data?.hotelname || 
                                 hotel.data?.name || 
                                 'N/A';
                const confirmationNo = hotel.confirmationNo || 
                                      hotel.data?.confirmationNo || 
                                      hotel.data?.confirmation_no || 
                                      hotel.data?.confirmation_number || 
                                      '';
                const hotelAddress = hotel.data?.hotelDetails?.location || 
                                   hotel.data?.hotelDetails?.address || 
                                   hotel.data?.address || 
                                   hotel.data?.location ||
                                   '';
                
                // Extract check-in/check-out dates and times
                const checkInDate = hotel.checkIn || hotel.data?.bookingDate?.[0] || '';
                const checkOutDate = hotel.checkOut || hotel.data?.bookingDate?.[1] || '';
                const checkInTime = hotel.data?.hotelDetails?.checkInTime || '15:00:00';
                const checkOutTime = hotel.data?.hotelDetails?.checkOutTime || '11:00:00';
                
                // Format dates
                const formatDateForDisplay = (dateStr) => {
                    if (!dateStr) return '';
                    try {
                        const date = new Date(dateStr);
                        const day = date.getDate();
                        const month = date.toLocaleString('en-US', { month: 'short' });
                        const year = date.getFullYear();
                        return `${day} ${month} ${year}`;
                    } catch (e) {
                        return dateStr;
                    }
                };
                
                // Format time (remove seconds if present)
                const formatTime = (timeStr) => {
                    if (!timeStr) return '';
                    return timeStr.substring(0, 5); // HH:MM format
                };
                
                // Extract room information
                const rooms = hotel.data?.rooms || [];
                // Calculate total rooms - sum up number_of_rooms from each room
                const totalRooms = rooms.length > 0 
                    ? rooms.reduce((sum, room) => sum + (parseInt(room.number_of_rooms) || 1), 0)
                    : (hotel.roomCount || 1);
                
                // Build table body - matching screenshot format with 8 columns (4 parameter-value pairs)
                // Fill all 8 columns before starting a new row
                const tableBody = [];
                
                // First row: Hotel Name, Confirmation Number, Hotel Address, Check in Date (4 pairs = 8 columns)
                tableBody.push(
                    ['Hotel Name :', hotelName, 'Hotel Confirmation number :', confirmationNo || 'N/A', 'Hotel Address :', hotelAddress, 'Check in Date :', formatDateForDisplay(checkInDate)]
                );
                
                // Second row: Check in time, Check out date, Check out time, No. of Rooms (4 pairs = 8 columns)
                tableBody.push(
                    ['Check in time :', formatTime(checkInTime), 'Check out date :', formatDateForDisplay(checkOutDate), 'Check out time :', formatTime(checkOutTime), 'No. of Rooms :', totalRooms.toString()]
                );
                
                // Room details - up to 3 rooms
                const roomsToShow = rooms.length > 0 ? rooms.slice(0, 3) : [];
                
                // Third row: First Room details (if available)
                if (roomsToShow.length === 0) {
                    // If no rooms data, show at least one room with available info
                    const roomType = hotel.roomType || 'Standard Room';
                    tableBody.push(
                        ['Room 1 -', roomType, 'Occupancy :', '', 'Inclusions :', '', '', '']
                    );
                } else {
                    // First room details
                    const firstRoom = roomsToShow[0];
                    const roomType = firstRoom.room_type || hotel.roomType || 'Standard Room';
                    
                    // Extract occupancy from beds
                    let occupancy = '';
                    if (firstRoom.beds && firstRoom.beds.length > 0) {
                        const maxOccupancy = firstRoom.beds[0].max_occupancy || firstRoom.beds[0].head_count || '';
                        occupancy = maxOccupancy ? `${maxOccupancy} persons` : '';
                    }
                    
                    // Extract inclusions/meals from beds
                    let inclusions = '';
                    if (firstRoom.beds && firstRoom.beds.length > 0) {
                        const mealTypes = firstRoom.beds[0].mealTypes || [];
                        if (mealTypes.length > 0) {
                            inclusions = mealTypes.join(', ');
                        }
                    }
                    
                    tableBody.push(
                        ['Room 1 -', roomType, 'Occupancy :', occupancy, 'Inclusions :', inclusions, '', '']
                    );
                    
                    // Additional rooms (if any) - each on a new row, filling all 8 columns
                    if (roomsToShow.length > 1) {
                        for (let index = 1; index < roomsToShow.length; index++) {
                            const room = roomsToShow[index];
                            const roomType = room.room_type || hotel.roomType || 'Standard Room';
                            
                            // Extract occupancy from beds
                            let occupancy = '';
                            if (room.beds && room.beds.length > 0) {
                                const maxOccupancy = room.beds[0].max_occupancy || room.beds[0].head_count || '';
                                occupancy = maxOccupancy ? `${maxOccupancy} persons` : '';
                            }
                            
                            // Extract inclusions/meals from beds
                            let inclusions = '';
                            if (room.beds && room.beds.length > 0) {
                                const mealTypes = room.beds[0].mealTypes || [];
                                if (mealTypes.length > 0) {
                                    inclusions = mealTypes.join(', ');
                                }
                            }
                            
                            tableBody.push(
                                [`Room ${index + 1} -`, roomType, 'Occupancy :', occupancy, 'Inclusions :', inclusions, '', '']
                            );
                        }
                    }
                }
                
                // Calculate column widths - smaller cells to fit 8 columns
                const availableWidth = pageWidth - leftMargin - (pageWidth - rightMargin);
                const columnWidth = availableWidth / 8;
                
                pdf.autoTable({
                    startY: yPos,
                    margin: { left: leftMargin, right: pageWidth - rightMargin },
                    theme: 'grid',
                    headStyles: { 
                        fillColor: [108, 117, 125], 
                        textColor: [255, 255, 255], 
                        fontStyle: 'bold', 
                        fontSize: 10 
                    },
                    bodyStyles: { 
                        fillColor: false, 
                        textColor: [33, 37, 41], 
                        fontSize: 8 
                    },
                    columnStyles: {
                        0: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth }, // Parameter column
                        1: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth }, // Value column
                        2: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth }, // Parameter column
                        3: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth }, // Value column
                        4: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth }, // Parameter column
                        5: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth }, // Value column
                        6: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth }, // Parameter column
                        7: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth }  // Value column
                    },
                    head: [[{content: 'Accommodation', colSpan: 8, styles: {halign: 'left', textColor: [255, 255, 255]}}]],
                    body: tableBody
                });
                yPos = pdf.lastAutoTable.finalY + 3;
            });
            return yPos;
        }
        
        function renderMealService(pdf, mealType, services, leftMargin, yPos, pageWidth, rightMargin) {
            console.log("Please check the meal service data in renderMealService function", services);
            services.forEach(service => {
                // Extract ALL fields from data - KEEP ALL EXISTING FIELDS FROM OLDER VERSION
                const restaurantName = service.name || service.data?.restaurantName || service.data?.name || '';
                const visitTime = service.visitTime || service.data?.visitTime || service.data?.time || '';
                const mealSpecificType = service.mealSpecificType || service.data?.mealSpecificType || '';
                const transferType = service.transferType || service.data?.transfer_options?.type || service.data?.type || '';
                const pickupLocation = service.pickupLocation || service.data?.transfer_options?.pickup_location_name || '';
                const way = service.way || service.data?.transfer_options?.way || service.data?.way || '';
                
                // Vehicle details (from older version)
                const vehicleDetails = service.data?.vehicle_details || {};
                const vehicleName = vehicleDetails?.vehicle_name || vehicleDetails?.vehicle_type || service.vehicle || service.data?.vehicle || '';
                const vehicleType = vehicleDetails?.vehicle_type || vehicleDetails?.vehicle_name || service.vehicle || service.data?.vehicle || '';
                const seatingCapacity = vehicleDetails?.seating_capacity || '';
                const vehicleId = vehicleDetails?.vehicle_id || '';
                
                // Booking details (from older version)
                const bookingDate = service.data?.bookingDate || service.date || '';
                const restaurantId = service.data?.restaurantId || '';
                
                // Meal description/menu items (from older version)
                const mealDescription = service.data?.MealDescription || [];
                let menuItems = '';
                if (Array.isArray(mealDescription) && mealDescription.length > 0) {
                    menuItems = mealDescription.map(item => item.name || item.item_name || '').filter(Boolean).join(', ');
                }
                
                // Pickup and Dropoff details (from current version)
                const pickupFrom = pickupLocation || '';
                const pickupTime = visitTime || '';
                const dropoffTo = restaurantName || '';
                const dropoffTime = visitTime || '';
                
                // Additional vehicle details - Get from serviceInfo first (populated from jobsheet/vehicles/drivers), then fallback to data
                const vehicleNumber = service.vehicleNumber || service.data?.vehicleNumber || service.data?.vehicle_number || '';
                const childSeatAvailable = service.data?.childSeatAvailable || service.data?.child_seat_available || '';
                
                // Driver details - Get from serviceInfo first (populated from jobsheet/vehicles/drivers), then fallback to data
                const driverName = service.driverName || service.data?.driverName || service.data?.driver_name || '';
                const driverPhone = service.driverPhone || service.data?.driverPhone || service.data?.driver_phone || '';
                
                // Max Passenger Capacity - Get from serviceInfo first (populated from jobsheet/vehicles/drivers), then fallback to data
                const maxPassengerCapacity = service.maxPassengerCapacity || service.data?.maxPassengerCapacity || service.data?.max_passenger_capacity || seatingCapacity || '';
                
                // Passenger details (from current version)
                const adultCount = service.data?.adultCount || service.data?.adults || '';
                const childCount = service.data?.childCount || service.data?.children || '';
                const infantCount = service.data?.infantCount || service.data?.infants || '';
                
                // Ticket details (from current version)
                const ticketDetails = service.data?.ticketDetails || service.data?.ticket_details || '';
                const numberOfTickets = service.data?.numberOfTickets || service.data?.number_of_tickets || '';
                
                // Restaurant details (from older version)
                const restaurantOpen = service.data?.restaurantOpen || service.data?.restaurant_open || '';
                const restaurantClose = service.data?.restaurantClose || service.data?.restaurant_close || '';
                const openDays = service.data?.openDays || service.data?.open_days || '';
                const closeDays = service.data?.closeDays || service.data?.close_days || '';
                
                // Booking details
                const confirmationNumber = service.data?.confirmationNumber || service.data?.confirmation_no || service.data?.confirmation_number || '';
                const specialRequests = service.data?.specialRequests || '';
                
                // Format date
                const formatDateForDisplay = (dateStr) => {
                    if (!dateStr) return '';
                    try {
                        const date = new Date(dateStr);
                        const day = date.getDate();
                        const month = date.toLocaleString('en-US', { month: 'short' });
                        const year = date.getFullYear();
                        return `${day} ${month} ${year}`;
                    } catch (e) {
                        return dateStr;
                    }
                };
                
                // Format time (remove seconds if present, handle AM/PM format)
                const formatTime = (timeStr) => {
                    if (!timeStr) return '';
                    // If it's already in HH:MM format, return as is
                    if (timeStr.match(/^\d{2}:\d{2}$/)) {
                        return timeStr.substring(0, 5);
                    }
                    // If it's in "9:00 AM" format, return as is
                    if (timeStr.match(/\d{1,2}:\d{2}\s*(AM|PM)/i)) {
                        return timeStr;
                    }
                    return timeStr;
                };
                
                // Build table body - 8 columns (4 parameter-value pairs per row)
                const tableBody = [];
                
                // First row: Restaurant Name, Visit Time, Meal Type, Meal Specific Type (from older version)
                tableBody.push([
                    'Restaurant Name :', restaurantName || 'N/A',
                    'Visit Time :', formatTime(visitTime) || 'N/A',
                    'Meal Type :', mealType || 'N/A',
                    'Meal Specific Type :', mealSpecificType || 'N/A'
                ]);
                
                // Second row: Transfer Type, Pickup Location, Way, Vehicle Type (from older version)
                tableBody.push([
                    'Transfer Type :', transferType || 'N/A',
                    'Pickup Location :', pickupLocation || 'N/A',
                    'Way :', way || 'N/A',
                    'Vehicle Type :', vehicleType || vehicleName || 'N/A'
                ]);
                
                // Third row: Pick up from, Pick up time, Drop off to, Drop off time (from current version)
                tableBody.push([
                    'Pick up from :', pickupFrom || 'N/A',
                    'Pick up time :', formatTime(pickupTime) || 'N/A',
                    'Drop off to :', dropoffTo || 'N/A',
                    'Drop off time :', formatTime(dropoffTime) || 'N/A'
                ]);
                
                // Fourth row: Vehicle Name, Seating Capacity, Vehicle ID, Booking Date (from older version)
                tableBody.push([
                    'Vehicle Name :', vehicleName || 'N/A',
                    'Seating Capacity :', seatingCapacity ? seatingCapacity.toString() : 'N/A',
                    'Vehicle ID :', vehicleId ? vehicleId.toString() : 'N/A',
                    'Booking Date :', bookingDate ? formatDateForDisplay(bookingDate) : 'N/A'
                ]);
                
                // Fifth row: Max Passenger Capacity, Child Seat Available, Vehicle Number, Confirmation Number (from current version)
                tableBody.push([
                    'Max Passenger Capacity :', maxPassengerCapacity ? maxPassengerCapacity.toString() : (seatingCapacity ? seatingCapacity.toString() : 'N/A'),
                    'Child Seat Available :', childSeatAvailable ? childSeatAvailable.toString() : 'N/A',
                    'Vehicle Number :', vehicleNumber || 'N/A',
                    'Confirmation Number :', confirmationNumber || 'N/A'
                ]);
                
                // Sixth row: Driver Name, Driver Phone, Adults, Children (from current version + older version)
                tableBody.push([
                    'Driver Name :', driverName || 'N/A',
                    'Driver Phone :', driverPhone || 'N/A',
                    'Adults :', adultCount ? adultCount.toString() : '0',
                    'Children :', childCount ? childCount.toString() : '0'
                ]);
                
                // Seventh row: Ticket Details, No. of Tickets, No. of Adults, No. of Children (from current version)
                tableBody.push([
                    'Ticket Details :', ticketDetails || 'N/A',
                    'No. of Tickets :', numberOfTickets ? numberOfTickets.toString() : 'N/A',
                    'No. of Adults :', adultCount ? adultCount.toString() : '0',
                    'No. of Children :', childCount ? childCount.toString() : '0'
                ]);
                
                // Eighth row: No. of Infants, Restaurant ID, Menu Items, Restaurant Open (from current + older version)
                tableBody.push([
                    'No. of Infants :', infantCount ? infantCount.toString() : '0',
                    'Restaurant ID :', restaurantId ? restaurantId.toString() : 'N/A',
                    'Menu Items :', menuItems || 'N/A',
                    'Restaurant Open :', formatTime(restaurantOpen) || 'N/A'
                ]);
                
                // Ninth row: Restaurant Close, Open days, Close days (from older version)
                tableBody.push([
                    'Restaurant Close :', formatTime(restaurantClose) || 'N/A',
                    'Open days :', openDays || 'N/A',
                    'Close days :', closeDays || 'N/A',
                    '', ''
                ]);
                
                // Remarks Row - Last row, full width (spans all 8 columns)
                tableBody.push([
                    {content: 'Remarks :', colSpan: 1, styles: {fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80]}},
                    {content: specialRequests || 'N/A', colSpan: 7, styles: {fillColor: [255, 255, 255], textColor: [33, 37, 41]}}
                ]);
                
                // Calculate column widths
                const availableWidth = pageWidth - leftMargin - (pageWidth - rightMargin);
                const columnWidth = availableWidth / 8;
                
                pdf.autoTable({
                    startY: yPos,
                    margin: { left: leftMargin, right: pageWidth - rightMargin },
                    theme: 'grid',
                    headStyles: { 
                        fillColor: [108, 117, 125], 
                        textColor: [255, 255, 255], 
                        fontStyle: 'bold', 
                        fontSize: 10 
                    },
                    bodyStyles: { 
                        fillColor: false, 
                        textColor: [33, 37, 41], 
                        fontSize: 8 
                    },
                    columnStyles: {
                        0: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        1: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                        2: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        3: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                        4: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        5: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                        6: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        7: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth }
                    },
                    head: [[{content: mealType, colSpan: 8, styles: {halign: 'left', textColor: [255, 255, 255]}}]],
                    body: tableBody
                });
                yPos = pdf.lastAutoTable.finalY + 3;
            });
            return yPos;
        }
        
        function renderCityTour(pdf, guides, leftMargin, yPos, pageWidth, rightMargin) {
            guides.forEach(guide => {
                console.log("Please check the city tour data in renderCityTour function", guides);
                
                // Extract all possible fields from data
                const guideName = guide.name || guide.data?.guide_name || guide.data?.guideName || '';
                const hours = guide.hours || guide.data?.hours || '';
                const entryTime = guide.entryTime || guide.data?.entrytime || guide.data?.entryTime || '';
                const languages = guide.languages || guide.data?.languages || [];
                const languagesStr = Array.isArray(languages) ? languages.join(', ') : '';
                
                // Pickup details
                const pickupFrom = guide.data?.entrypickup || guide.data?.pickup || '';
                const pickupTime = entryTime || guide.data?.pickupdate || '';
                
                // Dropoff details
                const dropoffTo = guide.data?.dropoff || guide.data?.dropoffLocation || '';
                const dropoffTime = guide.data?.dropoffTime || '';
                
                // Transfer details
                const transferType = guide.data?.transferType || guide.data?.type || '';
                const vehicleType = guide.data?.vehicleType || guide.data?.vehicle_type || guide.data?.vehicle || '';
                const vehicleNumber = guide.data?.vehicleNumber || guide.data?.vehicle_number || '';
                const seatingCapacity = guide.data?.seatingCapacity || guide.data?.seating_capacity || guide.data?.maxPassengerCapacity || '';
                const childSeatAvailable = guide.data?.childSeatAvailable || guide.data?.child_seat_available || '';
                
                // Driver details
                const driverName = guide.data?.driverName || guide.data?.driver_name || '';
                const driverPhone = guide.data?.driverPhone || guide.data?.driver_phone || '';
                
                // Passenger details
                const adultCount = guide.data?.adults || '';
                const childCount = guide.data?.children || '';
                
                // Booking details
                const bookingDate = guide.data?.bookingDate || guide.date || '';
                const guideId = guide.data?.guide_id || '';
                const city = guide.data?.city || '';
                const country = guide.data?.country || '';
                const confirmationNumber = guide.data?.confirmationNumber || guide.data?.confirmation_no || guide.data?.confirmation_number || '';
                const specialRequests = guide.data?.specialRequests || '';
                
                // Format date
                const formatDateForDisplay = (dateStr) => {
                    if (!dateStr) return '';
                    try {
                        const date = new Date(dateStr);
                        const day = date.getDate();
                        const month = date.toLocaleString('en-US', { month: 'short' });
                        const year = date.getFullYear();
                        return `${day} ${month} ${year}`;
                    } catch (e) {
                        return dateStr;
                    }
                };
                
                // Format time (remove seconds if present, handle AM/PM format)
                const formatTime = (timeStr) => {
                    if (!timeStr) return '';
                    // Convert to string to ensure .match() works
                    const timeStrConverted = String(timeStr);
                    // If it's already in HH:MM format, return as is
                    if (timeStrConverted.match(/^\d{2}:\d{2}$/)) {
                        return timeStrConverted.substring(0, 5);
                    }
                    // If it's in "1:00 AM" format, return as is
                    if (timeStrConverted.match(/\d{1,2}:\d{2}\s*(AM|PM)/i)) {
                        return timeStrConverted;
                    }
                    return timeStrConverted;
                };
                
                // Build table body - 8 columns (4 parameter-value pairs per row)
                const tableBody = [];
                
                // First row: Guide Name, Tour Duration, Entry Time, Languages
                tableBody.push([
                    'Guide Name :', guideName || 'N/A',
                    'Tour Duration :', hours ? `${hours} hour(s)` : 'N/A',
                    'Entry Time :', formatTime(entryTime) || 'N/A',
                    'Languages :', languagesStr || 'N/A'
                ]);
                
                // Second row: Pick up from, Pick up time, Transfer Type, Vehicle Type
                tableBody.push([
                    'Pick up from :', pickupFrom || 'N/A',
                    'Pick up time :', formatTime(pickupTime) || 'N/A',
                    'Transfer Type :', transferType || 'N/A',
                    'Vehicle Type :', vehicleType || 'N/A'
                ]);
                
                // Third row: Drop off to, Drop off time, Max Passenger Capacity, Child Seat Available
                tableBody.push([
                    'Drop off to :', dropoffTo || 'N/A',
                    'Drop off time :', formatTime(dropoffTime) || 'N/A',
                    'Max Passenger Capacity :', seatingCapacity ? seatingCapacity.toString() : 'N/A',
                    'Child Seat Available :', childSeatAvailable ? childSeatAvailable.toString() : 'N/A'
                ]);
                
                // Fourth row: Vehicle Number, Driver Name, Driver Phone, Guide ID
                tableBody.push([
                    'Vehicle Number :', vehicleNumber || 'N/A',
                    'Driver Name :', driverName || 'N/A',
                    'Driver Phone :', driverPhone || 'N/A',
                    'Guide ID :', guideId ? guideId.toString() : 'N/A'
                ]);
                
                // Fifth row: Adults, Children, City, Country
                tableBody.push([
                    'Adults :', adultCount ? adultCount.toString() : '0',
                    'Children :', childCount ? childCount.toString() : '0',
                    'City :', city || 'N/A',
                    'Country :', country || 'N/A'
                ]);
                
                // Sixth row: Booking Date, Confirmation Number
                if (bookingDate || confirmationNumber) {
                    tableBody.push([
                        'Booking Date :', bookingDate ? formatDateForDisplay(bookingDate) : 'N/A',
                        'Confirmation Number :', confirmationNumber || 'N/A',
                        '', '',
                        '', ''
                    ]);
                }
                
                // Remarks Row - Last row, full width (spans all 8 columns)
                tableBody.push([
                    {content: 'Remarks :', colSpan: 1, styles: {fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80]}},
                    {content: specialRequests || 'N/A', colSpan: 7, styles: {fillColor: [255, 255, 255], textColor: [33, 37, 41]}}
                ]);
                
                // Calculate column widths
                const availableWidth = pageWidth - leftMargin - (pageWidth - rightMargin);
                const columnWidth = availableWidth / 8;
                
                pdf.autoTable({
                    startY: yPos,
                    margin: { left: leftMargin, right: pageWidth - rightMargin },
                    theme: 'grid',
                    headStyles: { 
                        fillColor: [108, 117, 125], 
                        textColor: [255, 255, 255], 
                        fontStyle: 'bold', 
                        fontSize: 10 
                    },
                    bodyStyles: { 
                        fillColor: false, 
                        textColor: [33, 37, 41], 
                        fontSize: 8 
                    },
                    columnStyles: {
                        0: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        1: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                        2: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        3: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                        4: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        5: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                        6: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        7: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth }
                    },
                    head: [[{content: 'Tour Guide', colSpan: 8, styles: {halign: 'left', textColor: [255, 255, 255]}}]],
                    body: tableBody
                });
                yPos = pdf.lastAutoTable.finalY + 3;
            });
            return yPos;
        }
        
        function renderAttraction(pdf, title, attraction, leftMargin, yPos, pageWidth, rightMargin) {
            console.log("Please check the attraction data in renderAttraction function", attraction);
            
            // Extract all possible fields from data
            const attractionName = attraction.name || attraction.data?.AttractionName || attraction.data?.name || '';
            const visitTime = attraction.visitTime || attraction.data?.visitTime || attraction.data?.time || '';
            const ticketName = attraction.ticketName || attraction.data?.ticketName || '';
            const transferType = attraction.transferType || attraction.data?.transfer_options?.type || attraction.data?.type || '';
            const pickupLocation = attraction.pickupLocation || attraction.data?.transfer_options?.pickup_location_name || '';
            
            // Pickup and Dropoff details
            const pickupFrom = pickupLocation || '';
            const pickupTime = visitTime || '';
            const dropoffTo = attractionName || '';
            const dropoffTime = visitTime || '';
            
            // Vehicle details
            const vehicleDetails = attraction.data?.vehicle_details || {};
            const vehicleName = vehicleDetails?.vehicle_name || vehicleDetails?.vehicle_type || attraction.vehicle || attraction.data?.vehicle || '';
            const vehicleType = vehicleDetails?.vehicle_type || vehicleDetails?.vehicle_name || attraction.vehicle || attraction.data?.vehicle || '';
            const seatingCapacity = attraction.maxPassengerCapacity || vehicleDetails?.seating_capacity || attraction.data?.seatingCapacity || attraction.data?.seating_capacity || '';
            const vehicleNumber = attraction.vehicleNumber || attraction.data?.vehicleNumber || attraction.data?.vehicle_number || '';
            const childSeatAvailable = attraction.data?.childSeatAvailable || attraction.data?.child_seat_available || '';
            
            // Driver details - Get from vehicle_driver_data first (populated from jobsheet/vehicles/drivers), then fallback to data
            const driverName = attraction.driverName || attraction.data?.driverName || attraction.data?.driver_name || '';
            const driverPhone = attraction.driverPhone || attraction.data?.driverPhone || attraction.data?.driver_phone || '';
            
            // Passenger details
            const adultCount = attraction.data?.adults || attraction.data?.adultCount || '';
            const childCount = attraction.data?.children || attraction.data?.childCount || '';
            const infantCount = attraction.data?.infants || attraction.data?.infantCount || '';
            
            // Ticket details
            const ticketDetails = ticketName || attraction.data?.ticketDetails || attraction.data?.ticket_details || '';
            const numberOfTickets = attraction.data?.numberOfTickets || attraction.data?.number_of_tickets || '';
            
            // Attraction details
            const attractionOpen = attraction.data?.attractionOpen || attraction.data?.attraction_open || attraction.data?.openTime || '';
            const attractionClose = attraction.data?.attractionClose || attraction.data?.attraction_close || attraction.data?.closeTime || '';
            const openDays = attraction.data?.openDays || attraction.data?.open_days || '';
            const closeDays = attraction.data?.closeDays || attraction.data?.close_days || '';
            
            // Booking details
            const bookingDate = attraction.data?.bookingDate || attraction.date || '';
            const city = attraction.data?.city || '';
            const country = attraction.data?.country || '';
            const confirmationNumber = attraction.data?.confirmationNumber || attraction.data?.confirmation_no || attraction.data?.confirmation_number || '';
            const specialRequests = attraction.data?.specialRequests || '';
            
            // Format date
            const formatDateForDisplay = (dateStr) => {
                if (!dateStr) return '';
                try {
                    const date = new Date(dateStr);
                    const day = date.getDate();
                    const month = date.toLocaleString('en-US', { month: 'short' });
                    const year = date.getFullYear();
                    return `${day} ${month} ${year}`;
                } catch (e) {
                    return dateStr;
                }
            };
            
            // Format time (remove seconds if present, handle AM/PM format)
            const formatTime = (timeStr) => {
                if (!timeStr) return '';
                // If it's already in HH:MM format, return as is
                if (timeStr.match(/^\d{2}:\d{2}$/)) {
                    return timeStr.substring(0, 5);
                }
                // If it's in "9:00 AM" format, return as is
                if (timeStr.match(/\d{1,2}:\d{2}\s*(AM|PM)/i)) {
                    return timeStr;
                }
                return timeStr;
            };
            
            // Build table body - 8 columns (4 parameter-value pairs per row)
            const tableBody = [];
            
            // First row: Attraction Name, Visit Time, Ticket Name, Transfer Type
            tableBody.push([
                'Attraction Name :', attractionName || 'N/A',
                'Visit Time :', formatTime(visitTime) || 'N/A',
                'Ticket Name :', ticketName || 'N/A',
                'Transfer Type :', transferType || 'N/A'
            ]);
            
            // Second row: Pick up from, Pick up time, Vehicle Type, Max Passenger Capacity
            tableBody.push([
                'Pick up from :', pickupFrom || 'N/A',
                'Pick up time :', formatTime(pickupTime) || 'N/A',
                'Vehicle Type :', vehicleType || vehicleName || 'N/A',
                'Max Passenger Capacity :', seatingCapacity ? seatingCapacity.toString() : 'N/A'
            ]);
            
            // Third row: Drop off to, Drop off time, Vehicle Number, Child Seat Available
            tableBody.push([
                'Drop off to :', dropoffTo || 'N/A',
                'Drop off time :', formatTime(dropoffTime) || 'N/A',
                'Vehicle Number :', vehicleNumber || 'N/A',
                'Child Seat Available :', childSeatAvailable ? childSeatAvailable.toString() : 'N/A'
            ]);
            
            // Fourth row: Driver Name, Driver Phone, Ticket Details, No. of Tickets
            tableBody.push([
                'Driver Name :', driverName || 'N/A',
                'Driver Phone :', driverPhone || 'N/A',
                'Ticket Details :', ticketDetails || 'N/A',
                'No. of Tickets :', numberOfTickets ? numberOfTickets.toString() : 'N/A'
            ]);
            
            // Fifth row: No. of Adults, No. of Children, No. of Infants, Attraction Open
            tableBody.push([
                'No. of Adults :', adultCount ? adultCount.toString() : '0',
                'No. of Children :', childCount ? childCount.toString() : '0',
                'No. of Infants :', infantCount ? infantCount.toString() : '0',
                'Attraction Open :', formatTime(attractionOpen) || 'N/A'
            ]);
            
            // Sixth row: Attraction Close, Open days, Close days, Booking Date
            tableBody.push([
                'Attraction Close :', formatTime(attractionClose) || 'N/A',
                'Open days :', openDays || 'N/A',
                'Close days :', closeDays || 'N/A',
                'Booking Date :', bookingDate ? formatDateForDisplay(bookingDate) : 'N/A'
            ]);
            
            // Seventh row: City, Country, Confirmation Number
            if (city || country || confirmationNumber) {
                tableBody.push([
                    'City :', city || 'N/A',
                    'Country :', country || 'N/A',
                    'Confirmation Number :', confirmationNumber || 'N/A',
                    '', ''
                ]);
            }
            
            // Remarks Row - Last row, full width (spans all 8 columns)
            tableBody.push([
                {content: 'Remarks :', colSpan: 1, styles: {fontStyle: 'bold', fillColor: [240, 240, 240]}},
                {content: specialRequests || 'N/A', colSpan: 7, styles: {fillColor: [255, 255, 255]}}
            ]);
            
            // Calculate column widths
            const availableWidth = pageWidth - leftMargin - (pageWidth - rightMargin);
            const columnWidth = availableWidth / 8;
            
            pdf.autoTable({
                startY: yPos,
                margin: { left: leftMargin, right: pageWidth - rightMargin },
                theme: 'grid',
                headStyles: { 
                    fillColor: [108, 117, 125], 
                    textColor: [33, 37, 41], 
                    fontStyle: 'bold', 
                    fontSize: 10 
                },
                bodyStyles: { 
                    fillColor: false, 
                    textColor: [33, 37, 41], 
                    fontSize: 8 
                },
                columnStyles: {
                    0: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                    1: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                    2: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                    3: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                    4: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                    5: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                    6: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                    7: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth }
                },
                head: [[{content: title, colSpan: 8, styles: {halign: 'left', textColor: [255, 255, 255]}}]],
                body: tableBody
            });
            yPos = pdf.lastAutoTable.finalY + 3;
            return yPos;
        }
        
        function renderLocalTransfer(pdf, services, leftMargin, yPos, pageWidth, rightMargin) {
            services.forEach(service => {
                console.log("Please check the local transfer data in renderLocalTransfer function", services);
                
                // Extract all possible fields from data
                const pickup = service.pickup || service.data?.entrypickup || service.data?.pickup || '';
                const dropoff = service.dropoff || service.data?.entrydropoff || service.data?.dropoff || service.data?.dropoffLocation || '';
                const pickupTime = service.data?.entrytime || service.pickupTime || service.data?.pickupdate || '';
                // Get vehicle name from vehicle_driver_data (from jobsheet) first, then fallback to other sources
                const vehicle = service.vehicleName || service.vehicle || service.data?.vehicles_name || service.data?.vehicle || '';
                const transferType = service.transferType || service.data?.type || '';
                const selectedHours = service.selectedHours || service.data?.selectedHours || '';
                const serviceType = (service.data?.travel_type || service.data?.type || '').toLowerCase();
                const transferTypeLabel = serviceType.includes('hourly') ? 'Hourly' : 'Point to Point';
                
                // Vehicle details
                const vehicleDetails = service.data?.vehicle_details || {};
                const vehicleName = vehicleDetails?.vehicle_name || vehicleDetails?.vehicle_type || vehicle || '';
                const vehicleType = vehicleDetails?.vehicle_type || vehicleDetails?.vehicle_name || vehicle || '';
                const seatingCapacity = vehicleDetails?.seating_capacity || '';
                const vehicleNumber = service.data?.vehicleNumber || service.data?.vehicle_number || '';
                const vehicleId = service.data?.vehicles_id || vehicleDetails?.vehicle_id || '';
                const childSeatAvailable = service.data?.childSeatAvailable || service.data?.child_seat_available || '';
                
                // Driver details
                const driverName = service.data?.driverName || service.data?.driver_name || '';
                const driverPhone = service.data?.driverPhone || service.data?.driver_phone || '';
                
                // Passenger details
                const adultCount = service.data?.adults || '';
                const childCount = service.data?.children || '';
                const infantCount = service.data?.infants || service.data?.infantCount || '';
                
                // Booking details
                const bookingDate = service.data?.bookingDate || service.date || '';
                const city = service.data?.city || '';
                const country = service.data?.country || '';
                const confirmationNumber = service.data?.confirmationNumber || service.data?.confirmation_no || service.data?.confirmation_number || '';
                const specialRequests = service.data?.specialRequests || '';
                
                // Format date
                const formatDateForDisplay = (dateStr) => {
                    if (!dateStr) return '';
                    try {
                        const date = new Date(dateStr);
                        const day = date.getDate();
                        const month = date.toLocaleString('en-US', { month: 'short' });
                        const year = date.getFullYear();
                        return `${day} ${month} ${year}`;
                    } catch (e) {
                        return dateStr;
                    }
                };
                
                // Format time (remove seconds if present, handle AM/PM format)
                const formatTime = (timeStr) => {
                    if (!timeStr) return '';
                    // If it's already in HH:MM format, return as is
                    if (timeStr.match(/^\d{2}:\d{2}$/)) {
                        return timeStr.substring(0, 5);
                    }
                    // If it's in "10:00 AM" format, return as is
                    if (timeStr.match(/\d{1,2}:\d{2}\s*(AM|PM)/i)) {
                        return timeStr;
                    }
                    return timeStr;
                };
                
                // Build table body - 8 columns (4 parameter-value pairs per row)
                const tableBody = [];
                
                // First row: Transfer Type Label, Pick up from, Pick up time, Vehicle Type
                tableBody.push([
                    'Transfer Type :', transferTypeLabel || transferType || 'N/A',
                    'Pick up from :', pickup || 'N/A',
                    'Pick up time :', formatTime(pickupTime) || 'N/A',
                    'Vehicle Type :', vehicleType || vehicleName || 'N/A'
                ]);
                
                // Second row: Drop off to, Drop off time, Max Passenger Capacity, Child Seat Available
                tableBody.push([
                    'Drop off to :', dropoff || 'N/A',
                    'Drop off time :', formatTime(pickupTime) || 'N/A', // Usually same as pickup time
                    'Max Passenger Capacity :', seatingCapacity ? seatingCapacity.toString() : 'N/A',
                    'Child Seat Available :', childSeatAvailable ? childSeatAvailable.toString() : 'N/A'
                ]);
                
                // Third row: Vehicle Number, Vehicle ID, Selected Hours, Driver Name
                tableBody.push([
                    'Vehicle Number :', vehicleNumber || 'N/A',
                    'Vehicle ID :', vehicleId ? vehicleId.toString() : 'N/A',
                    'Selected Hours :', selectedHours ? selectedHours.toString() : 'N/A',
                    'Driver Name :', driverName || 'N/A'
                ]);
                
                // Fourth row: Driver Phone, No. of Adults, No. of Children, No. of Infants
                tableBody.push([
                    'Driver Phone :', driverPhone || 'N/A',
                    'No. of Adults :', adultCount ? adultCount.toString() : '0',
                    'No. of Children :', childCount ? childCount.toString() : '0',
                    'No. of Infants :', infantCount ? infantCount.toString() : '0'
                ]);
                
                // Fifth row: City, Country, Booking Date, Confirmation Number
                tableBody.push([
                    'City :', city || 'N/A',
                    'Country :', country || 'N/A',
                    'Booking Date :', bookingDate ? formatDateForDisplay(bookingDate) : 'N/A',
                    'Confirmation Number :', confirmationNumber || 'N/A'
                ]);
                
                // Remarks Row - Last row, full width (spans all 8 columns)
                tableBody.push([
                    {content: 'Remarks :', colSpan: 1, styles: {fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80]}},
                    {content: specialRequests || 'N/A', colSpan: 7, styles: {fillColor: [255, 255, 255], textColor: [33, 37, 41]}}
                ]);
                
                // Calculate column widths
                const availableWidth = pageWidth - leftMargin - (pageWidth - rightMargin);
                const columnWidth = availableWidth / 8;
                
                pdf.autoTable({
                    startY: yPos,
                    margin: { left: leftMargin, right: pageWidth - rightMargin },
                    theme: 'grid',
                    headStyles: { 
                        fillColor: [108, 117, 125], 
                        textColor: [255, 255, 255], 
                        fontStyle: 'bold', 
                        fontSize: 10 
                    },
                    bodyStyles: { 
                        fillColor: false, 
                        textColor: [33, 37, 41], 
                        fontSize: 8 
                    },
                    columnStyles: {
                        0: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        1: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                        2: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        3: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                        4: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        5: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                        6: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        7: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth }
                    },
                    head: [[{content: 'Local Transfer', colSpan: 8, styles: {halign: 'left', textColor: [255, 255, 255]}}]],
                    body: tableBody
                });
                yPos = pdf.lastAutoTable.finalY + 3;
            });
            return yPos;
        }
        
        function renderAirportDepartureTransfer(pdf, services, leftMargin, yPos, pageWidth, rightMargin) {
            console.log("Please check the airport departure transfer data in renderAirportDepartureTransfer function", services);
            services.forEach(service => {
                // Extract all possible fields from data
                const pickup = service.pickup || service.data?.exitpickup || service.data?.exit_pickup || service.data?.pickup || '';
                const dropoff = service.dropoff || service.data?.exitdropoff || service.data?.exit_dropoff || service.data?.dropoff || '';
                // Get vehicle name from vehicle_driver_data (from jobsheet) first, then fallback to other sources
                const vehicle = service.vehicleName || service.vehicle || service.data?.vehicles_name || service.data?.vehicle || '';
                const transferType = service.transferType || service.data?.type || '';
                // Extract pickup time - prefer entrytime (which has the actual time like "06:00 AM")
                const pickupTime = service.data?.entrytime || service.pickupTime || service.data?.exitpickupdate || service.data?.exitpickuptime || '';
                
                // Flight Information (if available)
                const originCity = service.data?.originCity || service.data?.origin_city || '';
                const departureTime = service.data?.departureTime || service.data?.departure_time || '';
                const originAirport = service.data?.originAirport || service.data?.origin_airport || '';
                const originFlightNumber = service.data?.originFlightNumber || service.data?.origin_flight_number || '';
                const originPNR = service.data?.originPNR || service.data?.origin_pnr || '';
                
                const arrivalCity = service.data?.arrivalCity || service.data?.arrival_city || service.data?.city || '';
                const arrivalTime = service.data?.arrivalTime || service.data?.arrival_time || '';
                const arrivalAirport = service.data?.arrivalAirport || service.data?.arrival_airport || '';
                const arrivalFlightNumber = service.data?.arrivalFlightNumber || service.data?.arrival_flight_number || '';
                const arrivalPNR = service.data?.arrivalPNR || service.data?.arrival_pnr || '';
                
                // Transfer Details
                const terminal = service.data?.terminal || '';
                const travelTime = service.data?.travelTime || service.data?.travel_time || '';
                
                // Prioritize vehicle/driver data from serviceInfo (populated from controller)
                // These come from vehicle_driver_data set in the controller
                const driverName = service.driverName || service.data?.driverName || service.data?.driver_name || '';
                const driverPhone = service.driverPhone || service.data?.driverPhone || service.data?.driver_phone || '';
                const vehicleNumber = service.vehicleNumber || service.data?.vehicleNumber || service.data?.vehicle_number || '';
                
                // Capacity Details
                const maxPassengerCapacity = service.maxPassengerCapacity || service.data?.maxPassengerCapacity || service.data?.max_passenger_capacity || service.data?.seating_capacity || '';
                const maxLuggageCapacity = service.data?.maxLuggageCapacity || service.data?.max_luggage_capacity || '';
                const childSeatAvailable = service.data?.childSeatAvailable || service.data?.child_seat_available || '';
                
                // Additional fields
                const city = service.data?.city || '';
                const country = service.data?.country || '';
                const adults = service.data?.adults || '';
                const children = service.data?.children || '';
                const bookingDate = service.data?.bookingDate || service.date || '';
                const remarks = service.data?.remarks || service.data?.specialRequests || '';
                
                // Format date
                const formatDateForDisplay = (dateStr) => {
                    if (!dateStr) return '';
                    try {
                        const date = new Date(dateStr);
                        const day = date.getDate();
                        const month = date.toLocaleString('en-US', { month: 'short' });
                        const year = date.getFullYear();
                        return `${day} ${month} ${year}`;
                    } catch (e) {
                        return dateStr;
                    }
                };
                
                // Format time (remove seconds if present, handle AM/PM format)
                const formatTime = (timeStr) => {
                    if (!timeStr) return '';
                    // If it's already in HH:MM format, return as is
                    if (timeStr.match(/^\d{2}:\d{2}$/)) {
                        return timeStr.substring(0, 5);
                    }
                    // If it's in "06:00 AM" format, return as is
                    if (timeStr.match(/\d{1,2}:\d{2}\s*(AM|PM)/i)) {
                        return timeStr;
                    }
                    return timeStr;
                };
                
                // Build table body - 8 columns (4 parameter-value pairs per row)
                const tableBody = [];
                
                // Flight Information - Origin Row
                tableBody.push([
                    'Origin City (from) :', originCity || 'N/A',
                    'Departure Time :', formatTime(departureTime) || 'N/A',
                    'Origin Airport Name & Code :', originAirport || 'N/A',
                    'Origin Flight Number :', originFlightNumber || 'N/A'
                ]);
                
                // Flight Information - Arrival Row (Arrival Time same as Pick up time)
                tableBody.push([
                    'Arrival City (to) :', arrivalCity || 'N/A',
                    'Arrival Time :', formatTime(pickupTime) || 'N/A',
                    'Arrival Airport Name & Code :', arrivalAirport || 'N/A',
                    'Arrival Flight Number :', arrivalFlightNumber || 'N/A'
                ]);
                
                // PNR and Transfer Details Row
                tableBody.push([
                    'PNR :', originPNR || arrivalPNR || 'N/A',
                    'Pick up time :', formatTime(pickupTime) || 'N/A',
                    'Transfer Type :', transferType || 'N/A',
                    'Vehicle Type :', vehicle || 'N/A'
                ]);
                
                // Transfer Details - Second Row
                tableBody.push([
                    'Pick up from :', pickup || 'N/A',
                    'Terminal :', terminal || 'N/A',
                    'Vehicle Number :', vehicleNumber || 'N/A',
                    'Max Passenger Capacity :', maxPassengerCapacity ? maxPassengerCapacity.toString() : 'N/A'
                ]);
                
                // Transfer Details - Third Row
                tableBody.push([
                    'Drop off to :', dropoff || 'N/A',
                    'Travel Time :', travelTime || 'N/A',
                    'Max Luggage Capacity :', maxLuggageCapacity ? maxLuggageCapacity.toString() : 'N/A',
                    'Child Seat Available :', childSeatAvailable ? childSeatAvailable.toString() : 'N/A'
                ]);
                
                // Driver and Passenger Details Row
                tableBody.push([
                    'Driver Name :', driverName || 'N/A',
                    'Driver Phone :', driverPhone || 'N/A',
                    'Adults :', adults ? adults.toString() : '0',
                    'Children :', children ? children.toString() : '0'
                ]);
                
                // Location and Booking Details Row
                tableBody.push([
                    'City :', city || 'N/A',
                    'Country :', country || 'N/A',
                    'Booking Date :', bookingDate ? formatDateForDisplay(bookingDate) : 'N/A',
                    '', ''
                ]);
                
                // Remarks Row - Last row, full width (spans all 8 columns)
                tableBody.push([
                    {content: 'Remarks :', colSpan: 1, styles: {fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80]}},
                    {content: remarks || 'N/A', colSpan: 7, styles: {fillColor: [255, 255, 255], textColor: [33, 37, 41]}}
                ]);
                
                // Calculate column widths
                const availableWidth = pageWidth - leftMargin - (pageWidth - rightMargin);
                const columnWidth = availableWidth / 8;
                
                pdf.autoTable({
                    startY: yPos,
                    margin: { left: leftMargin, right: pageWidth - rightMargin },
                    theme: 'grid',
                    headStyles: { 
                        fillColor: [108, 117, 125], 
                        textColor: [255, 255, 255], 
                        fontStyle: 'bold', 
                        fontSize: 10 
                    },
                    bodyStyles: { 
                        fillColor: false, 
                        textColor: [33, 37, 41], 
                        fontSize: 8 
                    },
                    columnStyles: {
                        0: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        1: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                        2: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        3: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                        4: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        5: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth },
                        6: { fontStyle: 'bold', fillColor: [240, 248, 255], textColor: [44, 62, 80], cellWidth: columnWidth },
                        7: { fillColor: [255, 255, 255], textColor: [33, 37, 41], cellWidth: columnWidth }
                    },
                    head: [[{content: 'Airport Departure Transfer', colSpan: 8, styles: {halign: 'left', textColor: [255, 255, 255]}}]],
                    body: tableBody
                });
                yPos = pdf.lastAutoTable.finalY + 3;
            });
            return yPos;
        }
        
        function preparePrint() {
            // Add any pre-print preparations here
            // For example, you could expand all collapsed items, etc.
            
            // Show any additional details for print
            document.querySelectorAll('.print-only').forEach(function(el) {
                el.style.display = 'block';
            });
        }
        
        // After print, restore the UI
        window.onafterprint = function() {
            document.querySelectorAll('.print-only').forEach(function(el) {
                el.style.display = 'none';
            });
        };
        
        // Drag and Drop functionality
        let draggedElement = null;
        let draggedData = null;
        
        // Add event listeners to draggable items
        const draggableItems = document.querySelectorAll('.service-item.draggable');
        draggableItems.forEach(item => {
            item.addEventListener('dragstart', handleDragStart);
            item.addEventListener('dragend', handleDragEnd);
        });
        
        // Add event listeners to drop zones (both date containers and services lists)
        const dropZones = document.querySelectorAll('.date-container.drop-zone');
        dropZones.forEach(zone => {
            zone.addEventListener('dragover', handleDragOver);
            zone.addEventListener('dragenter', handleDragEnter);
            zone.addEventListener('dragleave', handleDragLeave);
            zone.addEventListener('drop', handleDrop);
        });
        
        // Add event listeners to services lists for intra-day reordering
        const servicesLists = document.querySelectorAll('.services-list');
        servicesLists.forEach(list => {
            list.addEventListener('dragover', handleDragOver);
            list.addEventListener('dragenter', handleDragEnter);
            list.addEventListener('dragleave', handleDragLeave);
            list.addEventListener('drop', handleServiceDrop);
        });
        
        function handleDragStart(e) {
            draggedElement = this;
            draggedData = {
                bookingId: this.dataset.bookingId,
                serviceType: this.dataset.serviceType,
                currentDate: this.dataset.currentDate,
                bookingData: this.dataset.bookingData
            };
            
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.outerHTML);
            
            // Show drop indicators
            document.querySelectorAll('.drop-zone-indicator').forEach(indicator => {
                indicator.classList.add('active');
            });
        }
        
        function handleDragEnd(e) {
            this.classList.remove('dragging');
            draggedElement = null;
            draggedData = null;
            
            // Hide drop indicators
            document.querySelectorAll('.drop-zone-indicator').forEach(indicator => {
                indicator.classList.remove('active');
            });
            
            // Remove drag-over class from all drop zones
            document.querySelectorAll('.date-container').forEach(zone => {
                zone.classList.remove('drag-over');
            });
        }
        
        function handleDragOver(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            return false;
        }
        
        function handleDragEnter(e) {
            this.classList.add('drag-over');
        }
        
        function handleDragLeave(e) {
            // Only remove drag-over if we're leaving the container, not entering a child
            if (!this.contains(e.relatedTarget)) {
                this.classList.remove('drag-over');
            }
        }
        
        function handleDrop(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }
            
            this.classList.remove('drag-over');
            
            if (draggedElement && draggedData) {
                const newDate = this.dataset.date;
                const currentDate = draggedData.currentDate;
                
                // Check if we're dropping on a different date
                if (newDate !== currentDate) {
                    // Update the booking date
                    updateBookingDate(draggedData.bookingId, newDate, currentDate);
                }
            }
            
            return false;
        }
        
        function handleServiceDrop(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }
            
            this.classList.remove('drag-over');
            
            if (draggedElement && draggedData) {
                const currentServicesList = this;
                const dateContainer = currentServicesList.closest('.date-container');
                const newDate = dateContainer.dataset.date;
                const currentDate = draggedData.currentDate;
                
                // Check if we're reordering within the same day
                if (newDate === currentDate) {
                    // Reorder within the same day
                    reorderServicesInDay(draggedElement, currentServicesList, e.clientY);
                } else {
                    // Move to different day
                    updateBookingDate(draggedData.bookingId, newDate, currentDate);
                }
            }
            
            return false;
        }
        
        function reorderServicesInDay(draggedItem, servicesList, mouseY) {
            const serviceItems = Array.from(servicesList.querySelectorAll('.service-item:not(.dragging)'));
            let insertBeforeItem = null;
            
            // Find the position to insert based on mouse Y position
            for (let item of serviceItems) {
                const rect = item.getBoundingClientRect();
                const itemMiddle = rect.top + rect.height / 2;
                
                if (mouseY < itemMiddle) {
                    insertBeforeItem = item;
                    break;
                }
            }
            
            // Insert the dragged item at the new position
            if (insertBeforeItem) {
                servicesList.insertBefore(draggedItem, insertBeforeItem);
            } else {
                servicesList.appendChild(draggedItem);
            }
            
            // Show success message for reordering
            showSuccessMessage('Service order updated successfully!');
        }
        
        function updateBookingDate(bookingId, newDate, oldDate) {
            // Show loading state
            showLoadingState();
            
            // Make AJAX request to update booking date
            fetch('{{ route("bookinglist.updateDate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    booking_id: bookingId,
                    new_date: newDate,
                    old_date: oldDate
                })
            })
            .then(response => response.json())
            .then(data => {
                hideLoadingState();
                
                if (data.success) {
                    // Show success message
                    showSuccessMessage('Booking date updated successfully!');
                    
                    // Reload the page to reflect changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showErrorMessage(data.message || 'Failed to update booking date');
                }
            })
            .catch(error => {
                hideLoadingState();
                console.error('Error:', error);
                showErrorMessage('An error occurred while updating the booking date');
            });
        }
        
        function showLoadingState() {
            // Create loading overlay if it doesn't exist
            if (!document.getElementById('loadingOverlay')) {
                const overlay = document.createElement('div');
                overlay.id = 'loadingOverlay';
                overlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 9999;
                `;
                overlay.innerHTML = `
                    <div style="background: white; padding: 20px; border-radius: 8px; text-align: center;">
                        <div style="margin-bottom: 10px;">Updating booking date...</div>
                        <div style="width: 40px; height: 40px; margin: 0 auto; border: 4px solid #f3f3f3; border-top: 4px solid #435ebe; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    </div>
                `;
                document.body.appendChild(overlay);
                
                // Add spinner animation
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                `;
                document.head.appendChild(style);
            }
        }
        
        function hideLoadingState() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.remove();
            }
        }
        
        function showSuccessMessage(message) {
            showMessage(message, 'success');
        }
        
        function showErrorMessage(message) {
            showMessage(message, 'error');
        }
        
        function showMessage(message, type) {
            const messageDiv = document.createElement('div');
            const bgColor = type === 'success' ? '#28a745' : 
                           type === 'error' ? '#dc3545' : 
                           type === 'info' ? '#17a2b8' : '#ffc107';
            
            messageDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 5px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                max-width: 300px;
                background: ${bgColor};
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            `;
            messageDiv.textContent = message;
            document.body.appendChild(messageDiv);
            
            // Auto remove after 3 seconds (unless it's an info message for PDF generation)
            if (type !== 'info') {
                setTimeout(() => {
                    messageDiv.remove();
                }, 3000);
            }
            
            return messageDiv; // Return the element so it can be removed manually
        }
        
        // Price Hide Auto-Refresh Functionality
        // Ensure priceHide is properly set as integer (0 or 1)
        // 0 = show prices, 1 = hide prices
        let currentPriceHide = {{ isset($priceHide) ? (int)$priceHide : 0 }};
        let priceCheckInterval = null;
        let isChecking = false; // Prevent multiple simultaneous checks
        
        // Debug logging
        console.log('=== Price Hide Auto-Refresh Initialized ===');
        console.log('Initial price_hide value:', currentPriceHide, '(Type:', typeof currentPriceHide + ')');
        console.log('Polling will start in 1 second...');
        
        function startPriceHidePolling() {
            // Check every 3 seconds for price_hide changes (reduced from 5 for faster response)
            if (priceCheckInterval) {
                clearInterval(priceCheckInterval);
            }
            
            priceCheckInterval = setInterval(function() {
                if (!isChecking) {
                    checkPriceHideStatus();
                }
            }, 3000); // 3 seconds interval
            
            console.log('Price hide polling started');
        }
        
        function checkPriceHideStatus() {
            if (isChecking) {
                return; // Skip if already checking
            }
            
            isChecking = true;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                             document.querySelector('input[name="_token"]')?.value || 
                             '{{ csrf_token() }}';
            
            const url = '{{ route("bookinglist.checkPriceHide") }}';
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin',
                cache: 'no-cache'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                isChecking = false;
                
                if (data && data.success !== undefined && data.price_hide !== undefined) {
                    // Convert to integer for proper comparison (0 or 1)
                    const newPriceHide = parseInt(data.price_hide);
                    const currentPriceHideInt = parseInt(currentPriceHide);
                    
                    console.log('Checking price_hide - Current:', currentPriceHideInt, 'New:', newPriceHide);
                    
                    // If price_hide value has changed, refresh the page
                    if (newPriceHide !== currentPriceHideInt) {
                        console.log('Price hide status changed from', currentPriceHideInt, 'to', newPriceHide, '- Refreshing page...');
                        
                        // Stop polling before refresh
                        if (priceCheckInterval) {
                            clearInterval(priceCheckInterval);
                            priceCheckInterval = null;
                        }
                        
                        // Small delay to ensure cleanup
                        setTimeout(function() {
                            // Refresh the page to show updated prices
                            window.location.reload(true);
                        }, 100);
                    }
                } else {
                    console.warn('Invalid response data:', data);
                }
            })
            .catch(error => {
                isChecking = false;
                console.error('Error checking price hide status:', error);
                // Don't stop polling on error, just log it
            });
        }
        
        // Start polling when page loads (with small delay to ensure page is fully loaded)
        setTimeout(function() {
            startPriceHidePolling();
        }, 1000);
        
        // Stop polling when page is about to unload
        window.addEventListener('beforeunload', function() {
            if (priceCheckInterval) {
                clearInterval(priceCheckInterval);
                priceCheckInterval = null;
            }
        });
    });
</script>
@endsection 