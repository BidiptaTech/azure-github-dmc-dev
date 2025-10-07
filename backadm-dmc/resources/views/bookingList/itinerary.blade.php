@extends('layouts.layout')
@section('title', 'Tour Itinerary')

@section('content')
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
        line-height: 1.6;
    }
    
    .itinerary-header {
        background: var(--background-primary);
        padding: 32px 40px;
        margin: 0 24px 32px 24px;
        border-radius: 16px;
        box-shadow: var(--shadow-lg);
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
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 8px 0;
        letter-spacing: -0.025em;
    }
    
    .header-info h5 {
        font-size: 16px;
        font-weight: 500;
        color: var(--text-secondary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .header-actions {
        display: flex;
        gap: 12px;
        flex-shrink: 0;
    }
    
    .btn-modern {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 500;
        font-size: 14px;
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
        padding: 0 24px 24px 24px;
        position: relative;
        overflow: visible !important;
    }
    
    .day-indicator {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
        position: relative;
        background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);
        padding: 12px 16px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid #e1e8f0;
    }
    
    .day-circle {
        min-width: 70px;
        height: 36px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 12px;
        margin-right: 16px;
        position: relative;
        z-index: 2;
        transition: all 0.2s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .day-circle.day-1 { 
        background: linear-gradient(135deg, #FF6B6B 0%, #FF5252 100%);
        box-shadow: 0 3px 12px rgba(255,107,107,0.4);
    }
    .day-circle.day-2 { 
        background: linear-gradient(135deg, #4ECDC4 0%, #26A69A 100%);
        box-shadow: 0 3px 12px rgba(78,205,196,0.4);
    }
    .day-circle.day-3 { 
        background: linear-gradient(135deg, #45B7D1 0%, #2196F3 100%);
        box-shadow: 0 3px 12px rgba(69,183,209,0.4);
    }
    .day-circle.day-4 { 
        background: linear-gradient(135deg, #96CEB4 0%, #66BB6A 100%);
        box-shadow: 0 3px 12px rgba(150,206,180,0.4);
    }
    .day-circle.day-5 { 
        background: linear-gradient(135deg, #FECA57 0%, #FFC107 100%);
        box-shadow: 0 3px 12px rgba(254,202,87,0.4);
    }
    .day-circle.day-6 { 
        background: linear-gradient(135deg, #FF9FF3 0%, #E91E63 100%);
        box-shadow: 0 3px 12px rgba(255,159,243,0.4);
    }
    .day-circle.day-7 { 
        background: linear-gradient(135deg, #54A0FF 0%, #3F51B5 100%);
        box-shadow: 0 3px 12px rgba(84,160,255,0.4);
    }
    .day-circle:nth-child(8n) { 
        background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
        box-shadow: 0 3px 12px rgba(102,126,234,0.4);
    }
    
    .day-info {
        flex-grow: 1;
    }
    
    .day-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 2px 0;
        letter-spacing: -0.025em;
    }
    
    .day-date {
        font-size: 13px;
        color: var(--text-secondary);
        margin: 0;
        font-weight: 500;
    }
    
        .date-container {
        position: relative;
        margin-bottom: 24px;
        overflow: visible !important;
    }
    
    .timeline-line {
        position: absolute;
        left: 51px;
        top: 48px;
        bottom: -8px;
        width: 3px;
        z-index: 0;
        border-radius: 2px;
    }
    
    /* Day-specific timeline colors */
    .date-container.day-1 .timeline-line {
        background: repeating-linear-gradient(
            to bottom,
            #FF6B6B 0px,
            #FF6B6B 8px,
            transparent 8px,
            transparent 16px
        );
    }
    
    .date-container.day-2 .timeline-line {
        background: repeating-linear-gradient(
            to bottom,
            #4ECDC4 0px,
            #4ECDC4 8px,
            transparent 8px,
            transparent 16px
        );
    }
    
    .date-container.day-3 .timeline-line {
        background: repeating-linear-gradient(
            to bottom,
            #45B7D1 0px,
            #45B7D1 8px,
            transparent 8px,
            transparent 16px
        );
    }
    
    .date-container.day-4 .timeline-line {
        background: repeating-linear-gradient(
            to bottom,
            #96CEB4 0px,
            #96CEB4 8px,
            transparent 8px,
            transparent 16px
        );
    }
    
    .date-container.day-5 .timeline-line {
        background: repeating-linear-gradient(
            to bottom,
            #FECA57 0px,
            #FECA57 8px,
            transparent 8px,
            transparent 16px
        );
    }
    
    .date-container.day-6 .timeline-line {
        background: repeating-linear-gradient(
            to bottom,
            #FF9FF3 0px,
            #FF9FF3 8px,
            transparent 8px,
            transparent 16px
        );
    }
    
    .date-container.day-7 .timeline-line {
        background: repeating-linear-gradient(
            to bottom,
            #54A0FF 0px,
            #54A0FF 8px,
            transparent 8px,
            transparent 16px
        );
    }
    
    .date-container:nth-child(8n) .timeline-line {
        background: repeating-linear-gradient(
            to bottom,
            #667EEA 0px,
            #667EEA 8px,
            transparent 8px,
            transparent 16px
        );
    }
    
    .date-container:last-child .timeline-line {
        display: none;
    }
    
    .services-list {
        margin-left: 76px;
        margin-top: 8px;
        padding-left: 44px;
        position: relative;
        overflow: visible !important;
    }
    
    .service-item {
        background: #ffffff;
        border: 1px solid #e1e8f0;
        border-radius: 8px;
        margin-bottom: 8px;
        padding: 0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.2s ease;
        position: relative;
        overflow: visible !important;
        transform: translateX(0);
    }
    
    /* Timeline service markers - black circles with white icons positioned directly on timeline */
    .service-item::before {
        content: '●'; /* default dot */
        position: absolute !important;
        left: -83px !important; /* Position to center on timeline */
        top: 50% !important;
        transform: translateY(-50%) !important; /* Only center vertically */
        width: 28px !important;
        height: 28px !important;
        border-radius: 50% !important;
        background: #000000 !important;
        border: 3px solid #ffffff !important;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2) !important;
        z-index: 20 !important; /* Higher z-index to appear above timeline */
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 14px !important;
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
    .service-item[data-service-type*="travel"]::before {
        content: '🚗' !important; /* car symbol */
        font-family: Arial, sans-serif !important;
        font-weight: normal !important;
    }
    
    .service-item[data-service-type="attraction"]::before {
        content: '📍' !important; /* location pin */
        font-family: Arial, sans-serif !important;
        font-weight: normal !important;
    }
    
    .service-item[data-service-type="restaurant"]::before {
        content: '🍽' !important; /* dining symbol */
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
        transform: translateY(-1px) translateX(2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-color: #c7d2fe;
    }
    
    .service-item:last-child {
        margin-bottom: 0;
    }
    
    .service-item-content {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        gap: 12px;
        position: relative;
    }
    
    .service-right-details {
        position: absolute;
        right: 12px;
        top: 12px;
        bottom: 12px;
        width: 120px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: rgba(248,250,252,0.8);
        border-radius: 8px;
        border: 1px solid rgba(226,232,240,0.5);
        padding: 8px;
        text-align: center;
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
    
    /* Adjust main content to make room for right details */
    .service-main-content {
        flex-grow: 1;
        min-width: 0;
        margin-right: 130px;
    }
    
    /* Service priority styling */
    .service-item.hotel {
        border-left: 4px solid #9c27b0;
        background: linear-gradient(135deg, #faf5ff 0%, #ffffff 100%);
    }
    
    .service-item.entry-port {
        border-left: 4px solid #2196f3;
        background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%);
    }
    
    .service-item.exit-port {
        border-left: 4px solid #f44336;
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
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
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        position: relative;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .service-left-icon.flight {
        background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
        color: white;
    }
    
    .service-left-icon.hotel {
        background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);
        color: white;
    }
    
    .service-left-icon.transfer {
        background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
        color: white;
    }
    
    .service-left-icon.guide {
        background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
        color: white;
    }
    
    .service-left-icon.attraction {
        background: linear-gradient(135deg, #00BCD4 0%, #0097A7 100%);
        color: white;
    }
    
    .service-left-icon.restaurant {
        background: linear-gradient(135deg, #FF5722 0%, #D84315 100%);
        color: white;
    }
    
    .service-left-icon.entry {
        background: linear-gradient(135deg, #2196F3 0%, #1565C0 100%);
        color: white;
    }
    
    .service-left-icon.exit {
        background: linear-gradient(135deg, #F44336 0%, #C62828 100%);
        color: white;
    }
    
    .service-left-icon.entryport {
        background: linear-gradient(135deg, #2196F3 0%, #1565C0 100%);
        color: white;
    }
    
    .service-left-icon.exitport {
        background: linear-gradient(135deg, #F44336 0%, #C62828 100%);
        color: white;
    }
    
    .service-main-content {
        flex-grow: 1;
        min-width: 0;
    }
    
    .service-header {
        margin-bottom: 6px;
    }
    
    .service-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 3px 0;
        line-height: 1.3;
        letter-spacing: -0.025em;
    }
    
    .service-details-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 6px;
    }
    
    .service-time-badge {
        background: #2563eb;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .service-time-badge::before {
        content: '🕐';
        font-size: 10px;
    }
    
    .service-pax-badge {
        background: #10b981;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .service-pax-badge::before {
        content: '👥';
        font-size: 10px;
    }
    
    .service-description {
        color: var(--text-secondary);
        font-size: 13px;
        line-height: 1.4;
        margin: 0;
        font-weight: 400;
    }
    
    .service-type-tag {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
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
        left: 5px;
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
            padding: 8px 12px;
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
        }
        
        .service-item::before {
            left: -70px !important; /* Position to center on mobile timeline */
            width: 24px !important;
            height: 24px !important;
            font-size: 12px !important;
            border: 2px solid #ffffff !important;
            transform: translateY(-50%) !important; /* Only center vertically */
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
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(59, 130, 246, 0.05));
        border-radius: 12px;
        padding: 16px;
        margin-top: 10px;
        border: 1px solid rgba(37, 99, 235, 0.2);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .hotel-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .hotel-info-item {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 8px;
        padding: 8px 10px;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
        border-radius: 10px;
        padding: 12px;
        margin-top: 12px;
        border: 1px solid rgba(37, 99, 235, 0.15);
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
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 8px;
        border-left: 4px solid #10b981;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        position: relative;
    }
    
    .room-item::before {
        content: '';
        position: absolute;
        top: 8px;
        right: 8px;
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
    }
    
    .room-basic-info {
        margin-bottom: 6px;
    }
    
    .room-basic-info small {
        color: #1f2937 !important;
        font-weight: 600;
    }
    
    .bed-details {
        margin-left: 8px;
        padding-left: 8px;
        border-left: 2px solid #e5e7eb;
    }
    
    .bed-info {
        margin-bottom: 6px;
        background: rgba(255, 255, 255, 0.7);
        border-radius: 6px;
        padding: 6px 8px;
    }
    
    .bed-info small {
        color: #4b5563 !important;
        font-weight: 500;
    }
    
    .meal-info {
        margin-left: 0;
        margin-top: 6px;
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    
    .meal-badge {
        background: linear-gradient(135deg, #059669, #10b981) !important;
        color: white !important;
        font-size: 11px !important;
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
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(248, 113, 113, 0.05));
        border-radius: 12px;
        padding: 16px;
        margin-top: 10px;
        border: 1px solid rgba(239, 68, 68, 0.2);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .attraction-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .attraction-info-item {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 8px;
        padding: 8px 10px;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
        border-radius: 10px;
        padding: 12px;
        margin-top: 12px;
        border: 1px solid rgba(239, 68, 68, 0.15);
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
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(74, 222, 128, 0.05));
        border-radius: 12px;
        padding: 16px;
        margin-top: 10px;
        border: 1px solid rgba(34, 197, 94, 0.2);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .restaurant-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .restaurant-info-item {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 8px;
        padding: 8px 10px;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
        border-radius: 10px;
        padding: 12px;
        margin-top: 12px;
        border: 1px solid rgba(34, 197, 94, 0.15);
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
        border-radius: 10px;
        padding: 12px;
        margin-top: 12px;
        border: 1px solid rgba(147, 51, 234, 0.15);
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
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(129, 140, 248, 0.05));
        border-radius: 12px;
        padding: 16px;
        margin-top: 10px;
        border: 1px solid rgba(99, 102, 241, 0.2);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .transfer-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .transfer-info-item {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 8px;
        padding: 8px 10px;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
        border-radius: 10px;
        padding: 12px;
        position: relative;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
                                    <h3 class="day-title">{{ \Carbon\Carbon::parse($date)->format('l') }}</h3>
                                    <p class="day-date">{{ \Carbon\Carbon::parse($date)->format('jS M Y') }}</p>
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
                                        } elseif (strtolower($serviceType) == 'restaurant') {
                                            // For restaurants
                                            if (!empty($data['restaurantName'])) {
                                                $serviceName = $data['restaurantName'];
                                            } elseif (!empty($data['name'])) {
                                                $serviceName = $data['name'];
                                            } else {
                                                $serviceName = 'Restaurant';
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
                                    @endphp
                                    
                                    <div class="drop-zone-indicator"></div>
                                    <div class="service-item {{ $draggableClass }} {{ $serviceStyleClass }}" 
                                         id="{{ $itemId }}"
                                         {!! $draggableAttrs !!}
                                         data-booking-id="{{ $booking->booking_id ?? '' }}"
                                         data-service-type="{{ strtolower($serviceType) }}"
                                         data-current-date="{{ $date }}"
                                         data-booking-data="{{ base64_encode(json_encode($data)) }}">
                                        @if($isDraggable)
                                            <div class="drag-indicator">⋮⋮</div>
                                        @endif
                                        
                                        <div class="service-item-content">
                                            <!-- Left Icon -->
                                            <div class="service-left-icon {{ strtolower(str_replace(' ', '', $serviceType)) }}">
                                                @if(strtolower($serviceType) == 'hotel')
                                                    🏨
                                                @elseif(strtolower($serviceType) == 'guide')
                                                    👨‍🏫
                                                @elseif(strpos(strtolower($serviceType), 'transfer') !== false)
                                                    🚗
                                                @elseif(strpos(strtolower($serviceType), 'entry') !== false || strtolower($serviceType) == 'arrival')
                                                    ✈️
                                                @elseif(strpos(strtolower($serviceType), 'exit') !== false || strtolower($serviceType) == 'departure')
                                                    🛫
                                                @elseif(strtolower($serviceType) == 'attraction')
                                                    🎯
                                                @elseif(strtolower($serviceType) == 'restaurant')
                                                    🍽️
                                                @else
                                                    ✈️
                                                @endif
                                            </div>
                                            
                                            <!-- Main Content -->
                                            <div class="service-main-content">
                                                <div class="service-header">
                                                    <div class="service-type-tag {{ strtolower(str_replace(' ', '', $serviceType)) }}">
                                                        {{ $serviceType }}
                                                    </div>
                                                    <h4 class="service-title">{{ $serviceName }}</h4>
                                                    @if(strtolower($serviceType) == 'hotel')
                                                        <p class="service-description">
                                                            @if(isset($data['day_in_stay']) && isset($data['total_nights']))
                                                                @if(isset($data['stay_type']) && $data['stay_type'] == 'checkin')
                                                                    Check-in • {{ $data['total_nights'] }} {{ $data['total_nights'] > 1 ? 'nights' : 'night' }}
                                                                @else
                                                                    Day {{ $data['day_in_stay'] }} of {{ $data['total_nights'] }} • {{ $serviceName }}
                                                                @endif
                                                            @else
                                                                Hotel accommodation
                                                            @endif
                                                        </p>
                                                        
                                                        <!-- Enhanced Hotel Details -->
                                                        <div class="hotel-details mt-2">
                                                            @if(isset($data['hotelDetails']))
                                                                <div class="hotel-info-grid">
                                                                    @if(!empty($data['hotelDetails']['checkInTime']) || !empty($data['hotelDetails']['checkOutTime']))
                                                                        <div class="hotel-info-item">
                                                                            <small class="text-muted">
                                                                                <i class="fas fa-clock me-1"></i>
                                                                                Check-in: {{ $data['hotelDetails']['checkInTime'] ?? 'N/A' }} | 
                                                                                Check-out: {{ $data['hotelDetails']['checkOutTime'] ?? 'N/A' }}
                                                                            </small>
                                                                        </div>
                                                                    @endif
                                                                    
                                                                    @if(!empty($data['totalPrice']))
                                                                        <div class="hotel-info-item">
                                                                            <small class="text-success fw-bold">
                                                                                <i class="fas fa-dollar-sign me-1"></i>
                                                                                SGD {{ number_format($data['totalPrice'], 2) }}
                                                                                @if(!empty($data['priceMode']))
                                                                                    <span class="price-mode-badge">{{ ucfirst($data['priceMode']) }}</span>
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                            
                                                            <!-- Room Information -->
                                                            @if(isset($data['rooms']) && is_array($data['rooms']) && count($data['rooms']) > 0)
                                                                <div class="rooms-summary mt-2">
                                                                    <div class="rooms-header">
                                                                        <small class="text-primary fw-semibold">
                                                                            <i class="fas fa-bed me-1"></i>
                                                                            {{ count($data['rooms']) }} {{ count($data['rooms']) > 1 ? 'Rooms' : 'Room' }}
                                                                        </small>
                                                                    </div>
                                                                    
                                                                    @foreach($data['rooms'] as $index => $room)
                                                                        <div class="room-item mt-1">
                                                                            <div class="room-basic-info">
                                                                                <small class="text-dark">
                                                                                    <strong>Room {{ $index + 1 }}:</strong> {{ $room['room_type'] ?? 'Standard Room' }}
                                                                                </small>
                                                                            </div>
                                                                            
                                                                            @if(isset($room['beds']) && is_array($room['beds']) && count($room['beds']) > 0)
                                                                                <div class="bed-details mt-1">
                                                                                    @foreach($room['beds'] as $bedIndex => $bed)
                                                                                        <div class="bed-info">
                                                                                            <small class="text-muted">
                                                                                                • {{ $bed['bed_type'] ?? 'Standard Bed' }}
                                                                                                @if(!empty($bed['head_count']))
                                                                                                    ({{ $bed['head_count'] }} pax)
                                                                                                @endif
                                                                                                @if(!empty($bed['price']))
                                                                                                    - SGD {{ number_format($bed['price'], 2) }}
                                                                                                @endif
                                                                                            </small>
                                                                                            
                                                                                            @if(isset($bed['selectedMeals']) && is_array($bed['selectedMeals']) && count($bed['selectedMeals']) > 0)
                                                                                                <div class="meal-info mt-1">
                                                                                                    @foreach($bed['selectedMeals'] as $mealKey => $meal)
                                                                                                        <span class="badge meal-badge me-1 mb-1">
                                                                                                            <i class="fas fa-utensils me-1"></i>{{ $meal['type'] ?? 'Meal' }}
                                                                                                            @if(!empty($meal['price']))
                                                                                                                <span class="meal-price">+${{ number_format($meal['price'], 2) }}</span>
                                                                                                            @endif
                                                                                                        </span>
                                                                                                    @endforeach
                                                                                                </div>
                                                                                            @endif
                                                                                        </div>
                                                                                    @endforeach
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @elseif(strtolower($serviceType) == 'guide')
                                                        <p class="service-description">Professional tour guide service</p>
                                                        
                                                        <!-- Enhanced Guide Details -->
                                                        <div class="guide-details mt-2">
                                                            @php 
                                                                // $data is already the guide item data
                                                                $guideData = $data;
                                                            @endphp
                                                            
                                                            @if($guideData && is_array($guideData))
                                                                @php
                                                                    // Debug: Show all guide data
                                                                    $debugGuideData = json_encode($guideData, JSON_PRETTY_PRINT);
                                                                    
                                                                    // Check for different possible field names
                                                                    $bookingDate = $guideData['bookingDate'] ?? $guideData['booking_date'] ?? $guideData['pickupdate'] ?? null;
                                                                    $totalPrice = $guideData['totalPrice'] ?? $guideData['total_price'] ?? $guideData['price'] ?? null;
                                                                    $basePrice = $guideData['basePrice'] ?? $guideData['base_price'] ?? null;
                                                                    $hours = $guideData['hours'] ?? null;
                                                                    $adults = $guideData['adults'] ?? $guideData['adult_count'] ?? null;
                                                                    $children = $guideData['children'] ?? $guideData['child_count'] ?? null;
                                                                    $guideName = $guideData['guide_name'] ?? $guideData['guideName'] ?? null;
                                                                    $experience = $guideData['experience'] ?? null;
                                                                    $languages = $guideData['languages'] ?? null;
                                                                    $city = $guideData['city'] ?? null;
                                                                    $mode = $guideData['Mode'] ?? $guideData['mode'] ?? null;
                                                                @endphp
                                                                
                                                                
                                                                
                                                                <div class="guide-info-grid">
                                                                    @if(!empty($bookingDate))
                                                                        <div class="guide-info-item">
                                                                            <small class="text-muted">
                                                                                <i class="fas fa-calendar-alt me-1"></i>
                                                                                {{ date('d M Y', strtotime($bookingDate)) }}
                                                                                @if(!empty($hours))
                                                                                    • {{ $hours }} hour{{ $hours > 1 ? 's' : '' }}
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                    @endif
                                                                    
                                                                    @if(!empty($totalPrice))
                                                                        <div class="guide-info-item">
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
                                                                
                                                                <!-- Guide Information -->
                                                                @if(!empty($guideName) || !empty($experience) || !empty($languages) || !empty($adults) || !empty($children))
                                                                    <div class="guide-summary mt-2">
                                                                        <div class="guide-header">
                                                                            <small class="text-primary fw-semibold">
                                                                                <i class="fas fa-user-tie me-1"></i>
                                                                                Guide Information
                                                                            </small>
                                                                        </div>
                                                                        
                                                                        <div class="guide-item mt-1">
                                                                            @if(!empty($guideName))
                                                                                <div class="guide-basic-info">
                                                                                    <small class="text-dark">
                                                                                        <strong>{{ $guideName }}</strong>
                                                                                        @if(!empty($experience))
                                                                                            <span class="experience-badge">{{ $experience }} years exp.</span>
                                                                                        @endif
                                                                                    </small>
                                                                                </div>
                                                                            @endif
                                                                            
                                                                            <!-- Group Size -->
                                                                            @if(!empty($adults) || !empty($children))
                                                                                <div class="group-info mt-1">
                                                                                    <small class="text-muted">
                                                                                        <i class="fas fa-users me-1"></i>
                                                                                        @if(!empty($adults))
                                                                                            {{ $adults }} Adult{{ $adults > 1 ? 's' : '' }}
                                                                                        @endif
                                                                                        @if(!empty($children))
                                                                                            @if(!empty($adults)) • @endif
                                                                                            {{ $children }} Child{{ $children > 1 ? 'ren' : '' }}
                                                                                        @endif
                                                                                        @if(!empty($city))
                                                                                            • {{ $city }}
                                                                                        @endif
                                                                                    </small>
                                                                                </div>
                                                                            @endif
                                                                            
                                                                            <!-- Languages -->
                                                                            @php
                                                                                // Debug: Check what languages data we have
                                                                                $debugLanguages = $languages ?? 'NULL';
                                                                                if (is_array($debugLanguages)) {
                                                                                    $debugLanguages = 'ARRAY with ' . count($debugLanguages) . ' items: ' . json_encode($debugLanguages);
                                                                                }
                                                                            @endphp
                                                                            
                                                                            @if(isset($languages) && is_array($languages) && count($languages) > 0)
                                                                                <div class="languages-info mt-1">
                                                                                    <small class="text-muted mb-1 d-block">
                                                                                        <i class="fas fa-language me-1"></i>Languages:
                                                                                    </small>
                                                                                    <div class="languages-list">
                                                                                        @foreach($languages as $lang)
                                                                                            <span class="badge language-badge me-1 mb-1">
                                                                                                {{ $lang['language'] ?? 'Language' }}
                                                                                                @if(!empty($lang['proficiency']))
                                                                                                    <small class="proficiency">({{ $lang['proficiency'] }})</small>
                                                                                                @endif
                                                                                            </span>
                                                                                        @endforeach
                                                                                    </div>
                                                                                </div>
                                                                            @else
                                                                                
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @elseif(strpos(strtolower($serviceType), 'transfer') !== false)
                                                        <p class="service-description">Private transportation service</p>
                                                        
                                                        <!-- Enhanced Transfer Details -->
                                                        <div class="transfer-details mt-2">
                                                            @php 
                                                                // $data is already the transfer item data
                                                                $transferData = $data;
                                                            @endphp
                                                            
                                                            @if($transferData && is_array($transferData))
                                                                @php
                                                                    // Check for different possible field names
                                                                    $bookingDate = $transferData['bookingDate'] ?? $transferData['booking_date'] ?? $transferData['exitpickupdate'] ?? null;
                                                                    $totalPrice = $transferData['totalPrice'] ?? $transferData['total_price'] ?? $transferData['price'] ?? null;
                                                                    $entryPickup = $transferData['entrypickup'] ?? $transferData['entry_pickup'] ?? $transferData['pickup'] ?? null;
                                                                    $dropoffLocation = $transferData['dropoffLocation'] ?? $transferData['dropoff_location'] ?? $transferData['dropoff'] ?? null;
                                                                    $entryTime = $transferData['entrytime'] ?? $transferData['entry_time'] ?? $transferData['time'] ?? null;
                                                                    $selectedHours = $transferData['selectedHours'] ?? $transferData['selected_hours'] ?? $transferData['hours'] ?? null;
                                                                    $vehicleName = $transferData['vehicles_name'] ?? $transferData['vehicle_name'] ?? null;
                                                                    $vehicleType = $transferData['type'] ?? $transferData['vehicle_type'] ?? null;
                                                                    $adults = $transferData['adults'] ?? $transferData['adult_count'] ?? null;
                                                                    $children = $transferData['children'] ?? $transferData['child_count'] ?? null;
                                                                    $mode = $transferData['Mode'] ?? $transferData['mode'] ?? null;
                                                                    $serviceCategory = $transferData['service_category'] ?? null;
                                                                    $tax = $transferData['Tax'] ?? $transferData['tax'] ?? null;
                                                                @endphp
                                                                
                                                                <div class="transfer-info-grid">
                                                                    @if(!empty($bookingDate))
                                                                        <div class="transfer-info-item">
                                                                            <small class="text-muted">
                                                                                <i class="fas fa-calendar-alt me-1"></i>
                                                                                {{ date('d M Y', strtotime($bookingDate)) }}
                                                                                @if(!empty($entryTime))
                                                                                    • {{ $entryTime }}
                                                                                @endif
                                                                                @if(!empty($selectedHours))
                                                                                    • {{ $selectedHours }}h
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                    @endif
                                                                    
                                                                    @if(!empty($totalPrice))
                                                                        <div class="transfer-info-item">
                                                                            <small class="text-success fw-bold">
                                                                                <i class="fas fa-dollar-sign me-1"></i>
                                                                                SGD {{ number_format($totalPrice, 2) }}
                                                                                @if(!empty($tax) && $tax > 0)
                                                                                    <span class="tax-info">+{{ number_format($tax, 2) }} tax</span>
                                                                                @endif
                                                                                @if(!empty($mode))
                                                                                    <span class="price-mode-badge">{{ ucfirst($mode) }}</span>
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                
                                                                <!-- Transportation Information -->
                                                                @if(!empty($entryPickup) || !empty($dropoffLocation) || !empty($vehicleName))
                                                                    <div class="transportation-summary mt-2">
                                                                        <div class="transportation-header">
                                                                            <small class="text-primary fw-semibold">
                                                                                <i class="fas fa-car-side me-1"></i>
                                                                                Transportation Details
                                                                                @if(!empty($serviceCategory))
                                                                                    <span class="service-category-badge">{{ ucwords(str_replace('_', ' ', $serviceCategory)) }}</span>
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                        
                                                                        <div class="transportation-item mt-1">
                                                                            @if(!empty($entryPickup))
                                                                                <div class="transport-pickup-info">
                                                                                    <small class="text-dark">
                                                                                        <i class="fas fa-map-marker-alt me-1 text-success"></i>
                                                                                        <strong>From:</strong> {{ $entryPickup }}
                                                                                    </small>
                                                                                </div>
                                                                            @endif
                                                                            
                                                                            @if(!empty($dropoffLocation))
                                                                                <div class="transport-dropoff-info mt-1">
                                                                                    <small class="text-dark">
                                                                                        <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                                                                        <strong>To:</strong> {{ $dropoffLocation }}
                                                                                    </small>
                                                                                </div>
                                                                            @endif
                                                                            
                                                                            <!-- Vehicle Information -->
                                                                            @if(!empty($vehicleName) || !empty($vehicleType) || !empty($selectedHours))
                                                                                <div class="transport-vehicle-info mt-1">
                                                                                    <small class="text-muted">
                                                                                        <i class="fas fa-car me-1"></i>
                                                                                        @if(!empty($vehicleName))
                                                                                            {{ $vehicleName }}
                                                                                        @endif
                                                                                        @if(!empty($vehicleType))
                                                                                            @if(!empty($vehicleName)) • @endif
                                                                                            {{ ucfirst($vehicleType) }}
                                                                                        @endif
                                                                                        @if(!empty($selectedHours))
                                                                                            @if(!empty($vehicleName) || !empty($vehicleType)) • @endif
                                                                                            {{ $selectedHours }} Hour{{ $selectedHours > 1 ? 's' : '' }}
                                                                                        @endif
                                                                                        @if(!empty($adults) || !empty($children))
                                                                                            • 
                                                                                            @if(!empty($adults))
                                                                                                {{ $adults }} Adult{{ $adults > 1 ? 's' : '' }}
                                                                                            @endif
                                                                                            @if(!empty($children))
                                                                                                @if(!empty($adults)) + @endif
                                                                                                {{ $children }} Child{{ $children > 1 ? 'ren' : '' }}
                                                                                            @endif
                                                                                        @endif
                                                                                    </small>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @elseif(strpos(strtolower($serviceType), 'entry') !== false || strtolower($serviceType) == 'arrival')
                                                        <p class="service-description">Airport arrival transfer service</p>
                                                        
                                                        <!-- Enhanced Entry Port Details -->
                                                        <div class="entry-port-details mt-2">
                                                            @php 
                                                                // $data is already the entry port item data
                                                                $entryPortData = $data;
                                                            @endphp
                                                            
                                                            @if($entryPortData && is_array($entryPortData))
                                                                @php
                                                                    // Check for different possible field names
                                                                    $pickupDate = $entryPortData['pickupdate'] ?? $entryPortData['pickup_date'] ?? $entryPortData['bookingDate'] ?? null;
                                                                    $totalPrice = $entryPortData['totalPrice'] ?? $entryPortData['total_price'] ?? $entryPortData['price'] ?? null;
                                                                    $entryDropoff = $entryPortData['entrydropoff'] ?? $entryPortData['entry_dropoff'] ?? $entryPortData['dropoff'] ?? null;
                                                                    $entryPickup = $entryPortData['entrypickup'] ?? $entryPortData['entry_pickup'] ?? $entryPortData['pickup'] ?? null;
                                                                    $entryTime = $entryPortData['entrytime'] ?? $entryPortData['entry_time'] ?? $entryPortData['time'] ?? null;
                                                                    $vehicleName = $entryPortData['vehicles_name'] ?? $entryPortData['vehicle_name'] ?? null;
                                                                    $vehicleType = $entryPortData['type'] ?? $entryPortData['vehicle_type'] ?? null;
                                                                    $adults = $entryPortData['adults'] ?? $entryPortData['adult_count'] ?? null;
                                                                    $children = $entryPortData['children'] ?? $entryPortData['child_count'] ?? null;
                                                                    $mode = $entryPortData['Mode'] ?? $entryPortData['mode'] ?? null;
                                                                @endphp
                                                                
                                                                <div class="entry-port-info-grid">
                                                                    @if(!empty($pickupDate))
                                                                        <div class="entry-port-info-item">
                                                                            <small class="text-muted">
                                                                                <i class="fas fa-calendar-alt me-1"></i>
                                                                                {{ date('d M Y', strtotime($pickupDate)) }}
                                                                                @if(!empty($entryTime))
                                                                                    • {{ $entryTime }}
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                    @endif
                                                                    
                                                                    @if(!empty($totalPrice))
                                                                        <div class="entry-port-info-item">
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
                                                                
                                                                <!-- Transfer Information -->
                                                                @if(!empty($entryPickup) || !empty($entryDropoff) || !empty($vehicleName))
                                                                    <div class="transfer-summary mt-2">
                                                                        <div class="transfer-header">
                                                                            <small class="text-primary fw-semibold">
                                                                                <i class="fas fa-route me-1"></i>
                                                                                Transfer Details
                                                                            </small>
                                                                        </div>
                                                                        
                                                                        <div class="transfer-item mt-1">
                                                                            @if(!empty($entryPickup))
                                                                                <div class="pickup-info">
                                                                                    <small class="text-dark">
                                                                                        <i class="fas fa-map-marker-alt me-1 text-success"></i>
                                                                                        <strong>From:</strong> {{ $entryPickup }}
                                                                                    </small>
                                                                                </div>
                                                                            @endif
                                                                            
                                                                            @if(!empty($entryDropoff))
                                                                                <div class="dropoff-info mt-1">
                                                                                    <small class="text-dark">
                                                                                        <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                                                                        <strong>To:</strong> {{ $entryDropoff }}
                                                                                    </small>
                                                                                </div>
                                                                            @endif
                                                                            
                                                                            <!-- Vehicle Information -->
                                                                            @if(!empty($vehicleName) || !empty($vehicleType))
                                                                                <div class="vehicle-info mt-1">
                                                                                    <small class="text-muted">
                                                                                        <i class="fas fa-car me-1"></i>
                                                                                        @if(!empty($vehicleName))
                                                                                            {{ $vehicleName }}
                                                                                        @endif
                                                                                        @if(!empty($vehicleType))
                                                                                            @if(!empty($vehicleName)) • @endif
                                                                                            {{ ucfirst($vehicleType) }}
                                                                                        @endif
                                                                                        @if(!empty($adults) || !empty($children))
                                                                                            • 
                                                                                            @if(!empty($adults))
                                                                                                {{ $adults }} Adult{{ $adults > 1 ? 's' : '' }}
                                                                                            @endif
                                                                                            @if(!empty($children))
                                                                                                @if(!empty($adults)) + @endif
                                                                                                {{ $children }} Child{{ $children > 1 ? 'ren' : '' }}
                                                                                            @endif
                                                                                        @endif
                                                                                    </small>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @elseif(strpos(strtolower($serviceType), 'exit') !== false || strtolower($serviceType) == 'departure')
                                                        <p class="service-description">Airport departure transfer service</p>
                                                        
                                                        <!-- Enhanced Exit Port Details -->
                                                        <div class="exit-port-details mt-2">
                                                            @php 
                                                                // $data is already the exit port item data
                                                                $exitPortData = $data;
                                                            @endphp
                                                            
                                                            @if($exitPortData && is_array($exitPortData))
                                                                @php
                                                                    // Check for different possible field names
                                                                    $pickupDate = $exitPortData['pickupdate'] ?? $exitPortData['pickup_date'] ?? $exitPortData['bookingDate'] ?? null;
                                                                    $totalPrice = $exitPortData['totalPrice'] ?? $exitPortData['total_price'] ?? $exitPortData['price'] ?? null;
                                                                    $entryDropoff = $exitPortData['entrydropoff'] ?? $exitPortData['entry_dropoff'] ?? $exitPortData['dropoff'] ?? null;
                                                                    $entryPickup = $exitPortData['entrypickup'] ?? $exitPortData['entry_pickup'] ?? $exitPortData['pickup'] ?? null;
                                                                    $entryTime = $exitPortData['entrytime'] ?? $exitPortData['entry_time'] ?? $exitPortData['time'] ?? null;
                                                                    $vehicleName = $exitPortData['vehicles_name'] ?? $exitPortData['vehicle_name'] ?? null;
                                                                    $vehicleType = $exitPortData['type'] ?? $exitPortData['vehicle_type'] ?? null;
                                                                    $adults = $exitPortData['adults'] ?? $exitPortData['adult_count'] ?? null;
                                                                    $children = $exitPortData['children'] ?? $exitPortData['child_count'] ?? null;
                                                                    $mode = $exitPortData['Mode'] ?? $exitPortData['mode'] ?? null;
                                                                @endphp
                                                                
                                                                <div class="exit-port-info-grid">
                                                                    @if(!empty($pickupDate))
                                                                        <div class="exit-port-info-item">
                                                                            <small class="text-muted">
                                                                                <i class="fas fa-calendar-alt me-1"></i>
                                                                                {{ date('d M Y', strtotime($pickupDate)) }}
                                                                                @if(!empty($entryTime))
                                                                                    • {{ $entryTime }}
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                    @endif
                                                                    
                                                                    @if(!empty($totalPrice))
                                                                        <div class="exit-port-info-item">
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
                                                                
                                                                <!-- Departure Transfer Information -->
                                                                @if(!empty($entryPickup) || !empty($entryDropoff) || !empty($vehicleName))
                                                                    <div class="departure-summary mt-2">
                                                                        <div class="departure-header">
                                                                            <small class="text-primary fw-semibold">
                                                                                <i class="fas fa-plane-departure me-1"></i>
                                                                                Departure Details
                                                                            </small>
                                                                        </div>
                                                                        
                                                                        <div class="departure-item mt-1">
                                                                            @if(!empty($entryPickup))
                                                                                <div class="departure-pickup-info">
                                                                                    <small class="text-dark">
                                                                                        <i class="fas fa-map-marker-alt me-1 text-success"></i>
                                                                                        <strong>From:</strong> {{ $entryPickup }}
                                                                                    </small>
                                                                                </div>
                                                                            @endif
                                                                            
                                                                            @if(!empty($entryDropoff))
                                                                                <div class="departure-dropoff-info mt-1">
                                                                                    <small class="text-dark">
                                                                                        <i class="fas fa-plane me-1 text-primary"></i>
                                                                                        <strong>To:</strong> {{ $entryDropoff }}
                                                                                    </small>
                                                                                </div>
                                                                            @endif
                                                                            
                                                                            <!-- Vehicle Information -->
                                                                            @if(!empty($vehicleName) || !empty($vehicleType))
                                                                                <div class="departure-vehicle-info mt-1">
                                                                                    <small class="text-muted">
                                                                                        <i class="fas fa-car me-1"></i>
                                                                                        @if(!empty($vehicleName))
                                                                                            {{ $vehicleName }}
                                                                                        @endif
                                                                                        @if(!empty($vehicleType))
                                                                                            @if(!empty($vehicleName)) • @endif
                                                                                            {{ ucfirst($vehicleType) }}
                                                                                        @endif
                                                                                        @if(!empty($adults) || !empty($children))
                                                                                            • 
                                                                                            @if(!empty($adults))
                                                                                                {{ $adults }} Adult{{ $adults > 1 ? 's' : '' }}
                                                                                            @endif
                                                                                            @if(!empty($children))
                                                                                                @if(!empty($adults)) + @endif
                                                                                                {{ $children }} Child{{ $children > 1 ? 'ren' : '' }}
                                                                                            @endif
                                                                                        @endif
                                                                                    </small>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @elseif(strtolower($serviceType) == 'attraction')
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
                                                                    
                                                                    @if(!empty($totalPrice))
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
                                                                            @if(isset($ticketDetails) && is_array($ticketDetails))
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
                                                    @elseif(strtolower($serviceType) == 'restaurant')
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
                                                                    
                                                                    @if(!empty($totalPrice))
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
                                                                                        @if(!empty($mealPrice))
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
                                                                                            @if($transportPrice > 0)
                                                                                                Transport Included (+SGD {{ number_format($transportPrice, 2) }})
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
                                                </div>
                                                
                                                <div class="service-details-row">
                                                    <div class="service-pax-badge">{{ $pax }}</div>
                                                </div>
                                            </div>
                                            
                                            <!-- Right Side Details -->
                                            <div class="service-right-details">
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
        
        // Print functionality
        document.getElementById('printItinerary').addEventListener('click', function() {
            preparePrint();
            setTimeout(function() {
                window.print();
            }, 300);
        });
        
        document.getElementById('printItineraryMobile').addEventListener('click', function() {
            preparePrint();
            setTimeout(function() {
                window.print();
            }, 300);
        });
        
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
                background: ${type === 'success' ? '#28a745' : '#dc3545'};
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            `;
            messageDiv.textContent = message;
            document.body.appendChild(messageDiv);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                messageDiv.remove();
            }, 3000);
        }
    });
</script>
@endsection 