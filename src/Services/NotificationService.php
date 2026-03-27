<?php

namespace Cartino\Services;

use Cartino\Mail\AbandonedCartReminder;
use Cartino\Mail\BackInStockNotification;
use Cartino\Mail\LowStockAlert;
use Cartino\Mail\Newsletter;
use Cartino\Models\Order;
use Cartino\Models\User;
use Cartino\Notifications\NewOrderNotification;
use Cartino\Notifications\OrderConfirmationNotification;
use Cartino\Notifications\OrderFailureNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Send order confirmation to customer
     */
    public function sendOrderConfirmation(Order $order): void
    {
        try {
            $customer = $order->customer;

            if ($customer && $customer->email) {
                $customer->notify(new OrderConfirmationNotification($order));

                Log::info('Order confirmation sent', [
                    'order_id' => $order->id,
                    'customer_email' => $customer->email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify admins about new order
     */
    public function notifyAdmins(Order $order): void
    {
        try {
            $admins = User::where('can_access_control_panel', true)->where('receive_order_notifications', true)->get();

            Notification::send($admins, new NewOrderNotification($order));

            Log::info('Admin notifications sent', [
                'order_id' => $order->id,
                'admin_count' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify admins', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify about order failure
     */
    public function notifyOrderFailure(Order $order, \Throwable $exception): void
    {
        try {
            $admins = User::where('can_access_control_panel', true)->where('receive_error_notifications', true)->get();

            Notification::send($admins, new OrderFailureNotification($order, $exception));

            Log::info('Order failure notifications sent', [
                'order_id' => $order->id,
                'admin_count' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send failure notifications', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send low stock alerts
     */
    public function sendLowStockAlert($product): void
    {
        try {
            $admins = User::where('can_access_control_panel', true)->where('receive_stock_notifications', true)->get();

            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new LowStockAlert($product));
            }

            Log::info('Low stock alerts sent', [
                'product_id' => $product->id,
                'admin_count' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send low stock alerts', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send abandoned cart reminder
     */
    public function sendAbandonedCartReminder($cart): void
    {
        try {
            if ($cart->customer && $cart->customer->email) {
                Mail::to($cart->customer->email)->send(new AbandonedCartReminder($cart));

                Log::info('Abandoned cart reminder sent', [
                    'cart_id' => $cart->id,
                    'customer_email' => $cart->customer->email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send abandoned cart reminder', [
                'cart_id' => $cart->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send newsletter
     */
    public function sendNewsletter(string $subject, string $content, array $recipients = []): void
    {
        try {
            $subscribers = empty($recipients)
                ? User::where('newsletter_subscription', true)->get()
                : User::whereIn('email', $recipients)->get();

            foreach ($subscribers as $subscriber) {
                Mail::to($subscriber->email)->send(new Newsletter($subject, $content, $subscriber));
            }

            Log::info('Newsletter sent', [
                'subscriber_count' => $subscribers->count(),
                'subject' => $subject,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send newsletter', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send product back in stock notification
     */
    public function sendBackInStockNotification($product, array $subscribers): void
    {
        try {
            foreach ($subscribers as $subscriber) {
                Mail::to($subscriber['email'])->send(new BackInStockNotification($product, $subscriber));
            }

            Log::info('Back in stock notifications sent', [
                'product_id' => $product->id,
                'subscriber_count' => count($subscribers),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send back in stock notifications', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
