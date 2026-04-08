<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<style>
    /* Modern Sidebar Styling */
    .layout-menu {
        background: #ffffff;
        border-right: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }
    
    /* Logo area styling */
    .app-brand {
        padding: 0.95rem 1rem;
        min-height: 74px;
        height: auto;
        background: linear-gradient(135deg, #6366f1, #8b5cf6); 
        margin-bottom: 1rem;
        box-shadow: 0 4px 12px rgba(107, 114, 241, 0.2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }
    .app-brand-link {
        display: flex;
        align-items: center;
        flex: 1 1 auto;
        min-width: 0;
        max-width: calc(100% - 56px);
        gap: 0.55rem;
    }
    
    .app-brand-text {
        color: white !important;
        font-weight: 700 !important;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        min-width: 0;
    }
    
    /* Menu header styling */
    .menu-header {
        margin-top: 1.5rem !important;
        padding: 0.5rem 1.5rem !important;
    }
    
    .menu-header-text {
        color: #6366f1 !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    /* Menu item styling */
    .menu-item {
        margin: 6px 8px;
        border-radius: 12px;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .menu-item.active {
        background: linear-gradient(118deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
        box-shadow: 0 2px 10px rgba(99, 102, 241, 0.12);
    }
    
    .menu-link {
        padding: 0.8rem 1.5rem;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    
    .menu-link:hover {
        background-color: rgba(99, 102, 241, 0.04);
        transform: translateX(4px);
    }
    
    .menu-item.active .menu-link {
        box-shadow: none;
        color: #ffffff !important;
    }
    
    .menu-item.active .menu-link div {
        color: #ffffff !important;
        font-weight: 600;
    }
    
    /* Colorful Icon styling */
    .menu-icon {
        margin-right: 0.75rem;
        font-size: 1.25rem !important;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
        border-radius: 10px;
        background: #f9fafb;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    /* Individual icon colors */
    .ri-dashboard-3-line {
        color: #f97316 !important; /* Orange */
        background: rgba(249, 115, 22, 0.1);
    }
    
    .ri-ship-line {
        color: #0ea5e9 !important; /* Sky blue */
        background: rgba(14, 165, 233, 0.1);
    }
    
    .ri-map-pin-user-line {
        color: #ec4899 !important; /* Pink */
        background: rgba(236, 72, 153, 0.1);
    }
    
    .ri-route-line {
        color: #14b8a6 !important; /* Teal */
        background: rgba(20, 184, 166, 0.1);
    }
    
    .ri-bookmark-3-line {
        color: #8b5cf6 !important; /* Purple */
        background: rgba(139, 92, 246, 0.1);
    }
    
    .ri-questionnaire-line {
        color: #f43f5e !important; /* Rose */
        background: rgba(244, 63, 94, 0.1);
    }
    
    .ri-hotel-bed-line {
        color: #10b981 !important; /* Emerald */
        background: rgba(16, 185, 129, 0.1);
    }
    
    .ri-shield-check-line {
        color: #6366f1 !important; /* Indigo */
        background: rgba(99, 102, 241, 0.1);
    }
    
    .ri-function-line {
        color: #0284c7 !important; /* Blue */
        background: rgba(2, 132, 199, 0.1);
    }
    
    .ri-hotel-line {
        color: #0d9488 !important; /* Teal */
        background: rgba(13, 148, 136, 0.1);
    }
    
    .ri-camera-3-line {
        color: #8b5cf6 !important; /* Purple */
        background: rgba(139, 92, 246, 0.1);
    }
    
    .ri-stack-line {
        color: #10b981 !important; /* Emerald */
        background: rgba(16, 185, 129, 0.1);
    }
    
    .ri-restaurant-2-line {
        color: #ea580c !important; /* Orange */
        background: rgba(234, 88, 12, 0.1);
    }
    
    .ri-compass-3-line {
        color: #0369a1 !important; /* Blue */
        background: rgba(3, 105, 161, 0.1);
    }
    
    .ri-steering-2-line {
        color: #4f46e5 !important; /* Indigo */
        background: rgba(79, 70, 229, 0.1);
    }
    
    .ri-task-line {
        color: #be123c !important; /* Rose */
        background: rgba(190, 18, 60, 0.1);
    }
    
    .ri-user-line {
        color: #7c3aed !important; /* Violet */
        background: rgba(124, 58, 237, 0.1);
    }
    
    .ri-settings-3-line {
        color: #3b82f6 !important; /* Blue */
        background: rgba(59, 130, 246, 0.1);
    }

    .ri-mail-send-line {
        color: #39c262 !important; /* Blue */
        background: rgba(59, 130, 246, 0.1);
    }
    
    .ri-earth-line {
        color: #059669 !important; /* Emerald */
        background: rgba(5, 150, 105, 0.1);
    }
    
    .ri-bar-chart-box-line {
        color: #7c2d12 !important; 
        background: rgba(124, 45, 18, 0.1);
    }

    .ri-file-list-3-line {
        color: #2c54a0 !important; /* Amber */
        background: rgba(124, 45, 18, 0.1);
    }
    
    .ri-hand-heart-line {
        color: #e11d48 !important; /* Rose */
        background: rgba(225, 29, 72, 0.1);
    }
    
    /* Active icon state with shine effect */
    .menu-item.active .menu-icon {
        color: white !important;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        box-shadow: 0 3px 10px rgba(99, 102, 241, 0.3);
        transform: translateY(-2px);
    }

    /* Pro Badge - Top Right Corner Like Verified Badge */
    .pro-badge {
        position: absolute !important;
        top: 8px;
        right: 12px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #FFD700, #FFA500) !important;
        color: #ffffff !important;
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 8px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        box-shadow: 0 2px 8px rgba(255, 215, 0, 0.5);
        z-index: 10;
        animation: pulse-star 2s ease-in-out infinite;
    }
    
    .menu-item.active .pro-badge {
        background: linear-gradient(135deg, #FFD700, #FFA500) !important;
        color: #ffffff !important;
        box-shadow: 0 2px 12px rgba(255, 215, 0, 0.6);
    }
    
    .menu-item:hover .pro-badge {
        transform: scale(1.05);
        box-shadow: 0 3px 15px rgba(255, 215, 0, 0.7);
    }
    
    @keyframes pulse-star {
        0%, 100% {
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.5);
        }
        50% {
            box-shadow: 0 2px 15px rgba(255, 215, 0, 0.8);
        }
    }
    
    /* Submenu styling */
    .menu-sub {
        padding-left: 3.35rem !important;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .menu-sub .menu-link {
        padding: 0.6rem 1.5rem;
        color: #000;
        font-size: 0.92rem;
    }
    
    .menu-sub .menu-link:hover {
        color: #6366f1;
    }
    
    /* Menu toggle icon animation */
    .menu-toggle:after {
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        color: #94a3b8;
    }
    
    .menu-item.open > .menu-toggle:after {
        transform: rotate(90deg);
        color: #6366f1;
    }
    
    /* Menu section hover effect */
    .menu-item.open {
        background-color: rgba(99, 102, 241, 0.04);
        border-radius: 12px;
    }
    
    /* Custom scrollbar */
    .menu-inner::-webkit-scrollbar {
        width: 5px;
    }
    
    .menu-inner::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .menu-inner::-webkit-scrollbar-thumb {
        background-color: rgba(99, 102, 241, 0.2);
        border-radius: 10px;
    }
    
    .menu-inner::-webkit-scrollbar-thumb:hover {
        background-color: rgba(99, 102, 241, 0.4);
    }
    
    /* Smooth hover transitions */
    .menu-link, .menu-icon {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Reset parent menu color */
    .menu-item.open > .menu-link {
        color: #6366f1 !important; /* Keep parent menu normal color */
    }
    
    .menu-item.open > .menu-link div {
        color: #6366f1 !important;
    }
    
    /* Only make the specific active item white */
    .menu-item.active:not(.open) > .menu-link {
        color: #ffffff !important;
    }
    
    .menu-item.active:not(.open) > .menu-link div {
        color: #ffffff !important;
    }
    
    /* For submenu items that are active */
    .menu-sub .menu-item.active .menu-link {
        color: #ffffff !important;
        /* background: linear-gradient(118deg, #4bbca5, #725aab); */
        background: linear-gradient(25deg, #c851ec, #566ee4);
        box-shadow: 0 2px 10px rgba(99, 102, 241, 0.3);
    }
    
    .menu-sub .menu-item.active .menu-link div {
        color: #ffffff !important;
    }
    
    /* Ensure other submenu items are not white */
    .menu-sub .menu-item:not(.active) .menu-link {
        color: #000 !important;
    }
    
    .menu-sub .menu-item:not(.active) .menu-link div {
        color: #000 !important;
    }

    .menu-vertical .menu-item .menu-link{
        font-weight: bold;
    }

    /* Ensure menu items show full text without truncation */
    .menu-vertical .menu-item .menu-link > div:not(.badge) {
        overflow: visible !important;
        text-overflow: inherit !important;
        white-space: wrap !important;
        word-wrap: break-word !important;
        line-height: 1.467;
        hyphens: auto;
    }

    /* Additional override for menu text in tooltips */
    .menu-tooltip div[data-i18n] {
        overflow: visible !important;
        text-overflow: inherit !important;
        white-space: wrap !important;
        word-wrap: break-word !important;
    }

    /* Override any ellipsis display in menu items */
    .menu-item .menu-link div,
    .menu-item .menu-link span.menu-text-with-tooltip {
        overflow: visible !important;
        text-overflow: inherit !important;
        white-space: wrap !important;
        word-wrap: break-word !important;
        text-overflow: initial !important;
    }

    #template-customizer .template-customizer-open-btn{
        display: none !important;
    }

    /* 3D Menu Toggle Icon Styling */
    .layout-menu-toggle {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
    }

    .layout-menu-toggle .menu-icon {
        padding: 10px;
        border-radius: 12px;
        background: linear-gradient(145deg, #ffffff, #f0f0f0);
        box-shadow: 4px 4px 8px rgba(0,0,0,0.1), 
                    -4px -4px 8px rgba(255,255,255,0.9);
        color: #6366f1 !important;
        font-size: 1.4rem !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(99, 102, 241, 0.1);
    }

    .layout-menu-toggle:hover .menu-icon {
        transform: translateY(-3px) rotate(180deg);
        box-shadow: 6px 6px 10px rgba(0,0,0,0.15), 
                    -6px -6px 10px rgba(255,255,255,0.95);
        background: linear-gradient(145deg, #6366f1, #8b5cf6);
        color: white !important;
    }

    .layout-menu-toggle:active .menu-icon {
        transform: translateY(1px);
        box-shadow: 2px 2px 5px rgba(0,0,0,0.1), 
                    -2px -2px 5px rgba(255,255,255,0.8);
    }

    .rounded-logo {
        width: 46px;
        height: 46px;
        object-fit: cover;
        border-radius: 50%; /* Makes it a perfect circle */
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2),
                    inset 0 0 10px rgba(255, 255, 255, 0.2); /* 3D inner & outer */
        border: 3px solid #ffffff;
        background-color: #f9f9f9;
        transition: transform 0.3s ease;
        }

        .rounded-logo:hover {
        transform: scale(1.05) rotate(1deg);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.25),
                    inset 0 0 12px rgba(255, 255, 255, 0.3);
        }

    .small-brand-text {
        display: inline-block;
        max-width: 122px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.1px;
        line-height: 1.1;
        vertical-align: middle;
    }
    .layout-menu-toggle {
        flex: 0 0 auto;
        margin-left: 0.35rem;
    }

    .ri-gift-line {
        color: #f59e0b !important; /* Amber */
        background: rgba(245, 158, 11, 0.1);
    }
    
    .ri-service-line {
        color: #06b6d4 !important; /* Cyan */
        background: rgba(6, 182, 212, 0.1);
    }
    
    .ri-building-line {
        color: #7c3aed !important; /* Violet */
        background: rgba(124, 58, 237, 0.1);
    }
    
    .ri-car-line {
        color: #dc2626 !important; /* Red */
        background: rgba(220, 38, 38, 0.1);
    }
    
    .ri-percent-line {
        color: #16a34a !important; /* Green */
        background: rgba(22, 163, 74, 0.1);
    }
    
    .roadmap-icon:hover {
  color: #2ecc71;
  transform: scale(1.1);
  transition: 0.3s;
 }

    /* Menu Text with Full Display */
    .menu-text-with-tooltip {
        white-space: wrap;
        overflow: visible;
        text-overflow: inherit;
        max-width: 100%;
        position: relative;
        word-wrap: break-word;
        line-height: 1.3;
    }

    /* Tooltip Container */
    .menu-tooltip {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    /* Tooltip Text */
    .menu-tooltip .tooltip-text {
        visibility: hidden;
        opacity: 0;
        background: linear-gradient(135deg, #1f2937, #374151);
        color: white;
        text-align: left;
        border-radius: 8px;
        padding: 10px 15px;
        position: fixed;
        z-index: 999999;
        font-size: 14px;
        font-weight: 500;
        white-space: nowrap;
        min-width: max-content;
        max-width: 350px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
        border: 2px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        transform: scale(0.8);
        pointer-events: none;
        left: 280px;
        top: 100px;
    }

    /* Tooltip Arrow */
    .menu-tooltip .tooltip-text::before {
        content: "";
        position: absolute;
        top: 50%;
        left: -8px;
        transform: translateY(-50%);
        border-top: 8px solid transparent;
        border-bottom: 8px solid transparent;
        border-right: 8px solid #1f2937;
        filter: drop-shadow(-2px 0 4px rgba(0, 0, 0, 0.1));
    }

    /* Show Tooltip on Hover */
    .menu-tooltip:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
        transform: translateX(0);
    }

    /* Prevent tooltip from interfering with menu functionality */
    .menu-tooltip .tooltip-text {
        user-select: none;
    }

    /* Force tooltip visibility on hover - CSS fallback */
    .menu-tooltip:hover .tooltip-text {
        visibility: visible !important;
        opacity: 1 !important;
        transform: scale(1) !important;
        display: block !important;
    }

    /* Responsive tooltip positioning */
    @media (max-width: 768px) {
        .menu-tooltip .tooltip-text {
            font-size: 12px;
            padding: 6px 10px;
            margin-left: 10px;
        }
    }

    /* Better tooltip positioning for smaller screens */
    @media (max-width: 1200px) {
        .menu-tooltip .tooltip-text {
            left: 100%;
            margin-left: 10px;
        }
    }

    /* Special styling for truncated text */
    .truncated-text {
        position: relative;
    }

    .truncated-text:hover {
        cursor: help;
    }

    /* Debug styles - add red border to see tooltip areas */
    .menu-tooltip {
        position: relative;
    }
    
    /* Ensure proper z-index stacking */
    .layout-menu .menu-tooltip .tooltip-text {
        z-index: 99999 !important;
    }

    /* Booking Count Badge Styling */
    .menu-link .badge {
        font-size: 0.65rem !important;
        padding: 0.25rem 0.5rem !important;
        font-weight: 600 !important;
        min-width: 1.5rem;
        height: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1 !important;
        border: 2px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        animation: pulseCount 2s infinite;
    }

    @keyframes pulseCount {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
        }
    }

    /* Active menu item badge styling */
    .menu-item.active .menu-link .badge {
        background-color: #dc3545 !important;
        color: #ffffff !important;
        border-color: #dc3545 !important;
    }

    /* Submenu badge styling */
    .menu-sub .menu-link .badge {
        font-size: 0.6rem !important;
        padding: 0.2rem 0.4rem !important;
        min-width: 1.2rem;
        height: 1.2rem;
    }

    /* Prevent badge from interfering with hover effects */
    .menu-link .badge {
        pointer-events: none;
        flex-shrink: 0;
    }

    /* Ensure proper spacing in menu items with badges */
    .menu-link .d-flex {
        width: 100%;
        min-height: 1.5rem;
        align-items: center;
    }

    /* PRO badge (yellow/orange) - Refined Design */
    .badge-pro {
        position: absolute !important;
        right: 10px;
        top: 6px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #FFD700, #FFA500) !important;
        color: #ffffff !important;
        font-size: 8.5px;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        padding: 3px 7px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(255, 215, 0, 0.4), 
                    0 1px 2px rgba(0, 0, 0, 0.15);
        z-index: 10;
        transition: all 0.2s ease;
        border: 1px solid rgba(255, 255, 255, 0.25);
        min-width: 28px;
        text-align: center;
        line-height: 1.2;
    }
    
    .menu-item.active .badge-pro {
        background: linear-gradient(135deg, #FFD700, #FFA500) !important;
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(255, 215, 0, 0.5),
                    0 0 6px rgba(255, 215, 0, 0.3);
    }
    
    .menu-item:hover .badge-pro {
        transform: scale(1.05);
        box-shadow: 0 2px 10px rgba(255, 215, 0, 0.5),
                    0 0 8px rgba(255, 215, 0, 0.35);
    }

    /* LITE badge (blue) - Refined Design */
    .badge-lite {
        position: absolute !important;
        right: 10px;
        top: 6px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #5b6cff, #4c63d2) !important;
        color: #ffffff !important;
        font-size: 8.5px;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        padding: 3px 7px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(91, 108, 255, 0.35),
                    0 1px 2px rgba(0, 0, 0, 0.15);
        z-index: 10;
        transition: all 0.2s ease;
        border: 1px solid rgba(255, 255, 255, 0.25);
        min-width: 28px;
        text-align: center;
        line-height: 1.2;
    }
    
    .menu-item.active .badge-lite {
        background: linear-gradient(135deg, #5b6cff, #4c63d2) !important;
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(91, 108, 255, 0.5),
                    0 0 6px rgba(91, 108, 255, 0.3);
    }
    
    .menu-item:hover .badge-lite {
        transform: scale(1.05);
        box-shadow: 0 2px 10px rgba(91, 108, 255, 0.5),
                    0 0 8px rgba(91, 108, 255, 0.35);
    }

    .menu-link div {
        letter-spacing: 0.4px;
        word-spacing: 1px;
    }
    
    /* Ensure menu items with badges have proper positioning */
    .menu-item:has(.badge-pro),
    .menu-item:has(.badge-lite) {
        position: relative;
    }
    
    /* Alternative for browsers that don't support :has() */
    .menu-item[style*="position: relative"] {
        position: relative !important;
    }

    /* Submenu items modal (floating panel on the right) */
    #submenuModal .modal-dialog {
        position: fixed;
        margin: 0px;
        max-width: 280px;
        width: 280px;
        transform: none;
    }

    #submenuModal .modal-content {
        border-radius: 5px;
        box-shadow: 0 18px 60px rgba(0, 0, 0, 0.25);
        border: solid 1px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }

    #submenuModal .submenu-modal-body {
        padding: 10px 0;
        background: linear-gradient(135deg,rgba(240, 219, 255, 0.9));
        font-weight: 500;
    }

    #submenuModal .submenu-modal-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    #submenuModal .submenu-modal-list .menu-item {
        margin: 6px 8px;
    }

    #submenuModal .submenu-modal-list .menu-link {
        padding: 0.65rem 1.05rem;
        border-radius: 12px;
    }

    #submenuModal .submenu-modal-list .menu-item.open > .menu-link {
        color: inherit !important;
    }

    /* Modal colors to match sidebar (indigo/purple gradient) */
     #submenuModal .modal-header {
        background: linear-gradient(135deg,rgba(207, 219, 252, 0.96)); 
        color:rgb(194, 191, 191);
    } 

    #submenuModal .modal-header .btn-close {
        filter: invert(1) grayscale(1) brightness(2);
        opacity: 0.95;
    }

    #submenuModal .submenu-modal-list .menu-link {
        color: #0f172a;
    }

    /* Keep toggle icon at the far-right in modal and avoid text overlap. */
    #submenuModal .submenu-modal-list .menu-link {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 0.55rem;
        width: 100%;
    }

    #submenuModal .submenu-modal-list .submenu-modal-item-icon {
        color: #64748b;
        font-size: 1.05rem;
        flex: 0 0 auto;
    }

    /* For submenu toggles, force the existing theme arrow (`::after`) to the far right. */
    #submenuModal .submenu-modal-list .menu-link.menu-toggle {
        padding-right: 0.75rem;
    }

    #submenuModal .submenu-modal-list .menu-link.menu-toggle::after {
        position: static !important;
        margin-left: auto !important;
        transform: rotate(0deg) !important;
        color: #64748b !important;
        flex: 0 0 auto;
    }

    #submenuModal .submenu-modal-list .menu-item.open > .menu-link.menu-toggle::after {
        transform: rotate(0deg) !important;
    }

    #submenuModal .submenu-modal-list .submenu-modal-back-link {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: flex-start;
    }

    #submenuModal .submenu-modal-list .menu-item:hover .menu-link {
        background-color: rgba(132, 134, 255, 0.97);
        transform: translateX(4px);
        color: #fff;
    }

    #submenuModal .submenu-modal-list .menu-item.active {
        background:  rgba(94, 96, 247, 0.84);
        box-shadow: 0 2px 10px rgba(114, 117, 255, 0.92);
    }
    #submenuModal .submenu-modal-list .submenu-modal-item-icon {
        font-size: 1.05rem;
        flex: 0 0 auto;
    }
    #submenuModal .submenu-modal-list .menu-item.active > .menu-link .submenu-modal-item-icon {
        color: #fff;
    }
    #submenuModal .submenu-modal-list .menu-item:hover > .menu-link .submenu-modal-item-icon {
        color: #fff;
    }

    /* Always hide inline nested submenus (2nd-level) in the sidebar.
       Nested options will be shown via `#submenuModal` only. */
    #layout-menu ul.menu-sub ul.menu-sub {
        display: none !important;
    }
    </style>
        <body>
            <div class="layout-wrapper layout-content-navbar  ">
    <div class="layout-container">
        <!-- Menu -->
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
            <div class="app-brand demo ">
                <a href="{{ route('dashboard') }}" class="app-brand-link">
                    <span class="app-brand-logo demo">
                        @php
                        $currentUser = Auth::user();
                        $brandUser = $currentUser;

                        // Resolve DMC branding for hierarchical roles (sales/finance/product/ops etc.).
                        if ($currentUser) {
                            $dmcId = \App\Helpers\CommonHelper::getDmcId($currentUser);
                            if (!empty($dmcId)) {
                                $dmcUser = \App\Models\User::where('userId', $dmcId)->first();
                                if ($dmcUser) {
                                    $brandUser = $dmcUser;
                                }
                            }
                        }

                        $masterLogo = \App\Helpers\CommonHelper::masterSettingsName('logo')['master_value'] ?? '';
                        $masterName = \App\Helpers\CommonHelper::masterSettingsName('name')['master_value'] ?? 'Dashboard';

                        $brandName = trim((string) ($brandUser->company_name ?? ''));
                        if ($brandName === '') {
                            $brandName = $masterName;
                        }

                        $brandLogo = trim((string) ($brandUser->logo ?? ''));
                        if ($brandLogo === '') {
                            $brandLogo = $masterLogo;
                        }

                        if ($brandLogo !== '' && !preg_match('/^(https?:\/\/|data:image\/)/i', $brandLogo)) {
                            $brandLogo = asset(ltrim($brandLogo, '/'));
                        }
                        @endphp
                        <div class="logo-icon">
                            <img src="{{ $brandLogo }}" class="logo-img rounded-logo" alt="Logo">
                        </div>
                        {{-- <div class="logo-name flex-grow-1">
                            <h5 class="mb-0 text-white">
                                {{ \App\Helpers\CommonHelper::masterSettingsName('name')['master_value'] }}</h5>
                        </div> --}}
                    </span>
                    <span class="app-brand-text demo menu-text fw-semibold ms-2">
                        <span class="small-brand-text" title="{{ $brandName }}">{{ $brandName }}</span>
                    </span>
                </a>
                <a href="javascript:void(0);" class="layout-menu-toggle menu-link7 text-large ms-auto" style="right: -25px;">
                    <i class="menu-icon tf-icons ri-menu-fold-line" style="margin-left: 10px"></i>
                </a>
            </div>
            <div class="menu-inner-shadow"></div>
            <!-- Dashboards -->
            <ul class="menu-inner py-1" style="padding-bottom: 100px;">
                <li class="menu-item" style="height: 8px;"></li>
                <li class="menu-item @if(Request::is('dashboard')) active @endif">
                    <a href="{{ route('dashboard') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-dashboard-3-line"></i>
                        <div data-i18n="Dashboard">Dashboard</div>
                    </a>
                </li>

                <!-- <li class="menu-item" style="height: 8px;"></li>
                @if(in_array(auth()->user()->role_id, [2, 33]))
                <li class="menu-item @if(Request::is('custom-packages*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-gift-line"></i>
                        <div data-i18n="Custom Packages">Custom Packages</div>
                    </a>
                    <ul class="menu-sub">
                        
                        <li class="menu-item @if(Request::is('custom-packages/create')) active @endif">
                            <a href="{{ route('custom-packages.create') }}" class="menu-link">
                                <div data-i18n="Create Custom Package">Create Custom Package</div>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif -->

        

        @if(in_array(auth()->user()->role_id, [33, 37, 38, 128, 129, 130, 134, 135, 136, 138]))
            <li class="menu-item @if(Request::is('enquiry-form-pro/create')) active @endif" style="position: relative;">
                <a href="{{ route('enquiry-form-pro.create') }}" class="menu-link" id="createSingleTourProBtn">
                    <i class="menu-icon tf-icons ri-file-list-3-line"></i>
                    <div data-i18n="Create Tour">Create Tour</div>
                    <span class="badge-pro">Pro</span>
                </a>
            </li> 

            <!-- Single Tour Package for DMCs -->
            <li class="menu-item @if(Request::is('single-tour-package/create')) active @endif" style="position: relative;">
                <a href="{{ route('single-tour-package.create') }}" class="menu-link">
                    <i class="menu-icon tf-icons ri-route-line"></i>
                    <div data-i18n="Create Tour">Create Tour</div>
                    <span class="badge-lite">Lite</span>
                </a>
            </li> 
        @endif

        <!-- End Tour -->

        @if(in_array(auth()->user()->role_id, [1, 2, 3, 4, 20, 21, 22, 11, 33, 34, 27,54,55,56,57,58,86,95,104,113,122,36, 37, 38, 69,70,71,72,73,87,96,105,114,123,124,125,126,127,128,129,132,133,131,134,136,137,138
        ]))
        <!-- Bookings -->
        {{-- @if(hasPermission('view booking')) --}}
        <li class="menu-header mt-5">
            <span class="menu-header-text" data-i18n="Bookings">Bookings</span>
        </li>
        
        <li class="menu-item @if(Request::is('bookings/*') && !Request::is('bookings/tentative') || Request::is('predefined-package-booking-list') || Request::is('enquirylist') || Request::is('custom-packages/*')) open active @endif">
            <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri-bookmark-3-line"></i>
                <div data-i18n="Bookings">Bookings</div>
            </a>
            <ul class="menu-sub">
                @if(in_array(auth()->user()->role_id, [1, 2, 11, 33,  12, 37, 38, 128, 129, 130, 134, 135, 136, 138]))
                    <li class="menu-item @if(Request::is('enquirylist') && !Request::is('enquiries*')) active @endif">
                        <a href="{{ route('enquirylist.index') }}" class="menu-link">
                            <div data-i18n="Quick Enquiry">Quick Enquiry</div>
                        </a>
                    </li>
                
                    <li class="menu-item @if(Request::is('bookings/new-enquiries') || Request::is('custom-packages/create')) open active @endif">
                        <a href="#" class="menu-link menu-toggle">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <span data-i18n="Enquiries">Enquiries</span>
                                @if(isset($bookingCounts) && $bookingCounts['new_enquiries'] > 0)
                                    <span class="badge bg-danger rounded-pill text-white ms-2">{{ $bookingCounts['new_enquiries'] }}</span>
                                @endif
                            </div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item @if(Request::is('bookings/new-enquiries')) active @endif">
                                <a href="{{ route('bookings.new-enquiries') }}" class="menu-link">
                                    <div data-i18n="Custom Itenerary">Custom Itenerary</div>
                                </a>
                            </li>
                            <!-- <li class="menu-item @if(Request::is('custom-packages/create')) active @endif">
                                <a href="{{ route('custom-packages.create') }}" class="menu-link">
                                    <div data-i18n="Custom Itinerary">Custom Itinerary</div>
                                </a>
                            </li> -->
                        </ul>
                    </li>
                    <!-- Show Booking -->
                    <li class="menu-item @if(Request::is('bookings/follow-ups')) active @endif">
                        <a href="#" class="menu-link menu-toggle">
                            <div class="d-flex justify-content-between align-items-center">
                                <span data-i18n="Follow Ups">Follow Ups</span>
                                @if(isset($bookingCounts) && $bookingCounts['follow_ups'] > 0)
                                    <span class="badge bg-danger rounded-pill text-white ms-2">{{ $bookingCounts['follow_ups'] }}</span>
                                @endif
                            </div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item @if(Request::is('bookings/follow-ups')) active @endif">
                                <a href="{{ route('bookings.follow-ups') }}" class="menu-link">
                                    <div data-i18n="Custom Itenerary">Custom Itenerary</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
                @if(in_array(auth()->user()->role_id, [1,2,11,12,26,33,34,36,37,38,49,50,51,52,53,64,65,66,67,68,69,70,71,72,73,80,81,87,89,90,96,98,99,105,107,108,114,116,117,123,124,125,126,127,128,129,131,132,133,134,135,136,137,138]))
                    <li class="menu-item @if(Request::is('bookings/confirmed')) active @endif">
                        <a href="#" class="menu-link menu-toggle">
                            <div class="d-flex justify-content-between align-items-center">
                                <span data-i18n="Confirmed">Confirmed</span>
                                @if(isset($bookingCounts) && $bookingCounts['confirmed'] > 0) 
                                    <span class="badge bg-danger rounded-pill text-white ms-2">{{ $bookingCounts['confirmed'] }}</span>
                                @endif
                            </div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item @if(Request::is('bookings/confirmed')) active @endif">
                                <a href="{{ route('bookings.confirmed') }}" class="menu-link">
                                    <div data-i18n="Custom Itenerary">Custom Itenerary</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                
                    <li class="menu-item @if(Request::is('bookings/definite')) active @endif">
                        <a href="#" class="menu-link menu-toggle">
                            <div class="d-flex justify-content-between align-items-center">
                                <span data-i18n="Definite">Definite</span>
                                @if(isset($bookingCounts) && $bookingCounts['definite'] > 0)
                                    <span class="badge bg-danger rounded-pill text-white ms-2">{{ $bookingCounts['definite'] }}</span>
                                @endif
                            </div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item @if(Request::is('bookings/definite')) active @endif">
                                <a href="{{ route('bookings.definite') }}" class="menu-link">
                                    <div data-i18n="Custom Itenerary">Custom Itenerary</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <li class="menu-item @if(Request::is('bookings/actual')) active @endif">
                        <a href="#" class="menu-link menu-toggle">
                            <div class="d-flex justify-content-between align-items-center">
                                <span data-i18n="Actual">Actual</span>
                                @if(isset($bookingCounts) && $bookingCounts['actual'] > 0)
                                    <span class="badge bg-danger rounded-pill text-white ms-2">{{ $bookingCounts['actual'] }}</span>
                                @endif
                            </div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item @if(Request::is('bookings/actual')) active @endif">
                                <a href="{{ route('bookings.actual') }}" class="menu-link">
                                    <div data-i18n="Custom Itenerary">Custom Itenerary</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    {{-- <li class="menu-item @if(Request::is('bookings/cancelled') || Request::is('bookings/refunds')) open active @endif">
                        <a href="#" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons ri-close-circle-line"></i>
                            <div class="d-flex justify-content-between align-items-center">
                                <span data-i18n="Cancellations & Refunds">Cancellations & Refunds</span>
                                @if(isset($bookingCounts) && ($bookingCounts['cancelled'] > 0 || $bookingCounts['refunds'] > 0))
                                    <span class="badge bg-danger rounded-pill text-white ms-2">{{ $bookingCounts['cancelled'] + $bookingCounts['refunds'] }}</span>
                                @endif
                            </div>
                        </a>
                        <ul class="menu-sub"> --}}
                            <li class="menu-item @if(Request::is('bookings/cancelled')) active @endif">
                                <a href="#" class="menu-link menu-toggle">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span data-i18n="Cancelled">Cancelled</span>
                                        @if(isset($bookingCounts) && $bookingCounts['cancelled'] > 0)
                                            <span class="badge bg-danger rounded-pill text-white ms-2">{{ $bookingCounts['cancelled'] }}</span>
                                        @endif
                                    </div>
                                </a>
                                <ul class="menu-sub">
                                    <li class="menu-item @if(Request::is('bookings/cancelled')) active @endif">
                                        <a href="{{ route('bookings.cancelled') }}" class="menu-link">
                                            <div data-i18n="Custom Itenerary">Custom Itenerary</div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="menu-item @if(Request::is('bookings/refunds')) active @endif">
                                <a href="#" class="menu-link menu-toggle">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span data-i18n="Refunds">Refunds</span>
                                        @if(isset($bookingCounts) && $bookingCounts['refunds'] > 0)
                                            <span class="badge bg-danger rounded-pill text-white ms-2">{{ $bookingCounts['refunds'] }}</span>
                                        @endif
                                    </div>
                                </a>
                                <ul class="menu-sub">
                                    <li class="menu-item @if(Request::is('bookings/refunds')) active @endif">
                                        <a href="{{ route('bookings.refunds') }}" class="menu-link">
                                            <div data-i18n="Custom Itenerary">Custom Itenerary</div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        {{-- </ul>
                    </li> --}}
                @endif

                @if(in_array(auth()->user()->role_id, [1,2,11, 33, 34, 36, 37, 38, 26,49,50,51,52,53,80,89,98,107,116,
                64,65,66,67,68,81,90,99,108,117,124,125,128,129,130,131,132,134,135,136,137,138]))
                    <!-- Show Booking -->
                    <li class="menu-item @if(Request::is('predefined-package-booking-list')) active @endif">
                        <a href="{{ route('predefined.package.booking.list') }}" class="menu-link" title="Packages">
                            {{-- <i class="menu-icon tf-icons ri-gift-line"></i> --}}
                            <div data-i18n="Packages" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Packages</span>
                                <span class="tooltip-text">Packages</span>
                            </div>
                        </a>
                    </li>
                @endif
                
            </ul>
        </li>  
    @endif
    <!-- End Bookings --> 

        <!-- Booking List -->
        {{-- @if(hasPermission('view booking')) --}}
           {{-- <li class="menu-header mt-5">
                <span class="menu-header-text" data-i18n="Booking List">Booking List</span>
            </li>
            
            <li class="menu-item @if(Request::is('bookinglist*') || Request::is('enquiries')) open active @endif">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-bookmark-3-line"></i>
                    <div data-i18n="Booking List">Booking List</div>
                </a>
                <ul class="menu-sub">
                    <!-- Show Booking -->
                    <li class="menu-item @if(Request::is('bookinglist')) active @endif">
                        <a href="{{ route('bookinglist.index') }}" class="menu-link">
                            <div data-i18n="Booking List">Booking List</div>
                        </a>
                    </li>
                    <li class="menu-item @if(Request::is('enquiries')) active @endif">
                        <a href="{{ route('bookinglist.enquiry') }}" class="menu-link">
                            <div data-i18n="Enquiry List">Enquiry List</div>
                        </a>
                    </li>
                </ul>
            </li>  --}}
        {{-- @endif --}}
        <!-- End Booking List --> 

        {{-- Dmc = 11, Sales Head(dmc) = 33, Sales Manager(dmc) = [12, 37], Asst. Sales Manager(dmc) = 38 --}}
            {{-- @if(in_array(auth()->user()->role_id, [1, 2, 11, 33,  12, 37, 38, 128, 129, 130, 134, 135, 136, 138]))
            @if(hasPermission('view enquiry'))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Enquiries">Enquiries</span>
                </li>
                
                <li class="menu-item @if(Request::is('enquirylist*') && !Request::is('enquiries*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-customer-service-2-line" style="color: #3565bd"></i>
                        <div data-i18n="Enquiries">Enquiries</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item @if(Request::is('enquirylist') && !Request::is('enquiries*')) active @endif">
                            <a href="{{ route('enquirylist.index') }}" class="menu-link">
                                <div data-i18n="Enquiries">Enquiries</div>
                            </a>
                        </li>
                    </ul>
                </li>  
            @endif
            @endif --}}

            <!-- Enquiry -->
            <!-- @if(in_array(auth()->user()->role_id, [1,2,3,4,5,6,7,8,9,10,11,12,13, 14, 15, 16, 17,20,21,22,37, 49, 50, 51, 52, 53, 64, 65, 66, 67, 68, 90, 124, 125, 33, 37, 128, 129, 130, 134, 135, 136, 138]))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Negotiation">Negotiation</span>
                </li>

                <li class="menu-item @if(Request::is('enquiry') && !Request::is('enquiries*') && !Request::is('enquirylist*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-questionnaire-line"></i>
                        <div data-i18n="Negotiation">Negotiation</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item @if(Request::is('enquiry') && !Request::is('enquiries*') && !Request::is('enquirylist*')) active @endif">
                            <a href="{{ route('enquiry') }}" class="menu-link">
                                <div data-i18n="Negotiation List">Negotiation List</div>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif -->

            <!-- End Enquiry -->

            @if(in_array(auth()->user()->role_id, [1,2,3,4,10,11,19,20,44,45,46,47,48,25,59,60,61,62,63,83,101,110,119, 35,74,75,76,77,78,84,93,102,111,120,130, 132, 133, 135, 136, 137, 138,139,140]))

                <!-- Products Section -->
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Products">Products</span>
                </li>

            <li class="menu-item @if(Request::is('packages*') || Request::is('packaged-attractions*') || Request::is('hotels*') || Request::is('attraction*') || Request::is('restaurant*') || Request::is('guide*') || Request::is('vehicle*') || Request::is('driver*') || Request::is('category*') || Request::is('facility*') || Request::is('ports*') || Request::is('single-tour-package*') || Request::is('zones*') || Request::is('miscellaneous*') || Request::is('default-values*'))  open active @endif">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-stack-line" style="color: #3565bd"></i>
                    <div data-i18n="Products">Products</div>
                </a>
                <ul class="menu-sub">
                    <!-- Packages -->
            {{-- <li class="menu-item @if((Request::is('packages*') && !Request::is('packaged-attractions*')) || Request::is('predefined-package-booking-list') || Request::is('single-tour-package*')) open active @endif">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-gift-line"></i>
                    <div data-i18n="Packages">Packages</div>
                </a>
                <ul class="menu-sub"> --}}
                    <li class="menu-item @if(Request::is('packages') && !Request::is('packaged-attractions*')) active @endif">
                        <a href="{{ route('packages.index') }}" class="menu-link" title="Packages">
                            {{-- <i class="menu-icon tf-icons ri-gift-line"></i> --}}
                            <div data-i18n="Packages" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Packages</span>
                                <span class="tooltip-text">Packages</span>
                            </div>
                        </a>
                    </li>
                    {{-- <li class="menu-item @if(Request::is('packages/create')) active @endif">
                        <a href="{{ route('packages.create') }}" class="menu-link">
                            <div data-i18n="Create Package" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Create Predefined Package</span>
                                <span class="tooltip-text">Create Predefined Package</span>
                            </div>
                        </a>
                    </li> --}}
                <!-- Packaged Attractions -->
                {{-- <li class="menu-item @if(Request::is('packaged-attractions*') && !Request::is('packaged-attractions/packaged-attraction-approval*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-stack-line"></i>
                        <div data-i18n="Attraction Package">Attraction Package</div>
                    </a>
                    <ul class="menu-sub"> --}}
                        {{-- <li class="menu-item @if(Request::is('packaged-attractions')) active @endif">
                            <a href="{{ route('packaged-attractions.index') }}" class="menu-link" title="Packaged Attractions & Create Tab">
                                <i class="menu-icon tf-icons ri-stack-line"></i>
                                <div data-i18n="Packaged Attractions & Create Tab" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Packaged Attractions & Create Tab</span>
                                    <span class="tooltip-text">Packaged Attractions & Create Tab</span>
                                </div>
                            </a>
                        </li> --}}
                        {{-- <li class="menu-item @if(Request::is('packaged-attractions/create')) active @endif">
                            <a href="{{ route('packaged-attractions.create') }}" class="menu-link">
                                <div data-i18n="Create Attraction Package" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Create Packaged Attraction</span>
                                    <span class="tooltip-text">Create Packaged Attraction</span>
                                </div>
                            </a>
                        </li> --}}
                    {{-- </ul>
                </li> --}}

                <!-- Hotels & Accommodations -->
                {{-- @if(hasPermission('view hotel') || auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || hasPermission('create hotel'))
                <li class="menu-item @if(Request::is('hotels') || Request::is('hotels/create')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-hotel-line"></i>
                        <div data-i18n="Hotels & Accommodations">Hotels & Accommodations</div>
                    </a>
                    <ul class="menu-sub"> --}}
                        @if(hasPermission('view hotel') || auth()->user()->role_id == 1 || auth()->user()->role_id == 2)
                        <li class="menu-item @if(Request::is('hotels')) active @endif">
                            <a href="{{ route('hotels.index') }}" class="menu-link" title="Hotels & Accommodations">
                                {{-- <i class="menu-icon tf-icons ri-hotel-line"></i> --}}
                                <div data-i18n="Hotels & Accommodations" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Hotels & Accommodations</span>
                                    <span class="tooltip-text">Hotels & Accommodations</span>
                                </div>
                            </a>
                        </li>
                        @endif
                        {{-- @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 20)
                        @if(hasPermission('create hotel'))
                        <li class="menu-item @if(Request::is('hotels/create')) active @endif">
                            <a href=" {{ route('hotels.create') }}" class="menu-link">
                            <div data-i18n="Create Hotels">Create Hotels</div>
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </li>
                @endif --}}

                <!-- Attractions & Experiences -->
                @if(hasPermission('view attraction') || hasPermission('create attraction'))
                <li class="menu-item @if(Request::is('attraction*') && !Request::is('attractions/attraction-approval*') || Request::is('packaged-attractions')) open @endif">
                    <a href="#" class="menu-link menu-toggle">
                        {{-- <i class="menu-icon tf-icons ri-camera-3-line"></i> --}}
                        <div data-i18n="Attractions">Attractions</div>
                    </a>
                    <ul class="menu-sub">
                        @if(hasPermission('view attraction'))
                        <li class="menu-item @if(Request::is('attraction')) active @endif">
                            <a href="{{ route('attraction.index') }}" class="menu-link" title="Attractions & Experiences">
                                {{-- <i class="menu-icon tf-icons ri-camera-3-line"></i> --}}
                                <div data-i18n="Attractions & Experiences" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Attractions & Experiences</span>
                                    <span class="tooltip-text">Attractions & Experiences</span>
                                </div>
                            </a>
                        </li>
                        @endif
                        <li class="menu-item @if(Request::is('packaged-attractions')) active @endif">
                            <a href="{{ route('packaged-attractions.index') }}" class="menu-link" title="Bundle Attractions">
                                {{-- <i class="menu-icon tf-icons ri-stack-line"></i> --}}
                                <div data-i18n="Bundle Attractions" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Bundle Attractions</span>
                                    <span class="tooltip-text">Bundle Attractions</span>
                                </div>
                            </a>
                        </li>
                        {{-- @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 20)
                        @if(hasPermission('create attraction'))
                        <li class="menu-item @if(Request::is('attraction/create')) active @endif">
                            <a href="{{ route('attraction.create') }}" class="menu-link">
                                <div data-i18n="Create Attractions & Experiences" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Create Attractions & Experiences</span>
                                    <span class="tooltip-text">Create Attractions & Experiences</span>
                                </div>
                            </a>
                        </li>
                        @endif
                        @endif --}}
                    </ul>
                </li>
                @endif

                <!-- Restaurant -->
                @if(hasPermission('view restaurant') || hasPermission('create restaurant'))
                <li class="menu-item @if((Request::is('restaurant*') && !Request::is('restaurants/restaurant-approval*')) || Request::is('multiResturant*')) open @endif">
                    <a href="#" class="menu-link menu-toggle">
                        {{-- <i class="menu-icon tf-icons ri-restaurant-2-line"></i> --}}
                        <div data-i18n="Restaurant">Restaurant</div>
                    </a>
                    <ul class="menu-sub">
                        @if(hasPermission('view restaurant'))
                        <li class="menu-item @if(Request::is('restaurant')) active @endif">
                            <a href="{{ route('restaurant.index') }}" class="menu-link" title="Restaurant & Dining" >
                                {{-- <i class="menu-icon tf-icons ri-restaurant-2-line"></i> --}}
                                <div data-i18n="Restaurant & Dining" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Restaurant & Dining</span>
                                    <span class="tooltip-text">Restaurant & Dining</span>
                                </div>
                            </a>
                        </li>
                        @endif
                        @if(auth()->check() && in_array(auth()->user()->role_id, [1, 11, 20]))
                        <li class="menu-item @if(Request::is('multiRestaurant*')) active @endif">
                            <a href="{{ route('multiResturant.index') }}" class="menu-link" title="Multi Restaurants">
                                {{-- <i class="menu-icon tf-icons ri-restaurant-2-line"></i> --}}
                                <div data-i18n="Multi Restaurants" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Multi Restaurants</span>
                                    <span class="tooltip-text">Multi Restaurants</span>
                                </div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                <!-- Tour Guides -->
                {{-- @if(hasPermission('view guide') || hasPermission('create guide'))
                <li class="menu-item @if(Request::is('guide*') && !Request::is('guide/guide-approval*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-compass-3-line"></i>
                        <div data-i18n="Guide">Tour Guides</div>
                    </a>
                    <ul class="menu-sub"> --}}
                        @if(hasPermission('view guide'))
                        <li class="menu-item @if(Request::is('guide')) active @endif">
                            <a href="{{ route('guide.index') }}" class="menu-link" title="Tour Guides">
                                {{-- <i class="menu-icon tf-icons ri-compass-3-line"></i> --}}
                                <div data-i18n="Tour Guides" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Tour Guides</span>
                                    <span class="tooltip-text">Tour Guides</span>
                                </div>
                            </a>
                        </li>
                        @endif
                        {{-- @if(hasPermission('create guide'))
                        <li class="menu-item @if(Request::is('guide/create')) active @endif">
                            <a href="{{ route('guide.create') }}" class="menu-link">
                                <div data-i18n="Create Tour Guide">Create Tour Guide</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif --}}

                <!-- Vehicles -->
                {{-- @if(hasPermission('view vehicle') || hasPermission('create vehicle'))
                <li class="menu-item @if(Request::is('vehicle*')) open active @endif">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-car-line"></i>
                        <div data-i18n="Vehicles">Vehicles</div>
                    </a>
                    <ul class="menu-sub"> --}}
                        @if(hasPermission('view vehicle'))
                        <li class="menu-item @if(Request::is('vehicle')) active @endif">
                            <a href="{{ route('vehicle.index') }}" class="menu-link" title="Vehicles">
                                {{-- <i class="menu-icon tf-icons ri-car-line"></i> --}}
                                <div data-i18n="Vehicles" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Vehicles</span>
                                    <span class="tooltip-text">Vehicles</span>
                                </div>
                            </a>
                        </li>
                        @endif
                        {{-- @if(hasPermission('create vehicle'))
                        <li class="menu-item @if(Request::is('vehicle/create')) active @endif">
                            <a href="{{ route('vehicle.create') }}" class="menu-link">
                                <div data-i18n="Create Vehicles">Create Vehicle</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif --}}

                <!-- Drivers -->
                {{-- @if(hasPermission('view driver') || hasPermission('create driver'))
                <li class="menu-item @if(Request::is('driver*') && !Request::is('driver/driver-approval*')) open active @endif">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-steering-2-line"></i>
                        <div data-i18n="Driver">Drivers</div>
                    </a>
                    <ul class="menu-sub"> --}}
                        @if(hasPermission('view driver'))
                        <li class="menu-item @if(Request::is('driver')) active @endif">
                                <a href="{{ route('driver.index') }}" class="menu-link" title="Drivers">
                                {{-- <i class="menu-icon tf-icons ri-steering-2-line"></i> --}} 
                                <div data-i18n="Drivers" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Drivers</span>
                                    <span class="tooltip-text">Drivers</span>
                                </div>
                            </a>
                        </li>
                        @endif
                        {{-- @if(hasPermission('create driver'))
                        <li class="menu-item @if(Request::is('driver/create')) active @endif">
                            <a href="{{ route('driver.create') }}" class="menu-link">
                                <div data-i18n="Create Driver">Create Driver</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif --}}

                <!-- Product Configuration -->
            
                @if(hasPermission('view facility') || hasPermission('view category') || Auth::user()->role_id == 11 || Auth::user()->role_id == 35 || Auth::user()->role_id == 76 || Auth::user()->role_id == 111 || Auth::user()->role_id == 139 || Auth::user()->role_id == 140 || Auth::user()->role_id == 130 || Auth::user()->role_id == 132 || Auth::user()->role_id == 133 || Auth::user()->role_id == 135 || Auth::user()->role_id == 136 || Auth::user()->role_id == 137 || Auth::user()->role_id == 138)
                <li class="menu-item @if(Request::is('category*') || Request::is('facility*') || Request::is('zones*') || Request::is('default-values*') || Request::is('ports*') || Request::is('miscellaneous*')) open @endif">
                    <a href="#" class="menu-link menu-toggle" title="Product Configuration">
                        {{-- <i class="menu-icon tf-icons ri-function-line"></i> --}}
                        <div data-i18n="Product Configuration">Product Configuration</div>
                    </a>
                    <ul class="menu-sub">
                        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                        @if(hasPermission('view category'))
                        <li class="menu-item @if(Request::is('category')) active @endif">
                            <a href="{{ route('category.index') }}" class="menu-link" title="Facility Categories">
                                <div data-i18n="Facility Categories" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Facility Categories</span>
                                    <span class="tooltip-text">Facility Categories</span>
                                </div>
                            </a>
                        </li>
                        @endif
                        @if(hasPermission('view facility'))
                        <li class="menu-item @if(Request::is('facility')) active @endif">
                            <a href="{{ route('facility.index') }}" class="menu-link" title="Facilities">
                                <div data-i18n="Facilities" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Facilities</span>
                                    <span class="tooltip-text">Facilities</span>
                                </div>
                            </a>
                        </li>
                        @endif
                        
                        <!-- Miscellaneous Items - Admin Only -->
                        <li class="menu-item @if(Request::is('miscellaneous') || Request::is('miscellaneous/*')) active @endif">
                            <a href="{{ route('miscellaneous.index') }}" class="menu-link" title="Miscellaneous Items">
                                <div data-i18n="Miscellaneous Items" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Miscellaneous Items</span>
                                    <span class="tooltip-text">Miscellaneous Items</span>
                                </div>
                            </a>
                        </li>
                        @endif
                        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
                        <!-- Show Port -->
                        <li class="menu-item @if(Request::is('ports')) active @endif">
                            <a href="{{ route('ports.index') }}" class="menu-link">
                                {{-- <i class="menu-icon tf-icons ri-ship-line"></i> --}}
                                <div data-i18n="Ports" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Ports</span>
                                    <span class="tooltip-text">Ports</span>
                                </div>
                            </a>
                        </li>
                        @endif

                    <!-- Zones (hard-coded link under Product Configuration) -->
                    @if(Auth::user()->role_id == 11 || Auth::user()->role_id == 35 || Auth::user()->role_id == 76 || Auth::user()->role_id == 111 || Auth::user()->role_id == 139 || Auth::user()->role_id == 140 || Auth::user()->role_id == 130 || Auth::user()->role_id == 132 || Auth::user()->role_id == 133 || Auth::user()->role_id == 135 || Auth::user()->role_id == 136 || Auth::user()->role_id == 137 || Auth::user()->role_id == 138)
                    <li class="menu-item @if(Request::is('zones')) active @endif">
                        <a href="{{ route('zones.index') }}" class="menu-link" title="Zones">
                                <div data-i18n="Zones" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Zones</span>
                                    <span class="tooltip-text">Zones</span>
                                </div>
                            </a>
                        </li>

                    <!-- Default Value (same permissions as Zones) -->
                    <li class="menu-item @if(Request::is('default-values') || Request::is('default-values/*')) active @endif">
                        <a href="{{ route('default-values.index') }}" class="menu-link" title="Default Value">
                                <div data-i18n="Default Value" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Default Value</span>
                                    <span class="tooltip-text">Default Value</span>
                                </div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                <!-- Ports -->
                {{-- @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
                <li class="menu-item @if(Request::is('ports*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-ship-line"></i>
                        <div data-i18n="Ports">Ports</div>
                    </a>
                    <ul class="menu-sub"> --}}
                        
                        {{-- <!-- Add Port -->
                        <li class="menu-item @if(Request::is('ports/create')) active @endif">
                            <a href="{{ route('ports.create') }}" class="menu-link">
                                <div data-i18n="Create Port">Create Port</div>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif --}}
                    </ul>
                </li>

                <!-- Predefined Packages Booking List -->
                {{-- @if(in_array(auth()->user()->role_id, [1,2,11, 33, 128, 129, 130, 134, 135, 136, 138, 34, 36, 37, 38]))
                    <li class="menu-header mt-5">
                        <span class="menu-header-text" data-i18n="Predefined Packages Booking List">Predefined Packages Booking List</span>
                    </li>
                    
                    <li class="menu-item @if(Request::is('predefined-package-booking-list*')) open active @endif">
                        <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-suitcase-line" style="color: #1E90FF;"></i>

                            <div data-i18n="Predefined Packages Booking List">Predefined Packages Booking List</div>
                        </a>
                        <ul class="menu-sub">
                            <!-- Show Booking -->
                            <li class="menu-item @if(Request::is('predefined-package-booking-list')) active @endif">
                                <a href="{{ route('predefined.package.booking.list') }}" class="menu-link">
                                    <div data-i18n="Predefined Packages Booking List">Predefined Packages Booking List</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif --}}
                <!-- End Predefined Packages Booking List -->
            @endif
            @if(in_array(auth()->user()->role_id, [1,2,3,4,10,11,19,20,33,37,38,128, 129, 130, 134, 135, 136, 138]))
              <!-- Reports -->
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="View Reports">View Reports</span>
                </li>
                <li class="menu-item @if(Request::is('reports/sales-revenue*') || Request::is('reports/ledger') || Request::is('reports/balance-sheet*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-bar-chart-box-line"></i>
                        <div data-i18n="Reports">Reports</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item @if(Request::is('reports/sales-revenue')) active @endif">
                            <a href="{{ route('reports.sales-revenue') }}" class="menu-link">
                                
                                <div data-i18n="Sales & Revenue">Sales & Revenue</div>
                            </a>
                        </li>
                        <li class="menu-item @if(Request::is('reports/ledger')) active @endif">
                            <a href="{{ route('reports.ledger') }}" class="menu-link">
                                <div data-i18n="Ledger">Ledger</div>
                            </a>
                        </li>
                        {{-- <li class="menu-item @if(Request::is('reports/balance-sheet')) active @endif">
                            <a href="{{ route('reports.balance-sheet') }}" class="menu-link">
                                <div data-i18n="Balance Sheet & P&L">Balance Sheet & P&L</div>
                            </a>
                        </li> --}}
                    </ul>
                </li>
                <!-- End Reports -->
            @endif
            
            <!-- Bulk Upload -->
            {{-- @if(in_array(auth()->user()->role_id, [11, 20]))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Bulk Upload">Bulk Upload</span>
                </li>
                
                <li class="menu-item @if(Request::is('bulk-upload*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-upload-cloud-2-line" style="color: #10b981 !important; background: rgba(16, 185, 129, 0.1);"></i>
                        <div data-i18n="Bulk Upload">Bulk Upload</div>
                    </a>
                    <ul class="menu-sub"> --}}
                        {{-- <li class="menu-item @if(Request::is('bulk-upload/hotels')) active @endif">
                            <a href="{{ route('bulk-upload.hotels') }}" class="menu-link">
                                <div data-i18n="Hotels">Hotels</div>
                            </a>
                        </li> --}}
                        {{-- <li class="menu-item @if(Request::is('bulk-upload/guides')) active @endif">
                            <a href="{{ route('bulk-upload.guides') }}" class="menu-link">
                                <div data-i18n="Guides">Guides</div>
                            </a>
                        </li>
                        <li class="menu-item @if(Request::is('bulk-upload/drivers')) active @endif">
                            <a href="{{ route('bulk-upload.drivers') }}" class="menu-link">
                                <div data-i18n="Drivers">Drivers</div>
                            </a>
                        </li>
                        <li class="menu-item @if(Request::is('bulk-upload/vehicles')) active @endif">
                            <a href="{{ route('bulk-upload.vehicles') }}" class="menu-link">
                                <div data-i18n="Vehicles">Vehicles</div>
                            </a>
                        </li>
                        @if(auth()->user()->role_id !== '11')
                        <li class="menu-item @if(Request::is('bulk-upload/restaurants')) active @endif">
                            <a href="{{ route('bulk-upload.restaurants') }}" class="menu-link">
                                <div data-i18n="Restaurants">Restaurants</div>
                            </a>
                        </li>
                        <li class="menu-item @if(Request::is('bulk-upload/attractions')) active @endif">
                            <a href="{{ route('bulk-upload.attractions') }}" class="menu-link">
                                <div data-i18n="Attractions">Attractions</div>
                            </a>
                        </li>
                        @endif --}}
                        {{-- @if(auth()->user()->role_id == '11')
                        <li class="menu-item @if(Request::is('bulk-upload/tickets')) active @endif">
                            <a href="{{ route('bulk-upload.tickets') }}" class="menu-link">
                                <div data-i18n="Attraction Tickets">Attraction Tickets</div>
                            </a>
                        </li>
                        <li class="menu-item @if(Request::is('bulk-upload/meals')) active @endif">
                            <a href="{{ route('bulk-upload.meals') }}" class="menu-link">
                                <div data-i18n="Restaurant Meals">Restaurant Meals</div>
                            </a>
                        </li>
                        @endif --}}
                    {{-- </ul>
                </li>
            @endif --}}
            <!-- End Bulk Upload -->

                <!-- Jobsheets -->
                @if(in_array(Auth::user()->role_id, [1, 2, 7, 11, 26,49,50,51,52,53,80,89,98,107,116,34,64,65,66,67,68,81,90,99,108,117,124,125,128,131,132,134,135,137,138]))
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text">Service Delivery</span>
                    </li>
                    <li class="menu-item @if(Request::is('jobsheet/view') || Request::is('jobsheet/create-guide-jobsheet') || Request::is('jobsheet/create-driver-jobsheet') || Request::is('jobsheet/drivers') || Request::is('jobsheet/guides')) open @endif">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-file-list-3-line"></i>

                        <div data-i18n="Service Delivery">Service Delivery</div>
                        </a>
                        
                        <ul class="menu-sub">
                            @if(in_array(auth()->user()->role_id, [34, 128, 131, 132, 134, 135, 137, 138]))
                                <li class="menu-item @if(Request::is('bookings/today')) active @endif">
                                    <a href="{{ route('bookings.today') }}" class="menu-link" title="Trip Logs">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span data-i18n="Trip Logs">Trip Logs</span>
                                        </div>
                                    </a>
                                </li>
                            @endif
                            <li class="menu-item @if(Request::is('jobsheet/create-guide-jobsheet') || Request::is('jobsheet/create-driver-jobsheet') || Request::is('jobsheet/drivers') || Request::is('jobsheet/guides')) open @endif">
                                <a href="javascript:void(0);" class="menu-link menu-toggle">
                                    <div data-i18n="Jobs">Jobs</div>
                                </a>
                                <ul class="menu-sub">
                                    <li class="menu-item @if(Request::is('jobsheet/drivers') || Request::is('jobsheet/guides')) open @endif">
                                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                                            <div data-i18n="Assign Jobs">Assign Jobs</div>
                                        </a>
                                        <ul class="menu-sub">
                                            @if(in_array(Auth::user()->role_id, [1, 2,7,14,97,8,15,106, 10, 11, 26, 51,107, 34, 66, 108, 128, 131, 132, 134, 135, 137, 138]))
                                            <li class="menu-item @if(Request::is('jobsheet/drivers')) active @endif">
                                                <a href="{{ route('jobsheet.drivers') }}" class="menu-link">
                                                    <div data-i18n="Drivers">Drivers</div>
                                                </a>
                                            </li>
                                            @endif

                                            @if(in_array(Auth::user()->role_id, [1, 2,7,14,97, 10, 11, 26, 50, 98, 34, 65, 99, 128, 131, 132, 134, 135, 137, 138]))
                                            <li class="menu-item @if(Request::is('jobsheet/guides')) active @endif">
                                                <a href="{{ route('jobsheet.guides') }}" class="menu-link">
                                                    <div data-i18n="Guides">Guides</div>
                                                </a>
                                            </li>
                                            @endif
                                        </ul>
                                    </li>

                                    <li class="menu-item @if(Request::is('jobsheet/create-driver-jobsheet') || Request::is('jobsheet/create-guide-jobsheet')) open @endif">
                                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                                            <div data-i18n="Job Sheets">Job Sheets</div>
                                        </a>
                                        <ul class="menu-sub">
                                            <li class="menu-item @if(Request::is('jobsheet/create-driver-jobsheet')) active @endif">
                                                <a href="{{ route('jobsheet.create.driver') }}" class="menu-link">
                                                    <div data-i18n="Driver Jobsheet">Driver Jobsheet</div>
                                                </a>
                                            </li>
                                            <li class="menu-item @if(Request::is('jobsheet/create-guide-jobsheet')) active @endif">
                                                <a href="{{ route('jobsheet.create.guide') }}" class="menu-link">
                                                    <div data-i18n="Guide Jobsheet">Guide Jobsheet</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- End Jobsheets -->

                <!-- JobSheet -->
                {{-- @if(in_array(Auth::user()->role_id, [1 ,7,14,97,8,15,106, 10, 11, 26, 50, 98,51,107, 34,65, 99, 66, 108, 128, 131, 132, 134, 135, 137, 138]))
                    <li class="menu-header mt-5">
                        <span class="menu-header-text" data-i18n="Assigned Job">Assigned Job</span>
                    </li>

                    <li class="menu-item @if(Request::is('jobsheet/drivers') || Request::is('jobsheet/guides')) active open @endif">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons ri-task-line"></i>
                            <div data-i18n="Assigned Job">Assigned Job</div>
                        </a>
                        <ul class="menu-sub">
                            @if(in_array(Auth::user()->role_id, [1, 2,7,14,97,8,15,106, 10, 11, 26, 51,107, 34, 66, 108, 128, 131, 132, 134, 135, 137, 138]))
                            <!-- Driver Jobs -->
                            <li class="menu-item @if(Request::is('jobsheet/drivers')) active @endif">
                                <a href="{{ route('jobsheet.drivers') }}" class="menu-link">
                                    <div data-i18n="Driver Jobs">Driver Jobs</div>
                                </a>
                            </li>
                            @endif

                            @if(in_array(Auth::user()->role_id, [1, 2,7,14,97, 10, 11, 26, 50, 98, 34, 65, 99, 128, 131, 132, 134, 135, 137, 138]))
                            <!-- Guide Jobs -->
                            <li class="menu-item @if(Request::is('jobsheet/guides')) active @endif">
                                <a href="{{ route('jobsheet.guides') }}" class="menu-link">
                                    <div data-i18n="Guide Jobs">Guide Jobs</div>
                                </a>
                            </li>

                            @endif
                        </ul>
                    </li>
                @endif --}}
                <!-- End JobSheet -->
                


            {{-- @if(Auth::user()->role_id == 11)
            <li class="menu-header mt-5">
                <span class="menu-header-text" data-i18n="Zone">Zone</span>
            </li>

            <li class="menu-item @if(Request::is('zones*')) open active @endif">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-map-pin-user-line"></i>
                    <div data-i18n="Zone">Zone</div>
                </a>
                <ul class="menu-sub">
                    <!-- Show Zone -->
                    <li class="menu-item @if(Request::is('zones') && !Request::is('zones/create')) active @endif">
                        <a href="{{ route('zones.index') }}" class="menu-link">
                            <div data-i18n="Zone List">Zone List</div>
                        </a>
                    </li>
                    <!-- Add Zone -->
                    <li class="menu-item @if(Request::is('zones/create')) active @endif">
                        <a href="{{ route('zones.create') }}" class="menu-link">
                            <div data-i18n="Add Zone">Add Zone</div>
                        </a>
                    </li>
                </ul>
            </li>
            @endif --}}
            <!-- End Zone -->

            <!-- Services Management for DMC -->
            @php
                $allowedRoles = [11, 33, 37, 38, 128, 129, 130, 134, 135, 136, 138, 35, 74, 77, 78, 84, 93, 120, 132, 133, 139, 140];
            @endphp

            @if(in_array(Auth::user()->role_id, $allowedRoles))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Services Management">Services Management</span>
                </li>

                <li class="menu-item @if(Request::is('services/*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-service-line"></i>
                        <div data-i18n="Services">Services</div>
                    </a>
                    <ul class="menu-sub">
                        <!-- DMC Hotels Selection -->
                        @php
                            $allowedRoles = [11, 35, 77, 84, 130, 132, 133, 135, 136, 137, 138, 139, 140];
                        @endphp
                        @if(in_array(Auth::user()->role_id, $allowedRoles))
                            <li class="menu-item @if(Request::is('services/hotels')) active @endif">
                                <a href="{{ route('services.hotels') }}" class="menu-link">
                                    <div data-i18n="Select Hotels">Select Hotels</div>
                                </a>
                            </li>
                        @endif 
                        <!-- DMC Attractions Selection -->
                        @php
                            $allowedRoles = [11, 35,74, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140];
                        @endphp
                        @if(in_array(Auth::user()->role_id, $allowedRoles))
                        <li class="menu-item @if(Request::is('services/attractions')) active @endif">
                            <a href="{{ route('services.attractions') }}" class="menu-link">
                                <div data-i18n="Select Attractions">Select Attractions</div>
                            </a>
                        </li>
                        @endif
                        
                        <!-- DMC Restaurants Selection -->
                        @php
                            $allowedRoles = [11, 35, 78, 120, 130, 132, 133, 135, 136, 137, 138, 139, 140];
                        @endphp
                        @if(in_array(Auth::user()->role_id, $allowedRoles))
                        <li class="menu-item @if(Request::is('services/restaurants')) active @endif">
                            <a href="{{ route('services.restaurants') }}" class="menu-link">
                                <div data-i18n="Select Restaurants">Select Restaurants</div>
                            </a>
                        </li>
                        @endif
                        <!-- DMC Agencies Selection -->
                        @php
                            $allowedRoles = [11, 33, 37, 38, 128, 129, 130, 134, 135, 136, 138];
                        @endphp
                        @if(in_array(Auth::user()->role_id, $allowedRoles))
                        <li class="menu-item @if(Request::is('services/agencies')) active @endif">
                            <a href="{{ route('services.agencies') }}" class="menu-link">
                                <div data-i18n="Select Agencies">Select Agencies</div>
                            </a>
                        </li>
                        @endif
                        
                        <!-- DMC Miscellaneous Selection -->
                        @php
                            $allowedRoles = [11, 35, 77, 78, 84, 120, 130, 132, 133, 135, 136, 137, 138, 139, 140];
                        @endphp
                        @if(in_array(Auth::user()->role_id, $allowedRoles))
                        <li class="menu-item @if(Request::is('services/miscellaneous')) active @endif">
                            <a href="{{ route('services.miscellaneous') }}" class="menu-link">
                                <div data-i18n="Select Miscellaneous">Select Miscellaneous</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
            @endif
            <!-- End Services Management -->

            <!-- Booking -->
            {{-- @if(auth()->user()->role_id == 21||auth()->user()->role_id == 26 || auth()->user()->role_id == 34 || auth()->user()->role_id == 124 
            || auth()->user()->role_id == 125 || auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || auth()->user()->role_id == 3 ||
            auth()->user()->role_id == 4|| auth()->user()->role_id == 12|| auth()->user()->role_id == 28|| auth()->user()->role_id == 33 || 
            auth()->user()->role_id == 128 || auth()->user()->role_id == 129 || auth()->user()->role_id == 130 || auth()->user()->role_id == 134 ||
             auth()->user()->role_id == 135 || auth()->user()->role_id == 136 || auth()->user()->role_id == 138 || auth()->user()->role_id == 37)
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Hotel Booking">Hotel Booking</span>
                </li>
                <li class="menu-item @if(Request::is('booking*') && !Request::is('bookinglist*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-hotel-bed-line"></i>
                        <div data-i18n="Hotel Booking">Hotel Booking</div>
                    </a>
                    <ul class="menu-sub">
                        <!-- Show Booking -->
                        <li class="menu-item @if(Request::is('booking')) active @endif">
                            <a href="{{ route('booking.index') }}" class="menu-link">
                                <div data-i18n="Approve Hotel Booking List">Approve Hotel Booking List</div>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif  --}}

            {{-- @if(in_array(Auth::user()->role_id, [1, 2]))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Manage Approvals">Manage Approvals</span>
                </li>
                <li class="menu-item @if(Request::is('hotels/hotel-approval*', 'attractions/attraction-approval*', 'restaurants/restaurant-approval*', 'guide/guide-approval*', 'driver/driver-approval*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-shield-check-line"></i> 
                        <div data-i18n="Manage Approvals">Manage Approvals</div>
                    </a>                                       
                    <ul class="menu-sub">
                        <!-- Show Hotel Approval -->
                        @if(in_array(Auth::user()->role_id, [1, 2]))
                            <li class="menu-item @if(Request::is('hotels/hotel-approval')) active @endif">
                                <a href="{{ route('hotels.approval') }}" class="menu-link">
                                    <div data-i18n="Hotel Approval">Hotel Approval</div>
                                </a>
                            </li>
                        @endif

                        <!-- Show Attraction Approval -->
                        @if(in_array(Auth::user()->role_id, [1, 2]))
                            <li class="menu-item @if(Request::is('attractions/attraction-approval')) active @endif">
                                <a href="{{ route('attractions.approval') }}" class="menu-link">
                                    <div data-i18n="Attraction Approval">Attraction Approval</div>
                                </a>
                            </li>
                        @endif

                        <!-- Show Restaurant Approval -->
                        @if(in_array(Auth::user()->role_id, [1, 2]))
                            <li class="menu-item @if(Request::is('restaurants/restaurant-approval')) active @endif">
                                <a href="{{ route('restaurants.approval') }}" class="menu-link">
                                    <div data-i18n="Restaurant Approval">Restaurant Approval</div>
                                </a>
                            </li>
                        @endif

                        <!-- Show Guide Approval -->
                        @if(in_array(Auth::user()->role_id, [1, 2]))
                            <li class="menu-item @if(Request::is('guide/guide-approval')) active @endif">
                                <a href="{{ route('guide.approval') }}" class="menu-link">
                                    <div data-i18n="Guide Approval">Guide Approval</div>
                                </a>
                            </li>
                        @endif

                        <!-- Show Driver Approval -->
                        @if(in_array(Auth::user()->role_id, [1, 2]))
                        <li class="menu-item @if(Request::is('driver/driver-approval')) active @endif">
                            <a href="{{ route('driver.approval') }}" class="menu-link">
                                <div data-i18n="Driver Approval">Driver Approval</div>
                            </a>
                        </li>
                    @endif
                    </ul>
                </li>
            @endif --}}

            @php
                $allowedRoles = [1, 2, 3, 4, 11, 19, 20, 33, 37, 38, 128, 129, 130, 134, 135, 136, 138];
            @endphp

            @if(in_array(Auth::user()->role_id, $allowedRoles))
                <!-- Agency Management -->
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Agency Management">Agency Management</span>
                </li>

                <li class="menu-item @if(Request::is('agency*')) open active @endif">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-building-line"></i>
                        <div data-i18n="Agency Management">Agency Management</div>
                    </a>
                    <ul class="menu-sub">
                        <!-- List Agencies -->
                        <li class="menu-item @if(Request::is('agencies')) active @endif">
                            <a href="{{ route('agencies.index') }}" class="menu-link">
                                <div data-i18n="Agencies">Agencies</div>
                            </a>
                        </li>

                        <!-- Create Agency -->
                        <!-- <li class="menu-item @if(Request::is('agencies/create')) active @endif">
                            <a href="{{ route('agencies.create') }}" class="menu-link">
                                <div data-i18n="Create Agency">Create Agency</div>
                            </a>
                        </li> -->
                    </ul>
                </li>
                @endif

                <!-- Tax Management for DMC -->
                @php
                    $dmcTaxRoles = 11;
                @endphp

                @if(Auth::user()->role_id == $dmcTaxRoles)
                    <li class="menu-header mt-5">
                        <span class="menu-header-text" data-i18n="Tax Management">Tax Management</span>
                    </li>

                    <li class="menu-item @if(Request::is('tax*')) open active @endif">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons ri-percent-line"></i>
                            <div data-i18n="Tax Management">Tax Management</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item @if(Request::is('tax') || Request::is('tax/settings')) open active @endif">
                                <a href="javascript:void(0);" class="menu-link menu-toggle">
                                    <div data-i18n="Settings">Settings</div>
                                </a>
                                <ul class="menu-sub">
                                    <!-- Add Tax -->
                                    <li class="menu-item @if(Request::is('tax')) active @endif">
                                        <a href="{{ route('tax.index') }}" class="menu-link">
                                            <div data-i18n="Add Tax">Add Tax</div>
                                        </a>
                                    </li>

                                    <!-- Tax Settings -->
                                    <li class="menu-item @if(Request::is('tax/settings')) active @endif">
                                        <a href="{{ route('tax.settings') }}" class="menu-link">
                                            <div data-i18n="Tax Settings">Tax Settings</div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                @endif
                
                <!-- Operation Country -->
                {{-- @if(hasPermission('view country'))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="City Listing">City Listing</span>
                </li>
                <li class="menu-item @if(Request::is('country')) active @endif">
                    @if(hasPermission('view country'))
                    <a href="{{ route('country.index') }}" class="menu-link">
                        <i class="menu-icon ri-earth-line"></i>
                        <div data-i18n="List City">List City</div>
                    @endif
                    </a>
                </li>
                @endif --}}

                <!-- <li class="menu-header mt-4">
                    <span class="menu-header-text text-uppercase font-weight-bold" data-i18n="Report">Report</span>
                </li>
                
                <li class="menu-item @if(Request::is('report*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-bar-chart-box-line"></i>
                        <div data-i18n="Report">Report</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item @if(Request::is('report')) active @endif">
                            <a href="{{ route('report.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons ri-file-list-3-line"></i>
                                <div data-i18n="Sales Report">Sales Report</div>
                            </a>
                        </li>
                    </ul>
                </li>-->


                
                <!-- User Role Management -->
                @if( !( (auth()->user()->role_id >= 79 && auth()->user()->role_id <= 123) || in_array(auth()->user()->role_id, [125, 127, 140]) ) )
                @if(hasPermission('view users') || hasPermission('view roles') || hasPermission('view features') || hasPermission('view agent') || $auth_user->role_id == 124)
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="User Management">User Management</span>
                </li>
                <li class="menu-item @if(Request::is('users*', 'agents*', 'roles*', 'features*')) open @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-user-line"></i>
                        <div data-i18n="All Users">All Users</div>
                    </a>
                    <ul class="menu-sub">
                        @php
                            $excludedRoles = [81, 38, 84, 87, 90, 93, 96, 99, 102, 105, 108, 111, 114, 117, 120, 123, 125, 127];
                        @endphp

                        @if(hasPermission('view users') && !in_array(auth()->user()->role_id, $excludedRoles))
                            <li class="menu-item @if(Request::is('users')) active @endif">
                                <a href="{{ route('users.index') }}" class="menu-link">
                                    <div data-i18n="Users">Users</div>
                                </a>
                            </li>
                        @endif
                       

                        @if(hasPermission('view agent'))
                        <li class="menu-item @if(Request::is('agents')) active @endif">
                            <a href="{{ route('agents.index') }}" class="menu-link">
                                <div data-i18n="Travel Agents">Travel Agents</div>
                            </a>
                        </li>
                        @endif

                        <!-- Registered Agents View -->
                         {{-- @if(auth()->user()->role_id == 20 || auth()->user()->role_id == 19 || auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || auth()->user()->role_id == 3 || auth()->user()->role_id == 4)
                            <li class="menu-item @if(Request::is('registered-agents*')) active @endif">
                                <a href="{{ route('registered-agents.index') }}" class="menu-link">
                                    <div data-i18n="Registered Agents">Registered Agents</div>
                                </a>
                            </li>
                        @endif --}}
                        
                        <!-- Bank Details -->
                        @php
                            $allowedBankDetailsRoles = [1, 11];
                        @endphp
                        @if(in_array(auth()->user()->role_id, $allowedBankDetailsRoles))
                            <li class="menu-item @if(Request::is('bank-details*')) active @endif">
                                <a href="{{ route('bank-details.index') }}" class="menu-link">
                                    <div data-i18n="Bank Details">Bank Details</div>
                                </a>
                            </li>
                        @endif
                        
                        @if(hasPermission('view roles') && auth()->user()->user_type == 1)
                        <li class="menu-item @if(Request::is('roles')) active @endif">
                            <a href="{{ route('roles.index') }}" class="menu-link">
                                <div data-i18n="Roles">Roles</div>
                            </a>
                        </li>
                        @endif
                        {{-- <li class="menu-item @if(Request::is('countries')) active @endif">
                            <a href="{{ route('countries.index') }}" class="menu-link">
                                <div data-i18n="Countries">Countries</div>
                            </a>
                        </li> --}}
                        @if(hasPermission('view features') && auth()->user()->user_type == 1)
                        <li class="menu-item @if(Request::is('features')) active @endif">
                            <a href="{{ route('features') }}" class="menu-link">
                                <div data-i18n="Features">Features</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif
                @endif
                <!-- End User Role Management -->   

                {{-- <!-- Settings -->
                @if(hasPermission('settings') || hasPermission('edit settings'))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Settings">Settings</span>
                </li>
                @endif
                <li class="menu-item @if(Request::is('master-setting')) active @endif">
                    <a href="{{ route('master-setting') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-settings-3-line"></i>
                        <div data-i18n="Master Settings">Master Settings</div>
                    </a>
                </li>
                @endif --}}

                    <!-- Settings -->
                    @php
                        $sidebarRoleId = Auth::user()->role_id;
                        $sidebarIsAdmin = in_array($sidebarRoleId, [1]);
                        $sidebarIsDmc = in_array($sidebarRoleId, [11]);
                        $sidebarIsOperational = in_array($sidebarRoleId, [34, 124,125]);
                        $sidebarIsFinance = in_array($sidebarRoleId, [36, 126,127]);
                        $sidebarIsLimitedGeneralSettings = $sidebarIsDmc || $sidebarIsOperational || $sidebarIsFinance;
                    @endphp
                    @if(
                        ($sidebarIsAdmin && (hasPermission('settings') || hasPermission('edit settings') || hasPermission('view country')))
                        || ($sidebarIsLimitedGeneralSettings)
                    )
                    <li class="menu-header mt-5">
                        <span class="menu-header-text" data-i18n="Setting">Setting</span>
                    </li>
                    
                    <li class="menu-item @if(Request::is('master-setting*', 'country*', 'countries*', 'mail/settings*')) open @endif">
                        <a href="#" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons ri-settings-3-line"></i>
                            <div data-i18n="General Settings">General Settings</div>
                        </a>

                        <ul class="menu-sub">
                            @if($sidebarIsAdmin)
                            @if(hasPermission('settings') && hasPermission('edit settings'))
                            <li class="menu-item @if(Request::is('master-setting')) active @endif">
                                <a href="{{ route('master-setting') }}" class="menu-link">
                                    <div data-i18n="Master Setting">Master Setting</div>
                                </a>
                            </li>
                            @endif

                            <!-- List City -->
                            @if(hasPermission('view country'))
                            <li class="menu-item @if(Request::is('country')) active @endif">
                                <a href="{{ route('country.index') }}" class="menu-link">
                                    <div data-i18n="City Charges">City Charges</div>
                                </a>
                            </li>
                            @endif

                            <!-- Cities -->
                            <li class="menu-item @if(Request::is('cities*')) active @endif">
                                <a href="{{ route('cities.index') }}" class="menu-link">
                                    <div data-i18n="Cities">Cities</div>
                                </a>
                            </li>
                            @endif

                        <!-- Countries -->
                        @if(hasPermission('view country') || $sidebarIsLimitedGeneralSettings)
                            <li class="menu-item @if(Request::is('countries')) active @endif">
                                <a href="{{ route('countries.index') }}" class="menu-link">
                                    <div data-i18n="Countries">Countries</div>
                                </a>
                            </li>
                        @endif

                        @if($sidebarIsAdmin)
                            <!-- Email Settings -->
                            <li class="menu-item @if(Request::is('mail/settings')) active @endif">
                                <a href="{{ route('mail.settings') }}" class="menu-link">
                                    <div data-i18n="Email Settings">Email Settings</div>
                                </a>
                            </li>

                            <!-- App Settings -->
                            <li class="menu-item @if(Request::is('app-management')) active @endif">
                                <a href="{{ route('app-management.index') }}" class="menu-link">
                                    <div data-i18n="App Management Settings">App Management Settings</div>
                                </a>
                            </li>
                        @endif
                        @if(in_array(auth()->user()->role_id, [11, 33,34,37,38, 77, 84, 128, 131, 132, 134, 135, 137, 138]))
                            <li class="menu-item @if(Request::is('itinerary_settings.pdf')) active @endif">
                                <a href="{{ route('itinerary_settings.pdf') }}" class="menu-link">
                                    <div data-i18n="Itinerary Settings">Itinerary Settings</div>
                                </a>
                            </li>
                            <li class="menu-item @if(Request::is('quotation_settings.pdf')) active @endif">
                                <a href="{{ route('quotation_settings.pdf') }}" class="menu-link">
                                    <div data-i18n="Quotation Settings">Quotation Settings</div>
                                </a>
                            </li>
                        @endif

                        @if(in_array(auth()->user()->role_id, [11, 36, 126,127]))
                            <li class="menu-item @if(Request::is('booking-list/daily-arrival')) active @endif">
                                <a href="{{ route('booking-list.daily-arrival') }}" class="menu-link">
                                    <div data-i18n="Daily Arrival">Daily Arrival</div>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                @endif
                <!-- End Settings -->

                {{-- <!-- Mail -->
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Mail Center">Mail Center</span>
                </li>

                <li class="menu-item @if(Request::is('mail*') && !Request::is('mail/settings*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-mail-send-line"></i>
                        <div data-i18n="Email Templates">Email Templates</div>
                    </a>

                    <ul class="menu-sub">
                        <!-- All Email Templates -->
                        <li class="menu-item @if(Request::is('mail') || Request::is('mail/index')) active @endif">
                            <a href="{{ route('mail.index') }}" class="menu-link">
                                <div data-i18n="All Templates">All Templates</div>
                            </a>
                        </li>
                        
                        <!-- Booking Emails -->
                        <li class="menu-item @if(Request::is('mail/booking-*')) open @endif">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div data-i18n="Booking Emails">Booking Emails</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item @if(Request::is('mail/booking-confirmation')) active @endif">
                                    <a href="{{ route('mail.booking-confirmation') }}" class="menu-link">
                                        <div data-i18n="Confirmation">Confirmation</div>
                                    </a>
                                </li>
                                
                                <li class="menu-item @if(Request::is('mail/booking-reminder')) active @endif">
                                    <a href="{{ route('mail.booking-reminder') }}" class="menu-link">
                                        <div data-i18n="Reminder">Reminder</div>
                                    </a>
                                </li>
                                
                                <li class="menu-item @if(Request::is('mail/booking-cancellation')) active @endif">
                                    <a href="{{ route('mail.booking-cancellation') }}" class="menu-link">
                                        <div data-i18n="Cancellation">Cancellation</div>
                                    </a>
                                </li>
                                
                                <li class="menu-item @if(Request::is('mail/payment-confirmation')) active @endif">
                                    <a href="{{ route('mail.payment-confirmation') }}" class="menu-link">
                                        <div data-i18n="Payment">Payment</div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Tour Emails -->
                        <li class="menu-item @if(Request::is('mail/tour-*')) open @endif">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div data-i18n="Tour Emails">Tour Emails</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item @if(Request::is('mail/tour-itinerary')) active @endif">
                                    <a href="{{ route('mail.tour-itinerary') }}" class="menu-link">
                                        <div data-i18n="Itinerary">Itinerary</div>
                                    </a>
                                </li>
                                
                                <li class="menu-item @if(Request::is('mail/feedback-request')) active @endif">
                                    <a href="{{ route('mail.feedback-request') }}" class="menu-link">
                                        <div data-i18n="Feedback Request">Feedback Request</div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Staff Emails -->
                        <li class="menu-item @if(Request::is('mail/job-*') || Request::is('mail/agent-*')) open @endif">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div data-i18n="Staff Emails">Staff Emails</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item @if(Request::is('mail/job-assignment')) active @endif">
                                    <a href="{{ route('mail.job-assignment') }}" class="menu-link">
                                        <div data-i18n="Job Assignment">Job Assignment</div>
                                    </a>
                                </li>
                                <li class="menu-item @if(Request::is('mail/agent-creation')) active @endif">
                                    <a href="{{ route('mail.agent-creation') }}" class="menu-link">
                                        <div data-i18n="Agent Creation">Agent Creation</div>
                                    </a>
                                </li>
                                <li class="menu-item @if(Request::is('mail/agent-update')) active @endif">
                                    <a href="{{ route('mail.agent-update') }}" class="menu-link">
                                        <div data-i18n="Agent Update">Agent Update</div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Customer Service -->
                        <li class="menu-item @if(Request::is('mail/welcome-*') || Request::is('mail/enquiry-*')) open @endif">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div data-i18n="Customer Service">Customer Service</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item @if(Request::is('mail/welcome-email')) active @endif">
                                    <a href="{{ route('mail.welcome-email') }}" class="menu-link">
                                        <div data-i18n="Welcome Email">Welcome Email</div>
                                    </a>
                                </li>
                                
                                <li class="menu-item @if(Request::is('mail/enquiry-response')) active @endif">
                                    <a href="{{ route('mail.enquiry-response') }}" class="menu-link">
                                        <div data-i18n="Enquiry Response">Enquiry Response</div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li> --}}
                <!-- End Mail -->
                
                <!-- Tranasaction -->
                {{-- @if(hasPermission('transaction'))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Tranasaction">Tranasaction</span>
                </li>
                <li class="menu-item @if(Request::is('transaction')) active @endif"> --}}
                    {{-- @if(hasPermission('transaction')) --}}
                    {{-- <a href="{{ route('transaction') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-hand-heart-line"></i>
                        <div data-i18n="All Tranasaction">All Tranasaction</div>
                    </a> --}}
                    {{-- @endif --}}
                {{-- </li>
                @endif --}}
                <li class="menu-item" style="height: 102px;"></li>
            </ul>
            <!-- Add this right before the closing </ul> tag at the end of the menu -->
           
        </aside>

        <!-- Submenu Modal (for ul.menu-sub items) -->
        <div class="modal fade" id="submenuModal" tabindex="-1" aria-label="Submenu options" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body submenu-modal-body" id="submenuModalBody">
                        <!-- Filled dynamically -->
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enhanced tooltip functionality
            const tooltips = document.querySelectorAll('.menu-tooltip');
            
            tooltips.forEach((tooltip, index) => {
                const tooltipText = tooltip.querySelector('.tooltip-text');
                const menuText = tooltip.querySelector('.menu-text-with-tooltip');
                
                if (tooltipText && menuText) {
                    // Always show tooltip on hover
                    tooltipText.style.display = 'block';
                    
                    // Add hover events for dynamic positioning
                    tooltip.addEventListener('mouseenter', function(e) {
                        // Get the position of the hovered menu item
                        const rect = this.getBoundingClientRect();
                        
                        // Position tooltip to the right of the menu item
                        tooltipText.style.left = (rect.right + 10) + 'px';
                        tooltipText.style.top = (rect.top + (rect.height / 2) - 20) + 'px';
                        
                        // Show tooltip
                        tooltipText.style.visibility = 'visible';
                        tooltipText.style.opacity = '1';
                        tooltipText.style.transform = 'scale(1)';
                        tooltipText.style.display = 'block';
                    });
                    
                    tooltip.addEventListener('mouseleave', function() {
                        tooltipText.style.visibility = 'hidden';
                        tooltipText.style.opacity = '0';
                        tooltipText.style.transform = 'scale(0.8)';
                    });
                    
                    // Ensure tooltip positioning works on different screen sizes
                    function adjustTooltipPosition() {
                        const rect = tooltip.getBoundingClientRect();
                        const tooltipRect = tooltipText.getBoundingClientRect();
                        
                        // If tooltip would go off screen, position it to the left
                        if (rect.right + tooltipRect.width + 20 > window.innerWidth) {
                            tooltipText.style.left = 'auto';
                            tooltipText.style.right = '100%';
                            tooltipText.style.marginLeft = '0';
                            tooltipText.style.marginRight = '15px';
                            
                            // Update arrow direction
                            const arrow = tooltipText.querySelector('::before');
                            if (arrow) {
                                tooltipText.style.setProperty('--arrow-direction', 'right');
                            }
                        }
                    }
                    
                    // Adjust position on window resize
                    window.addEventListener('resize', adjustTooltipPosition);
                    adjustTooltipPosition();
                }
            });

            // Open sidebar submenus in a floating modal instead of expanding inline.
            const submenuModalEl = document.getElementById('submenuModal');
            const submenuModalTitleEl = document.getElementById('submenuModalLabel'); // Optional: header may be removed
            const submenuModalBodyEl = document.getElementById('submenuModalBody');

            if (submenuModalEl && window.bootstrap && window.bootstrap.Modal) {
                const submenuModal = new window.bootstrap.Modal(submenuModalEl, {
                    backdrop: false,
                    focus: false
                });
                let currentModalTrigger = null;
                let hoverSuppressedTrigger = null;

                const isSubmenuModalOpen = () =>
                    submenuModalEl.classList.contains('show') ||
                    submenuModalEl.style.display === 'block' ||
                    submenuModalEl.getAttribute('aria-hidden') === 'false';
                let modalSubmenuStack = [];

                const closeSubmenuModal = () => {
                    if (!isSubmenuModalOpen()) return;
                    hoverSuppressedTrigger = currentModalTrigger;
                    currentModalTrigger = null;
                    modalSubmenuStack = [];
                    submenuModal.hide();
                };

                const renderSubmenuInModal = (submenu, title) => {
                    if (!submenu || !submenuModalBodyEl) return;

                    submenuModalBodyEl.innerHTML = '';
                    const list = document.createElement('ul');
                    list.className = 'submenu-modal-list';

                    if (modalSubmenuStack.length > 1) {
                        const backLi = document.createElement('li');
                        backLi.className = 'menu-item submenu-modal-back';
                        backLi.innerHTML = '<a href="#" class="menu-link submenu-modal-back-link" data-submenu-back="1"><div class="d-flex align-items-center"><i class="ri-arrow-left-line me-2"></i><div data-i18n="Back">Back</div></div></a>';
                        list.appendChild(backLi);
                    }

                    const directLis = Array.from(submenu.children).filter(el => el && el.matches && el.matches('li.menu-item'));
                    (directLis.length ? directLis : Array.from(submenu.querySelectorAll('li.menu-item'))).forEach(li => {
                        const liClone = li.cloneNode(true);

                        // Add a left-side arrow icon before every nested submenu label inside the modal.
                        const modalAnchor = liClone.querySelector('a.menu-link');
                        if (modalAnchor && !modalAnchor.querySelector('.submenu-modal-item-icon')) {
                            // Avoid adding if the original item already has some icon/graphic.
                            if (!modalAnchor.querySelector('i') && !modalAnchor.querySelector('svg')) {
                                const labelNode = modalAnchor.querySelector('[data-i18n]') || modalAnchor.querySelector('div') || modalAnchor.querySelector('span');
                                const iconEl = document.createElement('i');
                                iconEl.className = 'ri-arrow-right-double-fill submenu-modal-item-icon';
                                iconEl.setAttribute('aria-hidden', 'true');
                                if (labelNode && labelNode.parentElement === modalAnchor) {
                                    modalAnchor.insertBefore(iconEl, labelNode);
                                } else {
                                    modalAnchor.prepend(iconEl);
                                }
                            }
                        }

                        // Keep nested submenu DOM so a second-level modal can be opened from modal items.
                        liClone.querySelectorAll('ul.menu-sub').forEach(nested => {
                            nested.style.display = 'none';
                        });
                        list.appendChild(liClone);
                    });

                    submenuModalBodyEl.appendChild(list);
                    if (submenuModalTitleEl) submenuModalTitleEl.textContent = (title || 'Options').trim();
                };

                const layoutMenuEl = document.getElementById('layout-menu');
                if (layoutMenuEl) {
                    // Use event delegation so submenu triggers inside any `menu-header` group open reliably.
                    layoutMenuEl.addEventListener('click', function(e) {
                        const trigger = e.target.closest('a.menu-toggle');
                        if (!trigger || !layoutMenuEl.contains(trigger)) return;

                        const parentItem = trigger.closest('.menu-item');
                        const submenu = parentItem ? parentItem.querySelector('ul.menu-sub') : null;
                        if (!submenu) return;

                        // Requirement: only nested submenu should open in modal.
                        // If this menu-toggle is NOT inside another submenu container, keep the default inline behavior.
                        // (Example: "Restaurant" should stay inline; "Enquiries" inside "Bookings" should open modal.)
                        const isNestedSubmenuTrigger = !!trigger.closest('ul.menu-sub');
                        if (!isNestedSubmenuTrigger) return;

                        // Prevent theme inline submenu toggle; we render it inside modal instead.
                        e.preventDefault();
                        e.stopPropagation();
                        // Some sidebar themes also bind their own click handlers on the same element.
                        // This prevents inline submenu expansion from happening right after our modal opens.
                        e.stopImmediatePropagation();

                        // Modal title from the trigger's i18n label/text.
                        const i18nNode = trigger.querySelector('[data-i18n]');
                        const title = i18nNode ? i18nNode.textContent.trim() : (trigger.textContent || 'Options');
                        if (submenuModalTitleEl) submenuModalTitleEl.textContent = title.trim() || 'Options';

                        currentModalTrigger = trigger;
                        // Clicking a nested submenu should re-enable hover reopening for other triggers.
                        hoverSuppressedTrigger = null;
                        modalSubmenuStack = [];

                        modalSubmenuStack.push({
                            submenu,
                            title
                        });
                        renderSubmenuInModal(submenu, title);

                        // Close any expanded inline state if theme added it already.
                        parentItem.classList.remove('open');
                        // Hide nested inline submenu levels under the clicked trigger.
                        // This ensures only the modal shows nested options.
                        parentItem.querySelectorAll('ul.menu-sub').forEach(u => {
                            u.style.display = 'none';
                        });

                        // Position modal next to the clicked menu item (responsive fallback for small screens).
                        const rect = trigger.getBoundingClientRect();
                        const dialog = submenuModalEl.querySelector('.modal-dialog');
                        if (window.innerWidth < 768) {
                            dialog.style.left = '40%';
                            dialog.style.top = '50%';
                            dialog.style.transform = 'translate(-50%, -50%)';
                            dialog.style.width = '90vw';
                            dialog.style.maxWidth = '540px';
                        } else {
                            dialog.style.transform = 'none';
                            dialog.style.width = '280px';
                            dialog.style.maxWidth = '280px';
                            dialog.style.left = `${rect.right + 14}px`;
                            dialog.style.top = `${Math.max(10, rect.top)}px`;
                        }

                        submenuModal.show();

                        // If modal overflows the viewport horizontally, clamp it.
                        requestAnimationFrame(() => {
                            const dRect = dialog.getBoundingClientRect();
                            if (window.innerWidth >= 768) {
                                if (dRect.right > window.innerWidth - 8) {
                                    const newLeft = Math.max(8, window.innerWidth - dRect.width - 8);
                                    dialog.style.left = `${newLeft}px`;
                                }
                                if (dRect.bottom > window.innerHeight - 8) {
                                    const newTop = Math.max(8, window.innerHeight - dRect.height - 8);
                                    dialog.style.top = `${newTop}px`;
                                }
                            }
                        });
                    }, true);
                }

                // Hide modal after user clicks a link inside it (navigation will happen anyway).
                submenuModalBodyEl.addEventListener('click', function(e) {
                    const backLink = e.target.closest('[data-submenu-back="1"]');
                    if (backLink) {
                        e.preventDefault();
                        if (modalSubmenuStack.length > 1) {
                            modalSubmenuStack.pop();
                            const prev = modalSubmenuStack[modalSubmenuStack.length - 1];
                            renderSubmenuInModal(prev.submenu, prev.title);
                        }
                        return;
                    }

                    const toggleLink = e.target.closest('a.menu-toggle');
                    if (toggleLink) {
                        const li = toggleLink.closest('li.menu-item');
                        const nested = li ? li.querySelector(':scope > ul.menu-sub') : null;
                        if (nested) {
                            e.preventDefault();
                            const i18nNode = toggleLink.querySelector('[data-i18n]');
                            const nestedTitle = i18nNode ? i18nNode.textContent.trim() : (toggleLink.textContent || 'Options');
                            modalSubmenuStack.push({
                                submenu: nested,
                                title: nestedTitle
                            });
                            renderSubmenuInModal(nested, nestedTitle);
                            return;
                        }
                    }

                    const link = e.target.closest('a');
                    if (link) {
                        // Close modal after selection; keep it closed even if the cursor stays hovered on the trigger.
                        closeSubmenuModal();
                    }
                });

                // Reliable close when user clicks the modal wrapper (outside `.modal-content`).
                submenuModalEl.addEventListener('click', function(e) {
                    if (e.target === submenuModalEl) {
                        closeSubmenuModal();
                    }
                }, true);

                // Close modal when clicking/tapping outside (reliable: use pointerdown).
                document.addEventListener('pointerdown', function(e) {
                    if (!isSubmenuModalOpen()) return;

                    // IMPORTANT: `.modal` wrapper covers the whole screen, so we must only treat clicks
                    // inside `.modal-content` as "inside". Everything else should close the modal.
                    const modalContent = submenuModalEl.querySelector('.modal-content');
                    if (modalContent && modalContent.contains(e.target)) return;

                    closeSubmenuModal();
                }, true);

            }
            
            // Enhanced menu interactions
            // Scope hover animation to the real sidebar only; modal items are cloned `.menu-item`s too.
            const menuItems = document.querySelectorAll('#layout-menu .menu-item');
            menuItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('active')) {
                        this.style.transform = 'translateX(2px)';
                    }
                });
                
                item.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('active')) {
                        this.style.transform = 'translateX(0)';
                    }
                });
            });
        });
        </script>        

