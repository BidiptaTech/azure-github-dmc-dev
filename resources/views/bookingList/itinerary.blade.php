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
                        <a href="{{ route('bookinglist.index') }}" class="btn-modern btn-secondary-modern">
                            <i class="fas fa-arrow-left"></i> Back to Bookings
                        </a>
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
                                        if (strtolower($serviceType) == 'entry port') {
                                            $serviceType = 'Arrival';
                                        } elseif (strtolower($serviceType) == 'exit port') {
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
                                            if (!empty($data['attractionname'])) {
                                                $serviceName = $data['attractionname'];
                                            } elseif (!empty($data['name'])) {
                                                $serviceName = $data['name'];
                                            } else {
                                                $serviceName = 'Attraction';
                                            }
                                        } elseif (strtolower($serviceType) == 'restaurant') {
                                            // For restaurants
                                            if (!empty($data['restaurantname'])) {
                                                $serviceName = $data['restaurantname'];
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
                                        
                                        // Format time to match example (04:00 PM) with a default value
                                        if (!$timeSlot) {
                                            // Default time for examples
                                            $exampleTimes = [
                                                'entry port' => '04:00 PM',
                                                'guide' => '14:02',
                                                'default' => '12:00 PM'
                                            ];
                                            
                                            $timeSlot = $exampleTimes[strtolower($serviceType)] ?? $exampleTimes['default'];
                                        } elseif (strpos($timeSlot, 'AM') === false && strpos($timeSlot, 'PM') === false) {
                                            // Convert 24-hour format to 12-hour format if needed
                                            $timeParts = explode(':', $timeSlot);
                                            $hour = (int)$timeParts[0];
                                            $min = isset($timeParts[1]) ? (int)$timeParts[1] : 0;
                                            
                                            $suffix = ($hour >= 12) ? 'PM' : 'AM';
                                            $hour = ($hour > 12) ? $hour - 12 : $hour;
                                            $hour = ($hour == 0) ? 12 : $hour; // Handle midnight
                                            
                                            $timeSlot = sprintf('%02d:%02d %s', $hour, $min, $suffix);
                                        }
                                        
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
                                                    @elseif(strtolower($serviceType) == 'guide')
                                                        <p class="service-description">Professional tour guide service</p>
                                                    @elseif(strpos(strtolower($serviceType), 'transfer') !== false)
                                                        <p class="service-description">Private transportation service</p>
                                                    @elseif(strpos(strtolower($serviceType), 'entry') !== false || strtolower($serviceType) == 'arrival')
                                                        <p class="service-description">Airport arrival transfer service</p>
                                                    @elseif(strpos(strtolower($serviceType), 'exit') !== false || strtolower($serviceType) == 'departure')
                                                        <p class="service-description">Airport departure transfer service</p>
                                                    @elseif(strtolower($serviceType) == 'attraction')
                                                        <p class="service-description">Sightseeing and attraction visit</p>
                                                    @elseif(strtolower($serviceType) == 'restaurant')
                                                        <p class="service-description">Dining experience</p>
                                                    @endif
                                                </div>
                                                
                                                <div class="service-details-row">
                                                    <div class="service-time-badge">{{ $timeSlot }}</div>
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