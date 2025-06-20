@extends('layouts.layout')
@section('content')
<div class="thankyou-container">
    <div class="thankyou-card">
        <div class="card-content">
            <!-- Success Icon Animation -->
            <div class="success-animation">
                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            
            <!-- Attraction Avatar -->
            <div class="avatar-container">
                <div class="avatar">
                    <span>🏰</span>
                </div>
                <div class="avatar-ring"></div>
            </div>

            <!-- Main Content -->
            <h1 class="title">Thank You!</h1>
            <p class="message">Your attraction submission has been received</p>
            
            <!-- Progress Timeline -->
            <div class="progress-timeline">
                <div class="progress-line">
                    <div class="progress-line-active"></div>
                </div>
                
                <div class="progress-steps">
                    <div class="progress-step completed">
                        <div class="step-dot">
                            <span>S</span>
                        </div>
                        <div class="step-label">Submitted</div>
                    </div>
                    
                    <div class="progress-step current">
                        <div class="step-dot">
                            <span>U</span>
                        </div>
                        <div class="step-label">Under Review</div>
                    </div>
                    
                    <div class="progress-step">
                        <div class="step-dot">
                            <span>A</span>
                        </div>
                        <div class="step-label">Approval</div>
                    </div>
                </div>
            </div>
            
            <!-- Notification Preview -->
            <div class="notification-preview">
                <div class="notification-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="notification-text">
                    <p class="notification-title">Coming Soon</p>
                    <p class="notification-message">You'll receive a notification when your attraction is approved</p>
                </div>
            </div>
            
            <!-- Action Button -->
            <a href="{{ route('attraction.index') }}" class="action-button">
                Back to Attractions <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <!-- Decorative Elements -->
        <div class="decoration decoration-1"></div>
        <div class="decoration decoration-2"></div>
        <div class="decoration decoration-3"></div>
        <div class="particles-container" id="particles-js"></div>
    </div>
</div>
@endsection

