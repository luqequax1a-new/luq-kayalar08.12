<?php

namespace Modules\Geliver\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\Order\Entities\Order;

class WebhookController
{
    public function shipmentStatus(Request $request): JsonResponse
    {
        \Log::info('Geliver webhook hit', [
            'ip' => $request->ip(),
            'payload' => $request->all(),
            'raw' => $request->getContent(),
            'headers' => $request->headers->all(),
        ]);
        \Log::info('GELIVER_WEBHOOK_HIT', [
            'path' => $request->path(),
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
        ]);
        $secret = config('services.geliver.webhook_secret') ?: setting('geliver_webhook_secret');
        \Log::info('Geliver webhook secret check', [
            'configured_secret' => $secret ? 'SET' : 'EMPTY',
            'provided' => $request->header('X-Geliver-Secret') ?: $request->query('secret'),
        ]);
        if ($secret) {
            $provided = $request->header('X-Geliver-Secret') ?: $request->query('secret');
            if (!$provided || $provided !== $secret) {
                return response()->json(['message' => 'unauthorized'], 401);
            }
        }

        $payload = $request->json()->all();
        if (empty($payload)) {
            $raw = $request->getContent();
            $decoded = null;
            try {
                $decoded = json_decode($raw, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
            } catch (\Throwable $e) {
            }
            if (!is_array($decoded)) {
                $decoded = json_decode(utf8_encode($raw), true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
            }
            if (!is_array($decoded) && stripos($request->header('Content-Type') ?? '', 'application/x-www-form-urlencoded') !== false) {
                $form = [];
                parse_str($raw, $form);
                if (is_array($form) && !empty($form)) { $decoded = $form; }
            }
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }
        \Log::info('GELIVER_PAYLOAD_DECODED', [
            'keys' => is_array($payload) ? array_keys($payload) : [],
            'has_id' => isset($payload['id']) || isset($payload['shipmentID']) || isset($payload['data']['id']) || isset($payload['data']['shipmentID']) || isset($payload['shipment']['id']) || isset($payload['shipment']['shipmentID']),
            'has_status' => isset($payload['status']) || isset($payload['statusCode']) || isset($payload['data']['status']) || isset($payload['data']['statusCode']) || isset($payload['shipment']['status']) || isset($payload['shipment']['statusCode']),
        ]);
        $shipmentId = $payload['id']
            ?? ($payload['shipmentID'] ?? null)
            ?? ($payload['data']['id'] ?? null)
            ?? ($payload['data']['shipmentID'] ?? null)
            ?? ($payload['shipment']['id'] ?? null)
            ?? ($payload['shipment']['shipmentID'] ?? null);
        $payloadStatus = $payload['status']
            ?? ($payload['statusCode'] ?? null)
            ?? ($payload['data']['status'] ?? null)
            ?? ($payload['data']['statusCode'] ?? null)
            ?? ($payload['shipment']['status'] ?? null)
            ?? ($payload['shipment']['statusCode'] ?? null);
        if (!$shipmentId || !$payloadStatus) {
            return response()->json(['message' => 'invalid payload'], 400);
        }

        $order = Order::where('geliver_shipment_id', $shipmentId)->first();
        if (!$order) {
            $orderNumber = $payload['order']['orderNumber']
                ?? ($payload['data']['order']['orderNumber'] ?? null)
                ?? ($payload['orderNumber'] ?? null)
                ?? ($payload['shipment']['order']['orderNumber'] ?? null);
            if ($orderNumber) {
                $order = Order::where('id', $orderNumber)->first();
            }
        }

        if ($order) {
            \Log::info('Geliver webhook order found', [
                'order_id' => $order->id,
                'shipment_id' => $shipmentId,
                'status' => $payloadStatus,
            ]);
        }
        \Log::info('GELIVER_STATUS_UPDATE_CANDIDATE', [
            'status' => $payloadStatus,
            'shipment_id' => $shipmentId,
            'order_number' => $payload['order']['orderNumber'] ?? ($payload['data']['order']['orderNumber'] ?? ($payload['orderNumber'] ?? null)),
            'order_found' => (bool) $order,
        ]);

        if (!$order) {
            Log::warning('Geliver webhook: order not found', ['shipment_id' => $shipmentId]);
            return response()->json(['message' => 'order not found'], 200);
        }

        $trackingNumber = data_get($payload, 'tracking_number')
            ?? data_get($payload, 'trackingNo')
            ?? data_get($payload, 'trackingNumber')
            ?? data_get($payload, 'shipment.tracking_number')
            ?? data_get($payload, 'shipment.trackingNo')
            ?? data_get($payload, 'data.tracking_number')
            ?? data_get($payload, 'data.trackingNo')
            ?? data_get($payload, 'awb')
            ?? data_get($payload, 'waybillNo');
        $carrierName = data_get($payload, 'carrier')
            ?? data_get($payload, 'carrierName')
            ?? data_get($payload, 'carrier.name')
            ?? data_get($payload, 'shipment.carrier')
            ?? data_get($payload, 'shipment.carrierName')
            ?? data_get($payload, 'shipment.carrier.name')
            ?? data_get($payload, 'data.carrier')
            ?? data_get($payload, 'data.carrierName')
            ?? data_get($payload, 'data.carrier.name');
        $trackingUrl = data_get($payload, 'tracking_url')
            ?? data_get($payload, 'trackingUrl')
            ?? data_get($payload, 'shipment.tracking_url')
            ?? data_get($payload, 'shipment.trackingUrl')
            ?? data_get($payload, 'data.tracking_url')
            ?? data_get($payload, 'data.trackingUrl')
            ?? data_get($payload, 'tracking.link')
            ?? data_get($payload, 'shipment.tracking.link')
            ?? data_get($payload, 'data.tracking.link');

        if ((!$trackingNumber || !$carrierName) && $shipmentId) {
            try {
                $svc = app(\Modules\Geliver\Services\GeliverService::class);
                $remote = $svc->fetchShipmentById($shipmentId);
                if (!$trackingNumber) { $trackingNumber = $svc->extractTrackingNumber($remote); }
                if (!$carrierName) { $carrierName = $svc->extractCarrierName($remote); }
                if (!$trackingUrl) { $trackingUrl = $svc->extractTrackingUrl($remote); }
            } catch (\Throwable $e) {
            }
        }

        $updatePayload = [
            'geliver_last_status' => $payloadStatus,
            'geliver_last_status_at' => now(),
        ];
        if ($trackingNumber) {
            $updatePayload['shipping_tracking_number'] = (string) $trackingNumber;
        }
        if ($carrierName) {
            $updatePayload['shipping_carrier_name'] = is_array($carrierName) ? ($carrierName['name'] ?? json_encode($carrierName)) : (string) $carrierName;
        }
        if ($trackingUrl && filter_var($trackingUrl, FILTER_VALIDATE_URL)) {
            $updatePayload['shipping_tracking_url'] = (string) $trackingUrl;
            $updatePayload['tracking_reference'] = (string) $trackingUrl;
        }

        $map = config('geliver.status_map');
        $finals = config('geliver.final_statuses');
        $statusKey = is_string($payloadStatus) ? $payloadStatus : (string) $payloadStatus;
        $mapLower = [];
        foreach ($map as $k => $v) { $mapLower[mb_strtolower((string)$k, 'UTF-8')] = $v; }
        $statusKeyLower = mb_strtolower($statusKey, 'UTF-8');
        $newStatus = $map[$statusKey] ?? ($mapLower[$statusKeyLower] ?? null);
        // tracking bilgilerini önce yaz ki email eventinde mevcut olsun
        $order->update($updatePayload);

        // no fallback: if status not mapped, we only update tracking fields without transition
        if ($newStatus === null) {
            Log::info('Geliver webhook: status not mapped', ['status' => $payloadStatus, 'shipment_id' => $shipmentId]);
            return response()->json(['message' => 'ignored'], 200);
        }

        if (in_array($order->status, $finals, true) && !in_array($newStatus, $finals, true)) {
            return response()->json(['message' => 'final status preserved'], 200);
        }

        $order->transitionTo($newStatus);
        \Log::info('Geliver webhook status transitioned', [
            'order_id' => $order->id,
            'new_status' => $newStatus,
            'tracking' => $updatePayload['shipping_tracking_number'] ?? null,
            'carrier' => $updatePayload['shipping_carrier_name'] ?? null,
        ]);
        \Log::info('GELIVER_STATUS_UPDATED', [
            'order_db_id' => $order->id,
            'new_status' => $newStatus,
            'shipment_id' => $shipmentId,
        ]);

        return response()->json(['message' => 'ok'], 200);
    }
}
