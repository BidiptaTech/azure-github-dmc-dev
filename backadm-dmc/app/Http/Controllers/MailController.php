<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\DmcMail;
use Illuminate\Support\Facades\Log;

class MailController extends Controller
{
    private $mailTypes = [
        'booking_confirmation' => 'Booking Confirmation',
        'booking_reminder' => 'Booking Reminder',
        'booking_cancellation' => 'Booking Cancellation',
        'payment_confirmation' => 'Payment Confirmation',
        'enquiry_response' => 'Enquiry Response',
        'welcome_email' => 'Welcome Email',
        'job_assignment' => 'Job Assignment',
        'feedback_request' => 'Feedback Request',
        'tour_itinerary' => 'Tour Itinerary'
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = EmailTemplate::orderBy('created_at', 'desc')->first();


        return view('mails.index', compact('templates'))
                ->with('success', 'Email sent successfully! Check your inbox or spam folder.');

        try {

            // Prepare dynamic data for the booking confirmation email
            $data = [
                "booking_id" => "BK-" . rand(10000, 99999),
                "customer_name" => "Mr. Kousik Alam",
                "type" => "Hotel Booking",
                "booking_date" => date('Y-m-d'),
                "check_in_date" => date('Y-m-d', strtotime('+7 days')),
                "check_out_date" => date('Y-m-d', strtotime('+10 days')),
                "location" => "Paris, France",
                "guests" => "2 Adults, 1 Child",
                "reference_number" => "REF-" . rand(1000, 9999),
                "total_price" => 1250.00,
                "payment_status" => "Paid"
            ];
            
            // Get company settings for the email
            $logoSetting = \App\Helpers\CommonHelper::masterSettingsName('logo');
            $nameSetting = \App\Helpers\CommonHelper::masterSettingsName('name');
            
            // Add company info to the data array
            $companyData = [
                "company" => [
                    "companyName" => $nameSetting['master_value'] ?? config('app.name'),
                    "logo" => $logoSetting['master_value'] ?? asset('images/logo.png')
                ]
            ];
            
            // Add mail settings for the template
            $mailSettings = (object)[
                "support_email" => "support@yourdomain.com",
                "support_phone" => "+1 (555) 123-4567",
                "facebook_url" => "https://facebook.com/yourcompany",
                "twitter_url" => "https://twitter.com/yourcompany",
                "instagram_url" => "https://instagram.com/yourcompany",
                "linkedin_url" => "https://linkedin.com/company/yourcompany"
            ];
            
            // Merge all data
            $viewData = array_merge($data, $companyData);
            $viewData['mail_settings'] = $mailSettings;
            
            // Render the email template
            $html = view('mails.booking_confirmation', $viewData)->render();
            
            // Extract the entire style tag content
            preg_match('/<style>(.*?)<\/style>/s', $html, $styleMatches);
            $styles = !empty($styleMatches[0]) ? $styleMatches[0] : '';
            
            // Extract the email-container div with all its contents
            preg_match('/<div class="email-container">(.*?)<\/div>\s*$/s', $html, $matches);
            if (!empty($matches[0])) {
                $extractedHtml = $matches[0];
                
                // Add minimal HTML structure with the extracted styles
                $emailHtml = '<!DOCTYPE html><html><head><title>Booking Details</title>' . $styles . '</head><body>' . $extractedHtml . '</body></html>';
                
                // Send just this part
                Mail::to("kousikalam786@gmail.com")->send(new DmcMail($emailHtml, "Your Booking Details"));
                return view('mails.index', compact('templates'))
                ->with('success', 'Email sent successfully! Check your inbox or spam folder.');
            } else {
                // Handle case where the div is not found
                Log::error("Email container div not found in email template");
                return response()->json(['error' => 'Email template structure is invalid'], 500);
            }
            // Send the email
            $recipientEmail = "saurabh.coactive@gmail.com";
            $subject = "Your Booking Confirmation #" . $data['booking_id'];
            
            Mail::to($recipientEmail)->send(new DmcMail($html, $subject));
            
            return view('mails.index', compact('templates'))
                ->with('success', 'Email sent successfully! Check your inbox or spam folder.');
        } catch (\Exception $e) {
            \Log::error('Email sending failed: ' . $e->getMessage());
            return view('mails.index', compact('templates'))
                ->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    public function syncTemplates()
    {
        foreach ($this->mailTypes as $type => $name) {
            $this->processTemplate($type, $name);
        }

        return redirect()->route('mail.index')
            ->with('success', 'All email templates have been synchronized');
    }

    private function processTemplate($type, $name)
    {
        $viewPath = resource_path("views/mails/{$type}.blade.php");
        
        if (!File::exists($viewPath)) {
            return;
        }

        $content = File::get($viewPath);
        
        // Extract variables from the template
        $variables = $this->extractVariables($content);

        // Get or create template
        $template = EmailTemplate::firstOrNew(['mail_type' => $type]);

        $mail_max_id = $lastMail->email_temp_id ?? 0;
        $mailId = CommonHelper::createId($mail_max_id);
        while (EmailTemplate::where('email_temp_id', $mailId)->exists()) {
            $mailId = CommonHelper::createId($mailId);
        }
        
        $template->fill([
            'email_temp_id' => $mailId,
            'name' => $name,
            'subject' => $this->getDefaultSubject($type),
            'content' => $content,
            'variables' => $variables,
            'category' => $this->getCategory($type),
            'is_active' => true
        ])->save();
    }

    private function extractVariables($content)
    {
        $variables = [];
        
        // Match {{ variable }} pattern
        preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);
        if (!empty($matches[1])) {
            $variables = array_merge($variables, $matches[1]);
        }

        // Match {{ $variable }} pattern
        preg_match_all('/\{\{\s*\$([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);
        if (!empty($matches[1])) {
            $variables = array_merge($variables, $matches[1]);
        }

        // Match {{ $object->property }} pattern
        preg_match_all('/\{\{\s*\$([a-zA-Z0-9_]+)->([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $index => $object) {
                $variables[] = $matches[2][$index];
            }
        }

        // Match {{ $mail_settings->property }} pattern
        preg_match_all('/\{\{\s*\$mail_settings->([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);
        if (!empty($matches[1])) {
            $variables = array_merge($variables, $matches[1]);
        }

        // Match {{ $booking->property }} pattern
        preg_match_all('/\{\{\s*\$booking->([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);
        if (!empty($matches[1])) {
            $variables = array_merge($variables, $matches[1]);
        }

        // Match {{ $enquiry->property }} pattern
        preg_match_all('/\{\{\s*\$enquiry->([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);
        if (!empty($matches[1])) {
            $variables = array_merge($variables, $matches[1]);
        }

        // Match {{ $assignment->property }} pattern
        preg_match_all('/\{\{\s*\$assignment->([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);
        if (!empty($matches[1])) {
            $variables = array_merge($variables, $matches[1]);
        }

        // Match {{ $payment->property }} pattern
        preg_match_all('/\{\{\s*\$payment->([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);
        if (!empty($matches[1])) {
            $variables = array_merge($variables, $matches[1]);
        }

        // Match {{ $feedback->property }} pattern
        preg_match_all('/\{\{\s*\$feedback->([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);
        if (!empty($matches[1])) {
            $variables = array_merge($variables, $matches[1]);
        }

        // Match {{ $tour->property }} pattern
        preg_match_all('/\{\{\s*\$tour->([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);
        if (!empty($matches[1])) {
            $variables = array_merge($variables, $matches[1]);
        }

        // Match {{ $user->property }} pattern
        preg_match_all('/\{\{\s*\$user->([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);
        if (!empty($matches[1])) {
            $variables = array_merge($variables, $matches[1]);
        }

        // Match {{ $companyName }} pattern
        preg_match_all('/\{\{\s*\$companyName\s*\}\}/', $content, $matches);
        if (!empty($matches[0])) {
            $variables[] = 'companyName';
        }

        // Match {{ $logo }} pattern
        preg_match_all('/\{\{\s*\$logo\s*\}\}/', $content, $matches);
        if (!empty($matches[0])) {
            $variables[] = 'logo';
        }

        // Match {{ $support_email }} pattern
        preg_match_all('/\{\{\s*\$support_email\s*\}\}/', $content, $matches);
        if (!empty($matches[0])) {
            $variables[] = 'support_email';
        }

        // Match {{ $support_phone }} pattern
        preg_match_all('/\{\{\s*\$support_phone\s*\}\}/', $content, $matches);
        if (!empty($matches[0])) {
            $variables[] = 'support_phone';
        }

        // Match {{ $support_message }} pattern
        preg_match_all('/\{\{\s*\$support_message\s*\}\}/', $content, $matches);
        if (!empty($matches[0])) {
            $variables[] = 'support_message';
        }

        // Match {{ $booking_url }} pattern
        preg_match_all('/\{\{\s*\$booking_url\s*\}\}/', $content, $matches);
        if (!empty($matches[0])) {
            $variables[] = 'booking_url';
        }

        // Match {{ $privacy_policy_url }} pattern
        preg_match_all('/\{\{\s*\$privacy_policy_url\s*\}\}/', $content, $matches);
        if (!empty($matches[0])) {
            $variables[] = 'privacy_policy_url';
        }

        // Match {{ $terms_url }} pattern
        preg_match_all('/\{\{\s*\$terms_url\s*\}\}/', $content, $matches);
        if (!empty($matches[0])) {
            $variables[] = 'terms_url';
        }

        // Match {{ $unsubscribe_url }} pattern
        preg_match_all('/\{\{\s*\$unsubscribe_url\s*\}\}/', $content, $matches);
        if (!empty($matches[0])) {
            $variables[] = 'unsubscribe_url';
        }

        // Match {{ $facebook_url }} pattern
        preg_match_all('/\{\{\s*\$facebook_url\s*\}\}/', $content, $matches);
        if (!empty($matches[0])) {
            $variables[] = 'facebook_url';
        }

        // Match {{ $twitter_url }} pattern
        preg_match_all('/\{\{\s*\$twitter_url\s*\}\}/', $content, $matches);
        if (!empty($matches[0])) {
            $variables[] = 'twitter_url';
        }

        // Match {{ $instagram_url }} pattern
        preg_match_all('/\{\{\s*\$instagram_url\s*\}\}/', $content, $matches);
        if (!empty($matches[0])) {
            $variables[] = 'instagram_url';
        }

        // Match {{ $linkedin_url }} pattern
        preg_match_all('/\{\{\s*\$linkedin_url\s*\}\}/', $content, $matches);
        if (!empty($matches[0])) {
            $variables[] = 'linkedin_url';
        }

        // Remove duplicates and sort
        $variables = array_unique($variables);
        sort($variables);

        return $variables;
    }

    private function getDefaultSubject($type)
    {
        $subjects = [
            'booking_confirmation' => 'Booking Confirmed!',
            'booking_reminder' => 'Upcoming Booking Reminder',
            'booking_cancellation' => 'Booking Cancellation Confirmation',
            'payment_confirmation' => 'Payment Confirmation',
            'enquiry_response' => 'Response to Your Enquiry',
            'welcome_email' => 'Welcome to ' . config('app.name'),
            'job_assignment' => 'New Job Assignment',
            'feedback_request' => 'We Value Your Feedback',
            'tour_itinerary' => 'Your Tour Itinerary'
        ];

        return $subjects[$type] ?? Str::title(str_replace('_', ' ', $type));
    }

    private function getCategory($type)
    {
        $categories = [
            'booking' => ['booking_confirmation', 'booking_reminder', 'booking_cancellation'],
            'payment' => ['payment_confirmation'],
            'enquiry' => ['enquiry_response'],
            'welcome' => ['welcome_email'],
            'job' => ['job_assignment'],
            'feedback' => ['feedback_request'],
            'tour' => ['tour_itinerary']
        ];

        foreach ($categories as $category => $types) {
            if (in_array($type, $types)) {
                return $category;
            }
        }

        return 'general';
    }

    public function previewTemplate($type)
    {
        $template = EmailTemplate::where('mail_type', $type)->firstOrFail();
        $variables = $this->getSampleData($type);
        
        // Replace variables in template content
        $content = $template->content;
        foreach ($variables as $key => $value) {
            $content = str_replace('{{ ' . $key . ' }}', $value, $content);
            $content = str_replace('{{ $' . $key . ' }}', $value, $content);
        }

        return view('mails.templates.preview', compact('template', 'content', 'variables'));
    }

    private function getSampleData($type)
    {
        $data = [
            'booking_confirmation' => [
                'booking_id' => 'BOK-12345',
                'customer_name' => 'John Doe',
                'type' => 'Hotel Booking',
                'booking_date' => date('Y-m-d'),
                'check_in_date' => date('Y-m-d', strtotime('+7 days')),
                'check_out_date' => date('Y-m-d', strtotime('+10 days')),
                'location' => 'Singapore',
                'guests' => 2,
                'reference_number' => 'REF-5678',
                'total_price' => 999.99,
                'payment_status' => 'Paid'
            ],
            'booking_reminder' => [
                'booking_id' => 'BOK-12345',
                'customer_name' => 'John Doe',
                'tour_name' => 'Singapore Heritage Tour',
                'booking_date' => date('Y-m-d', strtotime('-14 days')),
                'departure_date' => date('Y-m-d', strtotime('+3 days')),
                'departure_time' => '08:30 AM',
                'meeting_point' => 'Changi Airport Terminal 3'
            ],
            'booking_cancellation' => [
                'booking_id' => 'BOK-12345',
                'customer_name' => 'John Doe',
                'tour_name' => 'Singapore City Explorer',
                'booking_date' => date('Y-m-d', strtotime('-10 days')),
                'cancellation_date' => date('Y-m-d'),
                'refund_amount' => 750.00,
                'currency' => 'SGD',
                'cancellation_reason' => 'Customer request',
                'refund_status' => 'Processing'
            ],
            'payment_confirmation' => [
                'payment_id' => 'PAY-12345',
                'customer_name' => 'John Doe',
                'booking_id' => 'BOK-12345',
                'payment_date' => date('Y-m-d'),
                'amount' => 999.99,
                'currency' => 'SGD',
                'payment_method' => 'Credit Card',
                'card_last_four' => '1234',
                'booking_type' => 'Tour Package',
                'status' => 'Paid',
                'transaction_id' => 'TXN123456'
            ]
            // Add other template types as needed
        ];

        return $data[$type] ?? [];
    }

    public function bookingConfirmation()
    {
        // Get or create the template
        $template = EmailTemplate::firstOrNew(['mail_type' => 'booking_confirmation']);

        $mail_max_id = $lastMail->email_temp_id ?? 0;
        $mailId = CommonHelper::createId($mail_max_id);
        while (EmailTemplate::where('email_temp_id', $mailId)->exists()) {
            $mailId = CommonHelper::createId($mailId);
        }
        
        if (!$template->exists) {
            // If template doesn't exist, create it
            $viewPath = resource_path("views/mails/booking_confirmation.blade.php");
            $content = File::get($viewPath);

            $variables = [
                "company" => [
                    "companyName" => "",
                    "logo" => "",
                ],
                "booking" => [
                    "booking_id" => "",
                    "customer_name" => "",
                    "type" => "",
                    "booking_date" => "",
                    "check_in_date" => "",
                    "check_out_date" => "",
                    "location" => "",
                    "guests" => "",
                    "reference_number" => "",
                    "total_price" => "",
                    "payment_status" => "",
                    "arrival_name" => "",
                    "departure_name" => "",
                ],
                "urls" => [
                    "booking_url" => "",
                    "privacy_policy_url" => "",
                    "terms_url" => "",
                    "unsubscribe_url" => "",
                ],
                "date" => [
                    "Y" => "",
                    "l" => "",
                    "jS F Y" => "",
                    "g:i A" => "",
                ],
                "carbon_formats" => [
                    "l" => "",
                    "jS F Y" => "",
                    "g:i A" => "",
                ],
            ];
            

            $template->fill([
                'email_temp_id' => $mailId,
                'name' => 'Booking Confirmation',
                'mail_type' => 'booking_confirmation',
                'subject' => 'Booking Confirmed!',
                'content' => $content,
                'variables' => $variables,
                'category' => 'booking',
                'is_active' => true
            ])->save();
        }

        // Create sample booking data
        $booking = (object)[
            'booking_id' => 'BOK-' . rand(10000, 99999),
            'customer_name' => 'John Doe',
            'type' => 'Hotel Booking',
            'booking_date' => date('Y-m-d'),
            'check_in_date' => date('Y-m-d', strtotime('+7 days')),
            'check_out_date' => date('Y-m-d', strtotime('+10 days')),
            'location' => 'Singapore',
            'guests' => 2,
            'reference_number' => 'REF-' . rand(1000, 9999),
            'total_price' => rand(75000, 150000) / 100,
            'payment_status' => 'Paid'
        ];

        // Get company settings
        $logoSetting = CommonHelper::masterSettingsName('logo');
        $nameSetting = CommonHelper::masterSettingsName('name');
        // $mailSettings = Setting::where('type', 'mail')->get()->pluck('value', 'key');

        // Prepare all variables
        $variables = [
            // Booking details
            'booking_id' => $booking->booking_id,
            'customer_name' => $booking->customer_name,
            'type' => $booking->type,
            'booking_date' => $booking->booking_date,
            'check_in_date' => $booking->check_in_date,
            'check_out_date' => $booking->check_out_date,
            'location' => $booking->location,
            'guests' => $booking->guests,
            'reference_number' => $booking->reference_number,
            'total_price' => $booking->total_price,
            'payment_status' => $booking->payment_status,

            // Company details
            'company_name' => $nameSetting['master_value'] ?? config('app.name'),
            'logo' => $logoSetting['master_value'] ?? '',

            // Contact information
            'support_email' => $mailSettings['support_email'] ?? 'support@example.com',
            'support_phone' => $mailSettings['support_phone'] ?? '+1 (555) 123-4567',
            'support_message' => 'Need help or have questions? Our support team is here to assist you 24/7.',

            // URLs
            'booking_url' => route('bookinglist.index'),
            'privacy_policy_url' => '#',
            'terms_url' => '#',
            'unsubscribe_url' => '#',

            // Social media
            'facebook_url' => $mailSettings['facebook_url'] ?? 'https://facebook.com/yourcompany',
            'twitter_url' => $mailSettings['twitter_url'] ?? 'https://twitter.com/yourcompany',
            'instagram_url' => $mailSettings['instagram_url'] ?? 'https://instagram.com/yourcompany',
            'linkedin_url' => $mailSettings['linkedin_url'] ?? 'https://linkedin.com/company/yourcompany'
        ];

        // Replace variables in template content
        $content = $template->content;
        foreach ($variables as $key => $value) {
            $content = str_replace('{{ ' . $key . ' }}', $value, $content);
            $content = str_replace('{{ $' . $key . ' }}', $value, $content);
        }

        return view('mails.booking_confirmation', array_merge($variables, ['content' => $content]));
    }

    public function jobAssignment()
    {
        // Sample job assignment data
        $assignment = (object)[
            'job_id' => 'JOB-' . rand(10000, 99999),
            'employee_name' => 'Robert Smith',
            'job_type' => 'Tour Guide',
            'tour_name' => 'Singapore City Tour',
            'date' => date('Y-m-d', strtotime('+5 days')),
            'time' => '09:00 AM',
            'meeting_point' => 'Marina Bay Sands Hotel Lobby',
            'guests' => rand(2, 15),
            'duration' => '8 hours',
            'special_instructions' => 'Guest is celebrating a birthday, please prepare accordingly.'
        ];

        return view('mails.job_assignment', compact('assignment'));
    }

    public function enquiryResponse()
    {
        // Sample enquiry response data
        $enquiry = (object)[
            'enquiry_id' => 'ENQ-' . rand(10000, 99999),
            'customer_name' => 'Emma Johnson',
            'enquiry_subject' => 'Tour Package Information',
            'enquiry_date' => date('Y-m-d', strtotime('-2 days')),
            'response' => 'Thank you for your interest in our Singapore Explorer Package. As requested, here are the detailed itinerary options and pricing information.',
            'agent_name' => 'Michael Chen',
            'agent_position' => 'Senior Travel Consultant'
        ];

        return view('mails.enquiry_response', compact('enquiry'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Display the mail settings page.
     */
    public function settings()
    {
        // You can load existing settings from database if you have them
        $settings = (object)[
            'smtp_host' => env('MAIL_HOST', 'smtp.mailtrap.io'),
            'smtp_port' => env('MAIL_PORT', 2525),
            'smtp_encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'smtp_username' => env('MAIL_USERNAME', ''),
            'smtp_password' => env('MAIL_PASSWORD', ''),
            'from_email' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
            'from_name' => env('MAIL_FROM_NAME', config('app.name')),
            'support_email' => 'support@example.com',
            'support_phone' => '+1 (555) 123-4567',
            'facebook_url' => 'https://facebook.com/yourcompany',
            'twitter_url' => 'https://twitter.com/yourcompany',
            'instagram_url' => 'https://instagram.com/yourcompany',
            'linkedin_url' => 'https://linkedin.com/company/yourcompany',
            'footer_text' => '© ' . date('Y') . ' ' . config('app.name') . '. All rights reserved.'
        ];

        return view('mails.settings', compact('settings'));
    }

    /**
     * Save mail settings.
     */
    public function saveSettings(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|numeric',
            'smtp_encryption' => 'required|string|in:tls,ssl,none',
            'smtp_username' => 'required|string|max:255',
            'smtp_password' => 'required|string|max:255',
            'from_email' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
            'support_email' => 'required|email|max:255',
            'support_phone' => 'required|string|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'footer_text' => 'nullable|string',
        ]);

        // Save settings to database or update env file
        // This is a placeholder - you'll need to implement the actual saving logic
        
        return redirect()->route('mail.settings')->with('success', 'Mail settings updated successfully.');
    }

    /**
     * Send a test email.
     */
    public function testEmail(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'email' => 'required|email',
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|numeric',
            'smtp_encryption' => 'required|string',
            'smtp_username' => 'required|string',
            'smtp_password' => 'required|string',
            'from_email' => 'required|email',
            'from_name' => 'required|string',
        ]);

        try {
            // Here you would implement the actual test email sending
            // For now we'll just simulate success
            
            return response()->json(['success' => true, 'message' => 'Test email sent successfully to ' . $request->email]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send test email: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the tour itinerary email template
     */
    public function tourItinerary()
    {
        // Create sample tour data for preview
        $tour = (object)[
            'tour_id' => 'TOUR-' . rand(10000, 99999),
            'customer_name' => 'Sarah Wilson',
            'tour_name' => 'Singapore City Explorer',
            'start_date' => date('Y-m-d', strtotime('+14 days')),
            'end_date' => date('Y-m-d', strtotime('+17 days')),
            'number_of_days' => 4,
            'destination' => 'Singapore',
            'duration' => '4 Days/3 Nights',
            'total_travelers' => 2,
            'accommodation' => 'Luxury Hotel',
            'currency' => 'SGD',
            'total_price' => 1250.00,
            'highlights' => ['Marina Bay Sands', 'Gardens by the Bay', 'Sentosa Island'],
            'guide_name' => 'David Lee',
            'guide_contact' => '+65 9876 5432',
            'itinerary' => [
                [
                    'day' => 1,
                    'title' => 'Arrival & City Tour',
                    'date' => date('Y-m-d', strtotime('+14 days')),
                    'description' => 'Arrive at Singapore Changi Airport. Transfer to hotel and city orientation tour.',
                    'meals' => ['Dinner'],
                    'accommodation' => 'Luxury Hotel Singapore'
                ],
                [
                    'day' => 2,
                    'title' => 'Gardens & Marina Bay',
                    'date' => date('Y-m-d', strtotime('+15 days')),
                    'description' => 'Visit Gardens by the Bay and explore Marina Bay area.',
                    'meals' => ['Breakfast', 'Lunch'],
                    'accommodation' => 'Luxury Hotel Singapore'
                ]
            ],
            'inclusions' => [
                'Accommodation in specified hotels',
                'Meals as mentioned in the itinerary',
                'All transfers and sightseeing',
                'English speaking guide'
            ],
            'exclusions' => [
                'International airfare',
                'Personal expenses',
                'Optional tours',
                'Travel insurance'
            ],
            'contact_person' => 'John Smith',
            'contact_email' => 'support@example.com',
            'contact_phone' => '+65 9876 5432'
        ];
        
        return view('mails.tour_itinerary', compact('tour'));
    }

    /**
     * Display the booking reminder email template
     */
    public function bookingReminder()
    {
        // Sample booking reminder data
        $booking = (object)[
            'booking_id' => 'BOK-' . rand(10000, 99999),
            'customer_name' => 'Alex Thompson',
            'tour_name' => 'Singapore Heritage Tour',
            'booking_date' => date('Y-m-d', strtotime('-14 days')),
            'departure_date' => date('Y-m-d', strtotime('+3 days')),
            'departure_time' => '08:30 AM',
            'meeting_point' => 'Changi Airport Terminal 3 Arrival Hall',
            'important_items' => ['Passport', 'Booking confirmation', 'Comfortable shoes']
        ];
        
        return view('mails.booking_reminder', compact('booking'));
    }

    /**
     * Display the payment confirmation email template
     */
    public function paymentConfirmation()
    {
        // Sample payment data
        $payment = (object)[
            'payment_id' => 'PAY-' . rand(10000, 99999),
            'customer_name' => 'Jennifer Baker',
            'booking_id' => 'BOK-' . rand(10000, 99999),
            'payment_date' => date('Y-m-d'),
            'amount' => rand(50000, 150000) / 100,
            'currency' => 'SGD',
            'payment_method' => 'Credit Card',
            'card_last_four' => rand(1000, 9999),
            'booking_type' => 'Tour Package',
            'status' => 'Paid',
            'transaction_id' => 'TXN' . rand(100000, 999999),
            'booking_details' => 'Singapore City Tour - 2 Adults, 1 Child'
        ];
        
        return view('mails.payment_confirmation', compact('payment'));
    }

    /**
     * Display the welcome email template
     */
    public function welcomeEmail()
    {
        // Sample user data
        $user = (object)[
            'name' => 'Daniel Robinson',
            'email' => 'daniel.robinson@example.com',
            'registration_date' => date('Y-m-d'),
            'verification_link' => 'https://example.com/verify?token=' . md5(time()),
        ];
        
        return view('mails.welcome_email', compact('user'));
    }

    /**
     * Display the booking cancellation email template
     */
    public function bookingCancellation()
    {
        // Sample booking cancellation data
        $booking = (object)[
            'booking_id' => 'BOK-' . rand(10000, 99999),
            'customer_name' => 'David Wilson',
            'tour_name' => 'Singapore City Explorer',
            'booking_date' => date('Y-m-d', strtotime('-10 days')),
            'cancellation_date' => date('Y-m-d'),
            'refund_amount' => rand(30000, 80000) / 100,
            'currency' => 'SGD',
            'cancellation_reason' => 'Customer request',
            'refund_status' => 'Processing'
        ];
        
        return view('mails.booking_cancellation', compact('booking'));
    }

    /**
     * Display the feedback request email template
     */
    public function feedbackRequest()
    {
        // Sample feedback request data
        $feedback = (object)[
            'customer_name' => 'Sarah Johnson',
            'tour_name' => 'Singapore City Explorer',
            'booking_id' => 'BOK-' . rand(10000, 99999),
            'tour_date' => date('Y-m-d', strtotime('-3 days')),
            'survey_link' => 'https://example.com/feedback/' . md5(time()),
            'guide_name' => 'Michael Chen'
        ];
        
        return view('mails.feedback_request', compact('feedback'));
    }
}