@section('css')
<style>
    /* Base Styles */
    .thankyou-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        padding: 2rem;
    }
    
    .thankyou-card {
        position: relative;
        width: 100%;
        max-width: 800px;
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        transition: all 0.5s ease;
        transform: translateY(0);
    }
    
    .thankyou-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 30px 70px rgba(111, 66, 193, 0.15);
    }
    
    .card-content {
        position: relative;
        z-index: 10;
        padding: 3.5rem;
        text-align: center;
    }
    
    /* Success Checkmark Animation */
    .success-animation {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 50px;
        height: 50px;
    }
    
    .checkmark {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: block;
        stroke-width: 2;
        stroke: #4bb71b;
        stroke-miterlimit: 10;
        box-shadow: inset 0px 0px 0px #4bb71b;
        animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
    }
    
    .checkmark__circle {
        stroke-dasharray: 166;
        stroke-dashoffset: 166;
        stroke-width: 2;
        stroke-miterlimit: 10;
        stroke: #4bb71b;
        fill: none;
        animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }
    
    .checkmark__check {
        transform-origin: 50% 50%;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }
    
    @keyframes stroke {
        100% {
            stroke-dashoffset: 0;
        }
    }
    
    @keyframes scale {
        0%, 100% {
            transform: none;
        }
        50% {
            transform: scale3d(1.1, 1.1, 1);
        }
    }
    
    @keyframes fill {
        100% {
            box-shadow: inset 0px 0px 0px 30px #4bb71b;
        }
    }
    
    /* Avatar Styles */
    .avatar-container {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto 2rem;
    }
    
    .avatar {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);
        border-radius: 50%;
        font-size: 3.5rem;
        z-index: 2;
        box-shadow: 0 10px 20px rgba(142, 68, 173, 0.2);
    }
    
    .avatar-ring {
        position: absolute;
        top: -10px;
        left: -10px;
        width: calc(100% + 20px);
        height: calc(100% + 20px);
        border-radius: 50%;
        border: 2px dashed rgba(142, 68, 173, 0.5);
        animation: spin 15s linear infinite;
        z-index: 1;
    }
    
    @keyframes spin {
        100% {
            transform: rotate(360deg);
        }
    }
    
    /* Typography */
    .title {
        color: #1a202c;
        font-size: 2.8rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        background: linear-gradient(90deg, #8e44ad, #9b59b6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .message {
        color: #4a5568;
        font-size: 1.3rem;
        margin-bottom: 2.5rem;
    }
    
    /* Progress Timeline */
    .progress-timeline {
        position: relative;
        margin: 3rem 0;
        padding: 1rem 0;
    }
    
    /* Enhanced Progress Line */
    .progress-line {
        position: absolute;
        top: 50%;
        left: 16.666%;
        width: 66.667%;
        height: 4px;
        background-color: #e2e8f0;
        transform: translateY(-50%);
        z-index: 1;
        border-radius: 4px;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    /* Enhanced Active Progress Line */
    .progress-line-active {
        position: absolute;
        top: 0;
        left: 0;
        width: 50%;
        height: 100%;
        background: linear-gradient(to right, #6a1b9a, #9c27b0);
        z-index: 2;
        border-radius: 4px;
        transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 3px rgba(106, 27, 154, 0.3);
        animation: progressPulse 2s infinite;
    }
    
    @keyframes progressPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(106, 27, 154, 0.4);
        }
        70% {
            box-shadow: 0 0 0 5px rgba(106, 27, 154, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(106, 27, 154, 0);
        }
    }
    
    /* Progress Steps Container */
    .progress-steps {
        position: relative;
        display: flex;
        justify-content: space-between;
        z-index: 3;
    }
    
    /* Individual Step */
    .progress-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 33.333%;
    }
    
    /* Step Dot */
    .step-dot {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        border: 3px solid white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        font-weight: bold;
        color: white;
        position: relative;
    }
    
    /* Add a subtle bezel effect to dots */
    .step-dot::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 50%;
        box-shadow: inset 0 2px 3px rgba(255, 255, 255, 0.4), 
                    inset 0 -2px 3px rgba(0, 0, 0, 0.2);
        pointer-events: none;
    }
    
    /* Step Labels */
    .step-label {
        font-size: 0.95rem;
        font-weight: 600;
        text-align: center;
        color: #64748b;
        transition: all 0.3s ease;
    }
    
    /* Completed Step */
    .progress-step.completed .step-dot {
        background-color: #6a1b9a; /* Deep Purple */
        transform: scale(1.1);
    }
    
    .progress-step.completed .step-label {
        color: #6a1b9a;
    }
    
    /* Current Step */
    .progress-step.current .step-dot {
        background-color: #9c27b0; /* Medium Purple */
        transform: scale(1.1);
        animation: pulse 2s infinite;
    }
    
    .progress-step.current .step-label {
        color: #9c27b0;
    }
    
    /* Future Step with Green Color (for approval) */
    .progress-step:last-child .step-dot {
        background-color: #a3a3a3; /* Dimmed green */
    }
    
    .progress-step:last-child.completed .step-dot {
        background-color: #10b981; /* Green */
    }
    
    /* Pulse Animation */
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(156, 39, 176, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(156, 39, 176, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(156, 39, 176, 0);
        }
    }
    
    /* Notification Preview */
    .notification-preview {
        display: flex;
        align-items: center;
        background: #f7fafc;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin: 2rem 0;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05);
        text-align: left;
        border-left: 4px solid #8e44ad;
        transition: all 0.3s ease;
    }
    
    .notification-preview:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08);
    }
    
    .notification-icon {
        font-size: 1.5rem;
        color: #8e44ad;
        margin-right: 1rem;
    }
    
    .notification-text {
        flex: 1;
    }
    
    .notification-title {
        font-weight: 600;
        color: #1a202c;
        margin: 0 0 0.25rem;
    }
    
    .notification-message {
        color: #718096;
        margin: 0;
        font-size: 0.9rem;
    }
    
    /* Action Button */
    .action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(90deg, #8e44ad, #9b59b6);
        color: white;
        font-weight: 600;
        padding: 1rem 2rem;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 10px 20px rgba(142, 68, 173, 0.3);
        margin-top: 1.5rem;
    }
    
    .action-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 25px rgba(142, 68, 173, 0.4);
        color: white;
    }
    
    .action-button i {
        margin-left: 10px;
        transition: transform 0.3s ease;
    }
    
    .action-button:hover i {
        transform: translateX(5px);
    }
    
    /* Decorative Elements */
    .decoration {
        position: absolute;
        background: linear-gradient(135deg, rgba(142, 68, 173, 0.1), rgba(155, 89, 182, 0.1));
        border-radius: 50%;
    }
    
    .decoration-1 {
        width: 300px;
        height: 300px;
        top: -150px;
        left: -150px;
    }
    
    .decoration-2 {
        width: 200px;
        height: 200px;
        bottom: -100px;
        right: -100px;
    }
    
    .decoration-3 {
        width: 100px;
        height: 100px;
        top: 20%;
        right: -50px;
    }
    
    .particles-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }
    
    /* Responsive Styles */
    @media (max-width: 768px) {
        .card-content {
            padding: 2rem;
        }
        
        .title {
            font-size: 2.2rem;
        }
        
        .message {
            font-size: 1.1rem;
        }
        
        .progress-timeline {
            margin: 2rem 0;
        }
        
        .step-dot {
            width: 36px;
            height: 36px;
            font-size: 0.9rem;
        }
        
        .step-label {
            font-size: 0.8rem;
        }
    }
    
    @media (max-width: 480px) {
        .step-dot {
            width: 32px;
            height: 32px;
            font-size: 0.8rem;
        }
        
        .step-label {
            font-size: 0.75rem;
        }
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize particles.js
        if (window.particlesJS) {
            particlesJS('particles-js', {
                "particles": {
                    "number": {
                        "value": 20,
                        "density": {
                            "enable": true,
                            "value_area": 800
                        }
                    },
                    "color": {
                        "value": "#8e44ad"
                    },
                    "shape": {
                        "type": "circle"
                    },
                    "opacity": {
                        "value": 0.2,
                        "random": true
                    },
                    "size": {
                        "value": 5,
                        "random": true
                    },
                    "line_linked": {
                        "enable": false
                    },
                    "move": {
                        "enable": true,
                        "speed": 1,
                        "direction": "none",
                        "random": true,
                        "straight": false,
                        "out_mode": "out",
                        "bounce": false
                    }
                },
                "interactivity": {
                    "detect_on": "canvas",
                    "events": {
                        "onhover": {
                            "enable": true,
                            "mode": "bubble"
                        },
                        "onclick": {
                            "enable": true,
                            "mode": "push"
                        }
                    },
                    "modes": {
                        "bubble": {
                            "distance": 250,
                            "size": 6,
                            "duration": 2,
                            "opacity": 0.4,
                            "speed": 3
                        },
                        "push": {
                            "particles_nb": 4
                        }
                    }
                },
                "retina_detect": true
            });
        }
        
        // Animate the progress line
        setTimeout(function() {
            const progressLine = document.querySelector('.progress-line-active');
            if (progressLine) {
                progressLine.style.width = '50%'; // Halfway through the second step
            }
        }, 800);
    });
</script>
@endsection

