<?php

namespace ProClean\Quotation\Services;

use ProClean\Quotation\Admin\Settings;

/**
 * Webhook Manager Service
 * 
 * Handles webhook delivery to external systems with retry logic and signature verification
 * 
 * @package ProClean\Quotation\Services
 * @since 1.0.0
 */
class WebhookManager {
    
    /**
     * Manager instance
     * 
     * @var WebhookManager
     */
    private static $instance = null;
    
    /**
     * Webhook events
     * 
     * @var array
     */
    private const WEBHOOK_EVENTS = [
        'quote.submitted',
        'quote.accepted',
        'quote.rejected',
        'booking.created',
        'booking.confirmed',
        'booking.completed',
        'booking.cancelled',
        'payment.received'
    ];
    
    /**
     * Maximum retry attempts
     * 
     * @var int
     */
    private const MAX_RETRIES = 3;
    
    /**
     * Retry delay in seconds (exponential backoff)
     * 
     * @var int
     */
    private const BASE_RETRY_DELAY = 60;
    
    /**
     * Get manager instance
     * 
     * @return WebhookManager
     */
    public static function getInstance(): WebhookManager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->initHooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function initHooks(): void {
        // Quote events
        add_action('pcq_quote_created', [$this, 'handleQuoteSubmitted'], 10, 2);
        add_action('pcq_quote_accepted', [$this, 'handleQuoteAccepted'], 10, 1);
        add_action('pcq_quote_rejected', [$this, 'handleQuoteRejected'], 10, 1);
        
        // Booking events
        add_action('pcq_booking_created', [$this, 'handleBookingCreated'], 10, 2);
        add_action('pcq_booking_confirmed', [$this, 'handleBookingConfirmed'], 10, 1);
        add_action('pcq_booking_completed', [$this, 'handleBookingCompleted'], 10, 1);
        add_action('pcq_booking_cancelled', [$this, 'handleBookingCancelled'], 10, 2);
        
        // Payment events
        add_action('pcq_payment_received', [$this, 'handlePaymentReceived'], 10, 2);
        
        // Scheduled webhook retry
        add_action('pcq_retry_webhook', [$this, 'retryWebhook'], 10, 1);
    }
    
