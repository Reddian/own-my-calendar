# Stripe Integration Documentation

## Overview

This document provides detailed information about the Stripe integration implemented in the Own My Calendar application. The integration allows users to subscribe to the Premium plan with either monthly ($9/month) or yearly ($89/year) billing options.

## Configuration

### Environment Variables

The following environment variables need to be set in the `.env` file:

```
STRIPE_PUBLIC_KEY=pk_test_your_stripe_public_key
STRIPE_SECRET_KEY=sk_test_your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_stripe_webhook_secret
STRIPE_PRICE_MONTHLY=price_monthly_id
STRIPE_PRICE_YEARLY=price_yearly_id
```

Replace the placeholder values with your actual Stripe API keys and price IDs.

### Stripe Dashboard Setup

1. Create two price objects in your Stripe dashboard:
   - Monthly subscription: $9/month
   - Yearly subscription: $89/year

2. Set up a webhook endpoint in your Stripe dashboard:
   - URL: `https://your-domain.com/stripe/webhook`
   - Events to listen for:
     - `checkout.session.completed`
     - `customer.subscription.updated`
     - `customer.subscription.deleted`
     - `invoice.payment_failed`

## Implementation Details

### Components

The Stripe integration consists of the following components:

1. **StripeService Class**
   - Location: `app/Services/StripeService.php`
   - Handles all Stripe API interactions
   - Methods for creating checkout sessions, handling webhooks, and managing subscriptions

2. **CheckoutController**
   - Location: `app/Http/Controllers/CheckoutController.php`
   - Manages the checkout process and webhook handling
   - Provides endpoints for creating sessions, handling success, and canceling subscriptions

3. **Subscription Page**
   - Location: `resources/views/subscription.blade.php`
   - Displays subscription options and pricing
   - Integrates with Stripe's JavaScript library for checkout

4. **Settings Page**
   - Location: `resources/views/settings.blade.php`
   - Shows subscription status and allows cancellation
   - Provides feedback on subscription changes

### Routes

The following routes are used for the Stripe integration:

```php
// Checkout routes
Route::post('/checkout/create-session', [CheckoutController::class, 'createCheckoutSession'])->name('checkout.create-session');
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::post('/checkout/cancel-subscription', [CheckoutController::class, 'cancelSubscription'])->name('checkout.cancel-subscription');

// Stripe webhook route
Route::post('/stripe/webhook', [CheckoutController::class, 'handleWebhook'])->name('stripe.webhook');
```

## User Flow

### Subscription Process

1. User visits the subscription page
2. User selects either monthly or yearly billing
3. User clicks "Upgrade Now"
4. Application creates a Stripe checkout session
5. User is redirected to Stripe's hosted checkout page
6. User enters payment information
7. Upon successful payment, user is redirected back to the application
8. Application updates the user's subscription status
9. User receives confirmation of successful subscription

### Cancellation Process

1. User visits the settings page
2. User clicks "Cancel Subscription" in the Subscription tab
3. User confirms cancellation in the modal
4. Application cancels the subscription in Stripe
5. User's subscription remains active until the end of the current billing period
6. User receives confirmation of cancellation

## Testing

To test the Stripe integration:

1. Use Stripe's test mode and test credit cards
2. Test both monthly and yearly subscription flows
3. Test the cancellation process
4. Test webhook handling using Stripe's webhook testing tools

## Troubleshooting

Common issues and solutions:

1. **Checkout session creation fails**
   - Check that your Stripe API keys are correct
   - Verify that the price IDs exist in your Stripe account

2. **Webhook verification fails**
   - Ensure the webhook secret is correctly set in your .env file
   - Check that the webhook URL is accessible from the internet

3. **Subscription not updating after payment**
   - Check the webhook logs for errors
   - Verify that the webhook is properly configured in Stripe

## Security Considerations

1. All sensitive API keys are stored in the .env file and not committed to version control
2. Webhook signatures are verified to prevent unauthorized requests
3. User authentication is required for all subscription-related actions
4. Payment information is handled entirely by Stripe's secure checkout page
