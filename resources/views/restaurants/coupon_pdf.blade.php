<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Restaurant Coupon - {{ $display_id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            width: 600px;
            height: 300px;
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: Arial, sans-serif;
        }
        
        .voucher {
            width: 600px;
            height: 300px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 8px;
            box-sizing: border-box;
            border-radius: 12px;
        }
        
        .voucher-content {
            width: 100%;
            height: 100%;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
        }
        
        .voucher-header {
            background: linear-gradient(135deg, #1a202c 0%, #2d3748 50%, #4a5568 100%);
            color: white;
            padding: 12px 16px;
            text-align: center;
            position: relative;
        }
        
        .premium-badge {
            position: absolute;
            top: 8px;
            right: 12px;
            background: #ffd700;
            color: #1a202c;
            padding: 3px 6px;
            border-radius: 8px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .voucher-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            color: #ffffff;
            letter-spacing: 1px;
        }
        
        .voucher-subtitle {
            font-size: 10px;
            margin: 4px 0 0 0;
            color: #ffffff;
            opacity: 0.85;
        }
        
        .voucher-body {
            padding: 12px;
            background: #f8fafc;
            flex: 1;
            font-size: 11px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 8px;
        }
        
        .restaurant-info {
            padding: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 6px;
            color: white;
        }
        
        .customer-info {
            padding: 8px;
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            border-radius: 6px;
            color: white;
        }
        
        .meals-info {
            padding: 8px;
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            border-radius: 6px;
            color: white;
        }
        
        .info-title {
            font-size: 10px;
            font-weight: bold;
            margin: 0 0 4px 0;
        }
        
        .info-content {
            font-size: 9px;
            opacity: 0.9;
            line-height: 1.3;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-bottom: 8px;
        }
        
        .detail-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px;
            text-align: center;
            border-top: 2px solid #667eea;
        }
        
        .detail-label {
            font-size: 7px;
            color: #718096;
            text-transform: uppercase;
            margin-bottom: 2px;
            font-weight: bold;
        }
        
        .detail-value {
            font-size: 9px;
            font-weight: bold;
            color: #2d3748;
        }
        
        .voucher-footer {
            background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 9px;
            margin-top: auto;
        }
        
        .coupon-code {
            background: #ffd700;
            color: #1a202c;
            padding: 6px 12px;
            border-radius: 16px;
            display: inline-block;
            margin: 4px 0;
            font-weight: bold;
            font-size: 9px;
        }
        
        .validity {
            font-size: 8px;
            opacity: 0.8;
            line-height: 1.3;
        }
    </style>
</head>
<body>
    <div class="voucher">
        <div class="voucher-content">
            <div class="voucher-header">
                <div class="premium-badge">Premium</div>
                <h1 class="voucher-title">🎫 VOUCHER</h1>
                <p class="voucher-subtitle">Exclusive Dining Experience</p>
            </div>
            
            <div class="voucher-body">
                <div class="info-grid">
                    <div class="restaurant-info">
                        <div class="info-title">🍽️ Restaurant</div>
                        <div class="info-content">
                            {{ $bookingDetails['restaurantName'] ?? 'International Cuisine' }}
                        </div>
                    </div>
                    
                    <div class="customer-info">
                        <div class="info-title">👤 Customer Details</div>
                        <div class="info-content">
                            {{ $bookingDetails['fullName'] ?? 'Guest' }}<br>
                            📧 {{ $bookingDetails['email'] ?? 'N/A' }}<br>
                            📞 {{ $bookingDetails['phone'] ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="meals-info">
                        <div class="info-title">🍽️ Meals</div>
                        <div class="info-content">
                            Meal Type: {{ $bookingDetails['mealSpecificType']['specificMealType'] ?? 'N/A' }}<br>
                            Meal Session: {{ $bookingDetails['mealType'] ?? 'N/A' }}<br>
                            
                        </div>
                    </div>
                </div>
                
                <div class="booking-details">
                    <div class="detail-card">
                        <div class="detail-label">Booking ID</div>
                        <div class="detail-value">{{ $display_id }}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Date</div>
                        <div class="detail-value">{{ $check_in_date ?? 'N/A' }}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Time</div>
                        <div class="detail-value">{{ $bookingDetails['visitTime'] ?? 'TBD' }}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Guests</div>
                        <div class="detail-value">{{ ($bookingDetails['adultCount'] ?? 0) }} Adults + {{ ($bookingDetails['childCount'] ?? 0) }} Children</div>
                    </div>
                </div>
            </div>
            
            <div class="voucher-footer">
                <div class="coupon-code">COUPON CODE: {{ $coupon_code }}</div>
                <div class="validity">
                    Valid for: {{ $bookingDetails['check_in_date'] ?? 'N/A' }} | {{ $dmc_company ?? 'Your Vacation Singapore Pte Ltd' }}<br>
                    Please present this coupon at the restaurant | Generated on {{ date('Y-m-d H:i:s') }}
                </div>
            </div>
        </div>
    </div>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    // Auto-capture disabled - now handled by iframe capture in booking list
    // This prevents duplicate requests when the voucher is rendered in iframe
    
    // Function to capture voucher and store in database (kept for manual use if needed)
    function captureAndStoreVoucher() {
        const voucherElement = document.querySelector('.voucher');
        
        if (!voucherElement) {
            console.error('Voucher element not found');
            return;
        }
        
        const options = {
            scale: 2,
            useCORS: true,
            backgroundColor: null,
            width: 600,
            height: 300,
            logging: false
        };
        
        html2canvas(voucherElement, options).then(canvas => {
            canvas.toBlob(function(blob) {
                const reader = new FileReader();
                reader.onloadend = function() {
                    const base64data = reader.result;
                    
                    // Store in database via AJAX
                    fetch("{{ route('generate.restaurant.coupon') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            booking_id: '{{ $booking_id ?? "" }}',
                            tour_id: '{{ $tour_id ?? "" }}',
                            action: 'store_image',
                            image_data: base64data
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('✅ Voucher image stored successfully in database');
                            showMessage('Voucher image saved to database successfully!', 'success');
                        } else {
                            console.error('❌ Failed to store voucher image:', data.message);
                            showMessage('Failed to save image to database', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('❌ Error storing voucher image:', error);
                        showMessage('Error saving image to database', 'error');
                    });
                };
                reader.readAsDataURL(blob);
            }, 'image/png');
        }).catch(error => {
            console.error('Error capturing voucher:', error);
            showMessage('Error capturing voucher image', 'error');
        });
    }

    // Function to download the voucher image
    function downloadVoucherImage() {
        const voucherElement = document.querySelector('.voucher');
        
        if (!voucherElement) {
            console.error('Voucher element not found');
            return;
        }
        
        showMessage('Generating download...', 'info');
        
        const options = {
            scale: 2,
            useCORS: true,
            backgroundColor: null,
            width: 600,
            height: 300,
            logging: false
        };
        
        html2canvas(voucherElement, options).then(canvas => {
            const link = document.createElement('a');
            link.download = 'restaurant_voucher_{{ $display_id ?? "voucher" }}.png';
            link.href = canvas.toDataURL();
            link.click();
            showMessage('Download started!', 'success');
        }).catch(error => {
            console.error('Error downloading voucher:', error);
            showMessage('Error downloading voucher', 'error');
        });
    }

    // Function to show messages
    function showMessage(message, type) {
        const messageDiv = document.createElement('div');
        messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            color: white;
            font-family: Arial, sans-serif;
            font-size: 14px;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        `;
        messageDiv.textContent = message;
        document.body.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.remove();
        }, 5000);
    }

    // Add download button to the page
    document.addEventListener('DOMContentLoaded', function() {
        const downloadBtn = document.createElement('button');
        downloadBtn.textContent = 'Download as Image';
        downloadBtn.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 9999;
        `;
        downloadBtn.onclick = downloadVoucherImage;
        document.body.appendChild(downloadBtn);
    });
</script>
</html> 