<!-- Modal for Create Single Tour Pro Initial Information -->
<div class="modal fade" id="createTourProModal" tabindex="-1" aria-labelledby="createTourProModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 800px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title mb-0" id="createTourProModalLabel">
                    <i class="ri-file-list-3-line me-2"></i>Create Single Tour Pro
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createTourProForm" method="POST" action="{{ route('enquiry-form-pro.initialize') }}">
                @csrf
                <div class="modal-body" style="padding: 10px 15px;">
                    <!-- Row 1: Tour Type (Radio), Dates, Pax -->
                    <div class="row g-2 mb-1">
                        <div class="col-2">
                            <label class="form-label small mb-0" style="font-size: 10px;">Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2 mt-1">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tour_type" id="tourTypeFIT" value="FIT" checked required>
                                    <label class="form-check-label small" for="tourTypeFIT" style="font-size: 10px;">FIT</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tour_type" id="tourTypeGroup" value="Group" required>
                                    <label class="form-check-label small" for="tourTypeGroup" style="font-size: 10px;">Group</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <label class="form-label small mb-0" style="font-size: 10px;">Start <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" id="tourStartDate" name="tour_start_date" required style="font-size: 10px;">
                        </div>
                        <div class="col-2">
                            <label class="form-label small mb-0" style="font-size: 10px;">End <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" id="tourEndDate" name="tour_end_date" required style="font-size: 10px;">
                        </div>
                        <div class="col-2">
                            <label class="form-label small mb-0" style="font-size: 10px;">Adult <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm" id="adultCount" name="adult_count" min="0" value="1" required style="font-size: 10px;">
                        </div>
                        <div class="col-2">
                            <label class="form-label small mb-0" style="font-size: 10px;">Child</label>
                            <input type="number" class="form-control form-control-sm" id="childCount" name="child_count" min="0" value="0" style="font-size: 10px;">
                        </div>
                        <div class="col-2">
                            <label class="form-label small mb-0" style="font-size: 10px;">Infant</label>
                            <input type="number" class="form-control form-control-sm" id="infantCount" name="infant_count" min="0" value="0" style="font-size: 10px;">
                        </div>
                    </div>

                    <!-- Row 2: Destination (moved before Agency) -->
                    <div class="row g-2 mb-1">
                        <div class="col-12">
                            <label class="form-label small mb-0 d-flex align-items-center" style="font-size: 10px;">
                                Destination <span class="text-danger">*</span>
                                <div class="form-check form-check-inline ms-3 mb-0">
                                    <input class="form-check-input" type="checkbox" id="multipleDestination" name="multiple_destination" value="1" style="margin-top: 0;">
                                    <label class="form-check-label" for="multipleDestination" style="font-size: 10px;">Multiple Cities</label>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Destination (Single) - Text Input with Autocomplete (Single Select) -->
                    <div class="row g-2 mb-1" id="singleDestinationDiv">
                        <div class="col-12">
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-sm" id="destinationSingle" placeholder="Type to search destination..." autocomplete="off" style="font-size: 10px;" readonly onfocus="this.removeAttribute('readonly');">
                                <div id="destinationSuggestionsSingle" class="list-group position-absolute w-100" style="z-index: 1050; max-height: 120px; overflow-y: auto; display: none; box-shadow: 0 2px 8px rgba(0,0,0,0.15); font-size: 10px; background-color: white; border: 1px solid #dee2e6;"></div>
                            </div>
                            <input type="hidden" id="destinationSingleValue" name="destination_single">
                        </div>
                    </div>

                    <!-- Destination (Multiple) - Text Input with Autocomplete -->
                    <div class="row g-2 mb-1" id="multipleDestinationDiv" style="display: none;">
                        <div class="col-12">
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-sm" id="destinationMultiple" placeholder="Type to search and select multiple destinations..." autocomplete="off" style="font-size: 10px;" readonly onfocus="this.removeAttribute('readonly');">
                                <div id="destinationSuggestions" class="list-group position-absolute w-100" style="z-index: 1050; max-height: 120px; overflow-y: auto; display: none; box-shadow: 0 2px 8px rgba(0,0,0,0.15); font-size: 10px; background-color: white; border: 1px solid #dee2e6;"></div>
                            </div>
                            <div id="selectedDestinations" class="mt-1"></div>
                            <input type="hidden" id="destinationsArray" name="destinations">
                        </div>
                    </div>

                    <!-- Row 3: Agency, Agent -->
                    <div class="row g-2 mb-1">
                        <div class="col-6">
                            <label class="form-label small mb-0" style="font-size: 10px;">Agency <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-sm" id="agencySelectModal" placeholder="Select destination first..." autocomplete="off" style="font-size: 10px;" disabled readonly onfocus="this.removeAttribute('readonly');">
                                <div id="agencySuggestions" class="list-group position-absolute w-100" style="z-index: 1050; max-height: 150px; overflow-y: auto; display: none; box-shadow: 0 2px 8px rgba(0,0,0,0.15); font-size: 10px; background-color: white; border: 1px solid #dee2e6;"></div>
                            </div>
                            <input type="hidden" id="agencyIdValue" name="agency_id" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-0" style="font-size: 10px;">Agent <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-sm" id="agentSelectModal" placeholder="Select agency first..." autocomplete="off" style="font-size: 10px;" disabled readonly onfocus="this.removeAttribute('readonly');">
                                <div id="agentSuggestions" class="list-group position-absolute w-100" style="z-index: 1050; max-height: 150px; overflow-y: auto; display: none; box-shadow: 0 2px 8px rgba(0,0,0,0.15); font-size: 10px; background-color: white; border: 1px solid #dee2e6;"></div>
                            </div>
                            <input type="hidden" id="agentIdValue" name="agent_id" required>
                        </div>
                    </div>

                    <!-- Row 4: Customer Details -->
                    <div class="row g-2 mb-1">
                        <div class="col-2">   <!-- Increased from col-1 -->
                            <label class="form-label small mb-0" style="font-size: 10px;">
                                Sal. <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-sm" id="salutation" name="salutation" required>
                                <option value="Mr">Mr</option>
                                <option value="Mrs">Mrs</option>
                                <option value="Ms">Ms</option>
                                <option value="Dr">Dr</option>
                            </select>
                        </div>
                    
                        <div class="col-5">   <!-- Reduced from col-6 -->
                            <label class="form-label small mb-0" style="font-size: 10px;">
                                Customer Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" id="customerName" name="customer_name" required>
                        </div>
                    
                        <div class="col-5">
                            <label class="form-label small mb-0" style="font-size: 10px;">
                                Contact Number
                            </label>
                            <input type="text" class="form-control form-control-sm" id="contactNumber" name="contact_number">
                        </div>
                    </div>

                    <!-- Row 5: Email (optional) -->
                    <div class="row g-2 mb-1">
                        <div class="col-12">
                            <label class="form-label small mb-0" style="font-size: 10px;">Email</label>
                            <input type="email" class="form-control form-control-sm" id="customerEmail" name="email" placeholder="Optional" autocomplete="email" style="font-size: 10px;">
                        </div>
                    </div>                    

                </div>
                <div class="modal-footer py-1" style="padding: 8px 15px;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="font-size: 11px; padding: 4px 12px;">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success btn-sm" id="submitTourProBtn" style="font-size: 11px; padding: 4px 12px;">
                        <i class="ri-check-line me-1"></i>Continue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Open modal when Create Single Tour Pro is clicked
    const createTourBtn = document.getElementById('createSingleTourProBtn');
    if (createTourBtn) {
        createTourBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('createTourProModal'));
            modal.show();
            loadDestinations();
        });
    }

    // Store agencies list globally for autocomplete
    let availableAgencies = [];
    
    // Load Agencies by single destination
    function loadAgenciesByDestination(destination) {
        const agencyInput = document.getElementById('agencySelectModal');
        agencyInput.value = 'Loading agencies...';
        agencyInput.disabled = true;
        
        // Reset agency and agent
        document.getElementById('agencyIdValue').value = '';
        const agentInput = document.getElementById('agentSelectModal');
        agentInput.value = '';
        agentInput.placeholder = 'Select agency first...';
        agentInput.disabled = true;
        document.getElementById('agentIdValue').value = '';
        
        fetch('{{ route("enquiry-form-pro.get-agencies") }}?destination=' + encodeURIComponent(destination))
            .then(response => response.json())
            .then(data => {
                console.log('Agencies loaded for destination:', destination, 'DMC ID:', data.dmc_id, 'Count:', data.count);
                if (data.success && data.agencies.length > 0) {
                    availableAgencies = data.agencies;
                    agencyInput.value = '';
                    agencyInput.placeholder = 'Type to search agency...';
                    agencyInput.disabled = false;
                } else {
                    availableAgencies = [];
                    agencyInput.value = '';
                    agencyInput.placeholder = 'No agencies available (filtered by destination & DMC)';
                }
            })
            .catch(error => {
                console.error('Error loading agencies:', error);
                availableAgencies = [];
                agencyInput.value = '';
                agencyInput.placeholder = 'Error loading agencies';
            });
    }

    // Load Agencies by multiple destinations
    function loadAgenciesByDestinations() {
        if (selectedDestinations.length === 0) return;
        
        const agencyInput = document.getElementById('agencySelectModal');
        agencyInput.value = 'Loading agencies...';
        agencyInput.disabled = true;
        
        // Reset agency and agent
        document.getElementById('agencyIdValue').value = '';
        const agentInput = document.getElementById('agentSelectModal');
        agentInput.value = '';
        agentInput.placeholder = 'Select agency first...';
        agentInput.disabled = true;
        document.getElementById('agentIdValue').value = '';
        
        const destinations = selectedDestinations.join(',');
        
        fetch('{{ route("enquiry-form-pro.get-agencies") }}?destinations=' + encodeURIComponent(destinations))
            .then(response => response.json())
            .then(data => {
                console.log('Agencies loaded for destinations:', destinations, 'DMC ID:', data.dmc_id, 'Count:', data.count);
                if (data.success && data.agencies.length > 0) {
                    availableAgencies = data.agencies;
                    agencyInput.value = '';
                    agencyInput.placeholder = 'Type to search agency...';
                    agencyInput.disabled = false;
                } else {
                    availableAgencies = [];
                    agencyInput.value = '';
                    agencyInput.placeholder = 'No agencies available (filtered by destinations & DMC)';
                }
            })
            .catch(error => {
                console.error('Error loading agencies:', error);
                availableAgencies = [];
                agencyInput.value = '';
                agencyInput.placeholder = 'Error loading agencies';
            });
    }

    // Agency autocomplete
    const agencyInput = document.getElementById('agencySelectModal');
    const agencySuggestions = document.getElementById('agencySuggestions');
    const agencyIdValue = document.getElementById('agencyIdValue');

    agencyInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        
        if (query.length < 1) {
            agencySuggestions.style.display = 'none';
            return;
        }

        const filtered = availableAgencies.filter(agency => 
            agency.agency_name.toLowerCase().includes(query)
        );

        if (filtered.length > 0) {
            agencySuggestions.innerHTML = '';
            filtered.forEach(agency => {
                const item = document.createElement('a');
                item.href = 'javascript:void(0);';
                item.className = 'list-group-item list-group-item-action';
                item.style.padding = '6px 10px';
                item.style.fontSize = '10px';
                item.style.cursor = 'pointer';
                item.textContent = agency.agency_name;
                item.addEventListener('click', function() {
                    agencyInput.value = agency.agency_name;
                    agencyIdValue.value = agency.agency_id;
                    agencySuggestions.style.display = 'none';
                    // Load agents for this agency
                    loadAgentsByAgency(agency.agency_id);
                });
                agencySuggestions.appendChild(item);
            });
            agencySuggestions.style.display = 'block';
        } else {
            agencySuggestions.style.display = 'none';
        }
    });

    // Store agents list globally for autocomplete
    let availableAgents = [];

    // Load agents by agency
    function loadAgentsByAgency(agencyId) {
        const agentInput = document.getElementById('agentSelectModal');
        agentInput.value = 'Loading agents...';
        agentInput.disabled = true;
        document.getElementById('agentIdValue').value = '';

        fetch('{{ route("enquiry-form-pro.get-agents") }}?agency_id=' + agencyId)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.agents.length > 0) {
                    availableAgents = data.agents;
                    agentInput.value = '';
                    agentInput.placeholder = 'Type to search agent...';
                    agentInput.disabled = false;
                } else {
                    availableAgents = [];
                    agentInput.value = '';
                    agentInput.placeholder = 'No agents available';
                }
            })
            .catch(error => {
                console.error('Error loading agents:', error);
                availableAgents = [];
                agentInput.value = '';
                agentInput.placeholder = 'Error loading agents';
            });
    }

    // Agent autocomplete
    const agentInput = document.getElementById('agentSelectModal');
    const agentSuggestions = document.getElementById('agentSuggestions');
    const agentIdValue = document.getElementById('agentIdValue');

    agentInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        
        if (query.length < 1) {
            agentSuggestions.style.display = 'none';
            return;
        }

        const filtered = availableAgents.filter(agent => 
            agent.name.toLowerCase().includes(query)
        );

        if (filtered.length > 0) {
            agentSuggestions.innerHTML = '';
            filtered.forEach(agent => {
                const item = document.createElement('a');
                item.href = 'javascript:void(0);';
                item.className = 'list-group-item list-group-item-action';
                item.style.padding = '6px 10px';
                item.style.fontSize = '10px';
                item.style.cursor = 'pointer';
                item.textContent = agent.name;
                item.addEventListener('click', function() {
                    agentInput.value = agent.name;
                    agentIdValue.value = agent.agent_id;
                    agentSuggestions.style.display = 'none';
                });
                agentSuggestions.appendChild(item);
            });
            agentSuggestions.style.display = 'block';
        } else {
            agentSuggestions.style.display = 'none';
        }
    });

    // Multiple destination checkbox toggle
    const multipleDestCheckbox = document.getElementById('multipleDestination');
    const singleDestDiv = document.getElementById('singleDestinationDiv');
    const multipleDestDiv = document.getElementById('multipleDestinationDiv');
    const destinationSingle = document.getElementById('destinationSingle');
    const destinationMultiple = document.getElementById('destinationMultiple');

    multipleDestCheckbox.addEventListener('change', function() {
        if (this.checked) {
            // Switch to multiple mode
            singleDestDiv.style.display = 'none';
            multipleDestDiv.style.display = 'block';
            // Clear single destination
            destinationSingleInput.value = '';
            destinationSingleValue.value = '';
        } else {
            // Switch to single mode
            singleDestDiv.style.display = 'block';
            multipleDestDiv.style.display = 'none';
            // Clear multiple destinations
            destinationInput.value = '';
            selectedDestinations = [];
            updateSelectedDestinations();
        }
        
        // Reset agency and agent when switching modes
        const agencyInputReset = document.getElementById('agencySelectModal');
        agencyInputReset.value = '';
        agencyInputReset.placeholder = 'Select destination first...';
        agencyInputReset.disabled = true;
        document.getElementById('agencyIdValue').value = '';
        availableAgencies = [];
        
        const agentInputReset = document.getElementById('agentSelectModal');
        agentInputReset.value = '';
        agentInputReset.placeholder = 'Select agency first...';
        agentInputReset.disabled = true;
        document.getElementById('agentIdValue').value = '';
        availableAgents = [];
    });

    // Load destinations
    let allDestinations = [];
    function loadDestinations() {
        fetch('{{ route("enquiry-form-pro.get-destinations") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.destinations.length > 0) {
                    allDestinations = data.destinations;
                }
            })
            .catch(error => {
                console.error('Error loading destinations:', error);
            });
    }

    // Single destination autocomplete
    const destinationSingleInput = document.getElementById('destinationSingle');
    const suggestionBoxSingle = document.getElementById('destinationSuggestionsSingle');
    const destinationSingleValue = document.getElementById('destinationSingleValue');

    destinationSingleInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        
        if (query.length < 1) {
            suggestionBoxSingle.style.display = 'none';
            return;
        }

        const filtered = allDestinations.filter(dest => 
            dest.name.toLowerCase().includes(query)
        );

        if (filtered.length > 0) {
            suggestionBoxSingle.innerHTML = '';
            filtered.forEach(dest => {
                const item = document.createElement('a');
                item.href = 'javascript:void(0);';
                item.className = 'list-group-item list-group-item-action';
                item.style.padding = '6px 10px';
                item.style.fontSize = '10px';
                item.style.cursor = 'pointer';
                item.textContent = dest.name;
                item.addEventListener('click', function() {
                    destinationSingleInput.value = dest.name;
                    destinationSingleValue.value = dest.name;
                    suggestionBoxSingle.style.display = 'none';
                    // Load agencies for this destination
                    loadAgenciesByDestination(dest.name);
                });
                suggestionBoxSingle.appendChild(item);
            });
            suggestionBoxSingle.style.display = 'block';
        } else {
            suggestionBoxSingle.style.display = 'none';
        }
    });

    // Multiple destination autocomplete
    const destinationInput = document.getElementById('destinationMultiple');
    const suggestionBox = document.getElementById('destinationSuggestions');
    const selectedDestinationsDiv = document.getElementById('selectedDestinations');
    let selectedDestinations = [];

    destinationInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        
        if (query.length < 1) {
            suggestionBox.style.display = 'none';
            return;
        }

        const filtered = allDestinations.filter(dest => 
            dest.name.toLowerCase().includes(query) && 
            !selectedDestinations.includes(dest.name)
        );

        if (filtered.length > 0) {
            suggestionBox.innerHTML = '';
            filtered.forEach(dest => {
                const item = document.createElement('a');
                item.href = 'javascript:void(0);';
                item.className = 'list-group-item list-group-item-action';
                item.style.padding = '6px 10px';
                item.style.fontSize = '10px';
                item.style.cursor = 'pointer';
                item.textContent = dest.name;
                item.addEventListener('click', function() {
                    addDestination(dest.name);
                    destinationInput.value = '';
                    suggestionBox.style.display = 'none';
                });
                suggestionBox.appendChild(item);
            });
            suggestionBox.style.display = 'block';
        } else {
            suggestionBox.style.display = 'none';
        }
    });

    function addDestination(name) {
        if (!selectedDestinations.includes(name)) {
            selectedDestinations.push(name);
            updateSelectedDestinations();
            // Load agencies for selected destinations
            loadAgenciesByDestinations();
        }
    }

    function removeDestination(name) {
        selectedDestinations = selectedDestinations.filter(d => d !== name);
        updateSelectedDestinations();
        // Reload agencies based on remaining destinations
        if (selectedDestinations.length > 0) {
            loadAgenciesByDestinations();
        } else {
            // Reset agency if no destinations selected
            const agencyInputReset = document.getElementById('agencySelectModal');
            agencyInputReset.value = '';
            agencyInputReset.placeholder = 'Select destination first...';
            agencyInputReset.disabled = true;
            document.getElementById('agencyIdValue').value = '';
            availableAgencies = [];
            // Also reset agent
            const agentInputReset = document.getElementById('agentSelectModal');
            agentInputReset.value = '';
            agentInputReset.placeholder = 'Select agency first...';
            agentInputReset.disabled = true;
            document.getElementById('agentIdValue').value = '';
            availableAgents = [];
        }
    }

    function updateSelectedDestinations() {
        selectedDestinationsDiv.innerHTML = '';
        selectedDestinations.forEach(dest => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-info me-1 mb-1 d-inline-flex align-items-center';
            badge.style.fontSize = '11px';
            badge.style.padding = '4px 8px';
            badge.innerHTML = `${dest} <i class="ri-close-line ms-1" style="cursor: pointer; font-size: 14px;"></i>`;
            badge.querySelector('i').addEventListener('click', function() {
                removeDestination(dest);
            });
            selectedDestinationsDiv.appendChild(badge);
        });

        // Update hidden input
        document.getElementById('destinationsArray').value = JSON.stringify(selectedDestinations);
    }

    // Set min date for start date (today)
    const tourStartDateInput = document.getElementById('tourStartDate');
    const tourEndDateInput = document.getElementById('tourEndDate');
    
    if (tourStartDateInput && tourEndDateInput) {
        const today = new Date().toISOString().split('T')[0];
        tourStartDateInput.setAttribute('min', today);
        tourStartDateInput.value = today;
        
        // Set end date to tomorrow by default and set min
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tomorrowStr = tomorrow.toISOString().split('T')[0];
        tourEndDateInput.value = tomorrowStr;
        tourEndDateInput.setAttribute('min', tomorrowStr);
        
        // Update end date min when start date changes
        tourStartDateInput.addEventListener('change', function() {
            const startDate = new Date(this.value);
            const minEndDate = new Date(startDate);
            minEndDate.setDate(minEndDate.getDate() + 1);
            const minEndDateStr = minEndDate.toISOString().split('T')[0];
            tourEndDateInput.setAttribute('min', minEndDateStr);
            
            // If end date is less than start date + 1, update it
            if (new Date(tourEndDateInput.value) <= startDate) {
                tourEndDateInput.value = minEndDateStr;
            }
        });
    }
    
    // Form validation
    document.getElementById('createTourProForm').addEventListener('submit', function(e) {
        const multipleDestChecked = document.getElementById('multipleDestination').checked;
        
        // Validate destination based on mode
        if (multipleDestChecked) {
            if (selectedDestinations.length === 0) {
                e.preventDefault();
                alert('Please select at least one destination');
                return false;
            }
        } else {
            const singleDest = document.getElementById('destinationSingleValue').value;
            if (!singleDest || singleDest.trim() === '') {
                e.preventDefault();
                alert('Please select a destination');
                return false;
            }
        }

        // Validate dates
        const startDate = new Date(document.getElementById('tourStartDate').value);
        const endDate = new Date(document.getElementById('tourEndDate').value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (startDate < today) {
            e.preventDefault();
            alert('Start date cannot be in the past');
            return false;
        }
        
        const minEndDate = new Date(startDate);
        minEndDate.setDate(minEndDate.getDate() + 1);
        
        if (endDate < minEndDate) {
            e.preventDefault();
            alert('End date must be at least 1 day after start date');
            return false;
        }

        // Check at least one person
        const adults = parseInt(document.getElementById('adultCount').value) || 0;
        const children = parseInt(document.getElementById('childCount').value) || 0;
        const infants = parseInt(document.getElementById('infantCount').value) || 0;
        
        if (adults + children + infants === 0) {
            e.preventDefault();
            alert('Please specify at least one passenger (Adult, Child, or Infant)');
            return false;
        }
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        // Close multiple destinations suggestions
        if (!destinationInput.contains(e.target) && !suggestionBox.contains(e.target)) {
            suggestionBox.style.display = 'none';
        }
        // Close single destination suggestions
        if (!destinationSingleInput.contains(e.target) && !suggestionBoxSingle.contains(e.target)) {
            suggestionBoxSingle.style.display = 'none';
        }
        // Close agency suggestions
        if (!agencyInput.contains(e.target) && !agencySuggestions.contains(e.target)) {
            agencySuggestions.style.display = 'none';
        }
        // Close agent suggestions
        if (!agentInput.contains(e.target) && !agentSuggestions.contains(e.target)) {
            agentSuggestions.style.display = 'none';
        }
    });
});
</script>