    /**
     * Handle quote submitted event
     * 
     * @param int $quote_id Quote ID
     * @param array $data Quote data
     */
    public function handleQuoteSubmitted(int $quote_id, array $data): void {
        $this->triggerWebhook('quote.submitted', [
            'quote_id' => $quote_id,
            'quote_number' => $data['quote_number'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'service_type' => $data['service_type'] ?? null,
            'total_price' => $data['total_price'] ?? null,
            'created_at' => $data['created_at'] ?? date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Handle quote accepted event
     * 
     * @param int $quote_id Quote ID
     */
    public function handleQuoteAccepted(int $quote_id): void {
        $this->triggerWebhook('quote.accepted', [
            'quote_id' => $quote_id,
            'accepted_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Handle quote rejected event
     * 
     * @param int $quote_id Quote ID
     */
    public function handleQuoteRejected(int $quote_id): void {
        $this->triggerWebhook('quote.rejected', [
            'quote_id' => $quote_id,
            'rejected_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Handle booking created event
     * 
     * @param int $booking_id Booking ID
     * @param array $data Booking data
     */
    public function handleBookingCreated(int $booking_id, array $data): void {
        $this->triggerWebhook('booking.created', [
            'booking_id' => $booking_id,
            'booking_number' => $data['booking_number'] ?? null,
            'quote_id' => $data['quote_id'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'service_type' => $data['service_type'] ?? null,
            'service_date' => $data['service_date'] ?? null,
            'service_time_start' => $data['service_time_start'] ?? null,
            'service_time_end' => $data['service_time_end'] ?? null,
            'total_amount' => $data['total_amount'] ?? null,
            'booking_status' => $data['booking_status'] ?? 'pending',
            'created_at' => $data['created_at'] ?? date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Handle booking confirmed event
     * 
     * @param int $booking_id Booking ID
     */
    public function handleBookingConfirmed(int $booking_id): void {
        $this->triggerWebhook('booking.confirmed', [
            'booking_id' => $booking_id,
            'confirmed_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Handle booking completed event
     * 
     * @param int $booking_id Booking ID
     */
    public function handleBookingCompleted(int $booking_id): void {
        $this->triggerWebhook('booking.completed', [
            'booking_id' => $booking_id,
            'completed_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Handle booking cancelled event
     * 
     * @param int $booking_id Booking ID
     * @param string $reason Cancellation reason
     */
    public function handleBookingCancelled(int $booking_id, string $reason = ''): void {
        $this->triggerWebhook('booking.cancelled', [
            'booking_id' => $booking_id,
            'reason' => $reason,
            'cancelled_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Handle payment received event
     * 
     * @param int $booking_id Booking ID
     * @param array $payment_data Payment data
     */
    public function handlePaymentReceived(int $booking_id, array $payment_data): void {
        $this->triggerWebhook('payment.received', [
            'booking_id' => $booking_id,
            'amount' => $payment_data['amount'] ?? null,
            'payment_method' => $payment_data['payment_method'] ?? null,
            'transaction_id' => $payment_data['transaction_id'] ?? null,
            'payment_status' => $payment_data['status'] ?? 'completed',
            'received_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Trigger webhook delivery
     * 
     * @param string $event Event name
     * @param array $payload Event payload
     */
    public function triggerWebhook(string $event, array $payload): void {
        // Check if webhooks are enabled
        if (!Settings::get('webhooks_enabled', false)) {
            return;
        }
        
        // Get webhook URLs for this event
        $webhooks = $this->getWebhooksForEvent($event);
        
        if (empty($webhooks)) {
            return;
        }
        
        // Add metadata to payload
        $full_payload = [
            'event' => $event,
            'timestamp' => time(),
            'data' => $payload
        ];
        
        // Deliver to each webhook URL
        foreach ($webhooks as $webhook) {
            $this->deliverWebhook($webhook, $full_payload);
        }
    }
    
    /**
     * Get webhook URLs configured for an event
     * 
     * @param string $event Event name
     * @return array Webhook configurations
     */
    private function getWebhooksForEvent(string $event): array {
        $all_webhooks = Settings::get('webhooks', []);
        
        if (empty($all_webhooks)) {
            return [];
        }
        
        $matching_webhooks = [];
        
        foreach ($all_webhooks as $webhook) {
            // Check if webhook is active
            if (empty($webhook['active'])) {
                continue;
            }
            
            // Check if webhook is configured for this event
            if (empty($webhook['events']) || !in_array($event, $webhook['events'])) {
                // Check for wildcard (*) event listener
                if (empty($webhook['events']) || !in_array('*', $webhook['events'])) {
                    continue;
                }
            }
            
            $matching_webhooks[] = $webhook;
        }
        
        return $matching_webhooks;
    }
    
    /**
     * Deliver webhook to URL
     * 
     * @param array $webhook Webhook configuration
     * @param array $payload Event payload
     */
    private function deliverWebhook(array $webhook, array $payload): void {
        $url = $webhook['url'] ?? '';
        
        if (empty($url)) {
            return;
        }
        
        // Generate signature
        $signature = $this->generateSignature($payload, $webhook['secret'] ?? '');
        
        // Prepare headers
        $headers = [
            'Content-Type' => 'application/json',
            'X-PCQ-Signature' => $signature,
            'X-PCQ-Event' => $payload['event'],
            'X-PCQ-Timestamp' => $payload['timestamp'],
            'User-Agent' => 'ProCleanQuotation-Webhook/1.0'
        ];
        
        // Add custom headers if configured
        if (!empty($webhook['headers'])) {
            foreach ($webhook['headers'] as $key => $value) {
                $headers[$key] = $value;
            }
        }
        
        // Prepare request arguments
        $args = [
            'method' => 'POST',
            'headers' => $headers,
            'body' => wp_json_encode($payload),
            'timeout' => 30,
            'blocking' => false, // Non-blocking for better performance
            'sslverify' => !empty($webhook['verify_ssl'])
        ];
        
        // Send webhook
        $response = wp_remote_post($url, $args);
        
        // Log webhook delivery
        $this->logWebhookDelivery([
            'webhook_id' => $webhook['id'] ?? 0,
            'url' => $url,
            'event' => $payload['event'],
            'payload' => $payload,
            'response' => $response,
            'attempt' => 1,
            'status' => is_wp_error($response) ? 'failed' : 'success'
        ]);
        
        // Schedule retry if failed
        if (is_wp_error($response)) {
            $this->scheduleRetry($webhook, $payload, 1);
        }
    }
    
    /**
     * Retry webhook delivery
     * 
     * @param array $retry_data Retry data containing webhook, payload, and attempt number
     */
    public function retryWebhook(array $retry_data): void {
        $webhook = $retry_data['webhook'];
        $payload = $retry_data['payload'];
        $attempt = $retry_data['attempt'];
        
        if ($attempt > self::MAX_RETRIES) {
            $this->logWebhookDelivery([
                'webhook_id' => $webhook['id'] ?? 0,
                'url' => $webhook['url'],
                'event' => $payload['event'],
                'payload' => $payload,
                'attempt' => $attempt,
                'status' => 'max_retries_exceeded'
            ]);
            return;
        }
        
        // Generate signature
        $signature = $this->generateSignature($payload, $webhook['secret'] ?? '');
        
        // Prepare headers
        $headers = [
            'Content-Type' => 'application/json',
            'X-PCQ-Signature' => $signature,
            'X-PCQ-Event' => $payload['event'],
            'X-PCQ-Timestamp' => $payload['timestamp'],
            'X-PCQ-Retry-Attempt' => $attempt,
            'User-Agent' => 'ProCleanQuotation-Webhook/1.0'
        ];
        
        // Prepare request
        $args = [
            'method' => 'POST',
            'headers' => $headers,
            'body' => wp_json_encode($payload),
            'timeout' => 30,
            'blocking' => true,
            'sslverify' => !empty($webhook['verify_ssl'])
        ];
        
        // Send webhook
        $response = wp_remote_post($webhook['url'], $args);
        
        // Log delivery attempt
        $this->logWebhookDelivery([
            'webhook_id' => $webhook['id'] ?? 0,
            'url' => $webhook['url'],
            'event' => $payload['event'],
            'payload' => $payload,
            'response' => $response,
            'attempt' => $attempt,
            'status' => is_wp_error($response) ? 'retry_failed' : 'retry_success'
        ]);
        
        // Schedule next retry if still failing
        if (is_wp_error($response) && $attempt < self::MAX_RETRIES) {
            $this->scheduleRetry($webhook, $payload, $attempt + 1);
        }
    }
    
    /**
     * Schedule webhook retry with exponential backoff
     * 
     * @param array $webhook Webhook configuration
     * @param array $payload Event payload
     * @param int $attempt Current attempt number
     */
    private function scheduleRetry(array $webhook, array $payload, int $attempt): void {
        // Calculate delay with exponential backoff: 60s, 180s (3m), 540s (9m)
        $delay = self::BASE_RETRY_DELAY * pow(3, $attempt - 1);
        
        // Schedule the retry
        wp_schedule_single_event(
            time() + $delay,
            'pcq_retry_webhook',
            [[
                'webhook' => $webhook,
                'payload' => $payload,
                'attempt' => $attempt
            ]]
        );
    }
    
    /**
     * Generate HMAC signature for webhook payload
     * 
     * @param array $payload Event payload
     * @param string $secret Webhook secret key
     * @return string HMAC signature
     */
    private function generateSignature(array $payload, string $secret): string {
        if (empty($secret)) {
            return '';
        }
        
        $payload_json = wp_json_encode($payload);
        return hash_hmac('sha256', $payload_json, $secret);
    }
    
    /**
     * Verify webhook signature
     * 
     * @param string $payload_json JSON payload
     * @param string $signature Provided signature
     * @param string $secret Webhook secret key
     * @return bool True if signature is valid
     */
    public function verifySignature(string $payload_json, string $signature, string $secret): bool {
        if (empty($secret) || empty($signature)) {
            return false;
        }
        
        $expected_signature = hash_hmac('sha256', $payload_json, $secret);
        return hash_equals($expected_signature, $signature);
    }
    
    /**
     * Log webhook delivery attempt
     * 
     * @param array $log_data Log data
     */
    private function logWebhookDelivery(array $log_data): void {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_webhook_logs';
        
        // Prepare log entry
        $log_entry = [
            'webhook_id' => $log_data['webhook_id'] ?? 0,
            'url' => $log_data['url'] ?? '',
            'event' => $log_data['event'] ?? '',
            'payload' => wp_json_encode($log_data['payload'] ?? []),
            'attempt' => $log_data['attempt'] ?? 1,
            'status' => $log_data['status'] ?? 'unknown',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Add response data if available
        if (isset($log_data['response'])) {
            if (is_wp_error($log_data['response'])) {
                $log_entry['response_code'] = 0;
                $log_entry['response_body'] = $log_data['response']->get_error_message();
            } else {
                $log_entry['response_code'] = wp_remote_retrieve_response_code($log_data['response']);
                $log_entry['response_body'] = wp_remote_retrieve_body($log_data['response']);
            }
        }
        
        // Insert log (suppress errors if table doesn't exist yet)
        @$wpdb->insert($table, $log_entry);
    }
    
    /**
     * Get webhook delivery logs
     * 
     * @param array $filters Filter criteria
     * @param int $limit Maximum number of logs to return
     * @return array Webhook logs
     */
    public function getWebhookLogs(array $filters = [], int $limit = 100): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_webhook_logs';
        
        $where_clauses = [];
        $where_values = [];
        
        if (!empty($filters['webhook_id'])) {
            $where_clauses[] = 'webhook_id = %d';
            $where_values[] = $filters['webhook_id'];
        }
        
        if (!empty($filters['event'])) {
            $where_clauses[] = 'event = %s';
            $where_values[] = $filters['event'];
        }
        
        if (!empty($filters['status'])) {
            $where_clauses[] = 'status = %s';
            $where_values[] = $filters['status'];
        }
        
        $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
        
        $sql = "SELECT * FROM {$table} {$where_sql} ORDER BY created_at DESC LIMIT %d";
        $where_values[] = $limit;
        
        $query = $wpdb->prepare($sql, $where_values);
        return $wpdb->get_results($query, ARRAY_A) ?: [];
    }
    
    /**
     * Get webhook delivery statistics
     * 
     * @param string|null $webhook_id Specific webhook ID or null for all
     * @return array Statistics
     */
    public function getWebhookStats(?string $webhook_id = null): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_webhook_logs';
        
        $where = $webhook_id ? $wpdb->prepare('WHERE webhook_id = %d', $webhook_id) : '';
        
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_deliveries,
                SUM(CASE WHEN status IN ('success', 'retry_success') THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status IN ('failed', 'retry_failed') THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'max_retries_exceeded' THEN 1 ELSE 0 END) as max_retries,
                AVG(CASE WHEN status IN ('success', 'retry_success') THEN attempt ELSE NULL END) as avg_attempts
            FROM {$table}
            {$where}
        ", ARRAY_A);
        
        return $stats ?: [
            'total_deliveries' => 0,
            'successful' => 0,
            'failed' => 0,
            'max_retries' => 0,
            'avg_attempts' => 0
        ];
    }
    
    /**
     * Get supported webhook events
     * 
     * @return array List of webhook events
     */
    public static function getSupportedEvents(): array {
        return self::WEBHOOK_EVENTS;
    }
    
    /**
     * Test webhook delivery
     * 
     * @param string $url Webhook URL
     * @param string $secret Webhook secret
     * @return array Test result
     */
    public function testWebhook(string $url, string $secret = ''): array {
        $test_payload = [
            'event' => 'test.ping',
            'timestamp' => time(),
            'data' => [
                'message' => 'This is a test webhook from Pro Clean Quotation',
                'plugin_version' => PCQ_VERSION,
                'site_url' => get_site_url()
            ]
        ];
        
        $signature = $this->generateSignature($test_payload, $secret);
        
        $headers = [
            'Content-Type' => 'application/json',
            'X-PCQ-Signature' => $signature,
            'X-PCQ-Event' => 'test.ping',
            'X-PCQ-Timestamp' => $test_payload['timestamp'],
            'User-Agent' => 'ProCleanQuotation-Webhook/1.0'
        ];
        
        $args = [
            'method' => 'POST',
            'headers' => $headers,
            'body' => wp_json_encode($test_payload),
            'timeout' => 10,
            'blocking' => true,
            'sslverify' => true
        ];
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message(),
                'code' => 0
            ];
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        return [
            'success' => $response_code >= 200 && $response_code < 300,
            'message' => 'Webhook responded with status ' . $response_code,
            'code' => $response_code,
            'body' => $response_body
        ];
    }
}
