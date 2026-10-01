<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

class StripePaymentController extends Controller
{
    public function stripe(Request $request)
    {
        return view('stripe.stripe');
    }

    public function checkout(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'mode' => 'payment',
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'inr',
                        'product_data' => [
                            'name' => 'Travclick Agent Subscription',
                        ],
                        'unit_amount' => 10000, // ₹100.00
                    ],
                    'quantity' => 1,
                ],
            ],
            'success_url' => route('payment.success', absolute: true)
                .'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancel', absolute: true),
        ]);

        return redirect()->away($session->url);
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        return view('stripe.success', [
            'sessionId' => $sessionId,
        ]);
    }

    public function cancel(Request $request)
    {
        return view('stripe.cancel');
    }

    /**
     * Stripe webhook endpoint.
     * Configure in Stripe Dashboard → Developers → Webhooks:
     * URL: https://your-domain/stripe/webhook
     * Events: checkout.session.completed, payment_intent.succeeded,
     *         payment_intent.payment_failed, charge.refunded, checkout.session.expired
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        if (! $webhookSecret) {
            Log::error('Stripe webhook secret is not configured');

            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (UnexpectedValueException $e) {
            Log::warning('Stripe webhook invalid payload', ['message' => $e->getMessage()]);

            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', ['message' => $e->getMessage()]);

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        Log::info('Stripe webhook received', [
            'type' => $event->type,
            'id' => $event->id,
        ]);

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;

            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;

            case 'charge.refunded':
                $this->handleChargeRefunded($event->data->object);
                break;

            case 'checkout.session.expired':
                $this->handleCheckoutSessionExpired($event->data->object);
                break;

            default:
                Log::info('Stripe webhook unhandled event type', ['type' => $event->type]);
                break;
        }

        return response()->json(['received' => true]);
    }

    protected function handleCheckoutSessionCompleted(object $session): void
    {
        Log::info('Stripe checkout.session.completed', [
            'session_id' => $session->id ?? null,
            'payment_status' => $session->payment_status ?? null,
            'payment_intent' => $session->payment_intent ?? null,
            'customer_email' => $session->customer_details->email ?? ($session->customer_email ?? null),
            'amount_total' => $session->amount_total ?? null,
            'currency' => $session->currency ?? null,
        ]);

        // Update order / subscription status here when business logic is ready.
    }

    protected function handlePaymentIntentSucceeded(object $paymentIntent): void
    {
        Log::info('Stripe payment_intent.succeeded', [
            'payment_intent_id' => $paymentIntent->id ?? null,
            'amount' => $paymentIntent->amount ?? null,
            'currency' => $paymentIntent->currency ?? null,
            'status' => $paymentIntent->status ?? null,
        ]);
    }

    protected function handlePaymentIntentFailed(object $paymentIntent): void
    {
        Log::warning('Stripe payment_intent.payment_failed', [
            'payment_intent_id' => $paymentIntent->id ?? null,
            'status' => $paymentIntent->status ?? null,
            'last_payment_error' => $paymentIntent->last_payment_error->message ?? null,
        ]);
    }

    protected function handleChargeRefunded(object $charge): void
    {
        Log::info('Stripe charge.refunded', [
            'charge_id' => $charge->id ?? null,
            'amount_refunded' => $charge->amount_refunded ?? null,
            'payment_intent' => $charge->payment_intent ?? null,
        ]);
    }

    protected function handleCheckoutSessionExpired(object $session): void
    {
        Log::info('Stripe checkout.session.expired', [
            'session_id' => $session->id ?? null,
        ]);
    }
}
