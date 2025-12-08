<?php

namespace Modules\ProductFeeds\Http\Controllers\Public;

use Illuminate\Http\Response;
use Modules\ProductFeeds\Services\FeedCacheService;
use Modules\ProductFeeds\Services\ProductFeedBuilder;

class TrendyolFeedController
{
    public function __construct(
        private readonly ProductFeedBuilder $feeds,
        private readonly FeedCacheService $cache,
    )
    {
    }

    public function index(): Response
    {
        if (! setting('product_feeds.global.enabled', true) || ! setting('product_feeds.trendyol.enabled', true)) {
            abort(404);
        }

        $channel = 'trendyol';

        if ($this->cache->isEnabled() && ! $this->cache->shouldRegenerate($channel)) {
            $cached = $this->cache->readCache($channel);

            if ($cached !== null) {
                return new Response($cached, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
            }
        }

        $response = $this->generate();

        if ($this->cache->isEnabled()) {
            $this->cache->writeCache($channel, (string) $response->getContent());
        }

        return $response;
    }

    public function generate(): Response
    {
        $rows = $this->feeds->normalizedItemsForFeed('trendyol');

        $supplierId = (string) setting('product_feeds.trendyol.supplier_id', '');
        $defaultBrand = (string) setting('product_feeds.trendyol.brand', setting('product_feeds.global.brand_name', setting('store_name')));
        $cargoCompany = (string) setting('product_feeds.trendyol.cargo_company', '');
        $vatRate = (string) setting('product_feeds.trendyol.vat_rate', '');
        $shipmentTime = (string) setting('product_feeds.trendyol.shipment_time', '1-3');

        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<products>';

        foreach ($rows as $row) {
            $id = (string) $row['id'];
            $sku = $row['sku'] ?: $id;
            $brand = $row['brand'] ?: $defaultBrand;
            $price = (float) ($row['sale_price'] ?? $row['price']);
            $listPrice = (float) $row['price'];
            $stockQty = $row['stock'] ?? 0;

            $xml[] = '<product>';
            $xml[] = '<id>' . e($id) . '</id>';
            $xml[] = '<name>' . e($row['title']) . '</name>';
            $xml[] = '<barcode>' . e($sku) . '</barcode>';
            $xml[] = '<brand>' . e($brand) . '</brand>';

            if (! empty($row['category_path'])) {
                $xml[] = '<category>' . e($row['category_path']) . '</category>';
            }

            $xml[] = '<price>' . number_format($price, 2, '.', '') . '</price>';
            $xml[] = '<listPrice>' . number_format($listPrice, 2, '.', '') . '</listPrice>';

            if ($vatRate !== '') {
                $xml[] = '<vatRate>' . e($vatRate) . '</vatRate>';
            }

            $xml[] = '<stockCode>' . e($sku) . '</stockCode>';
            $xml[] = '<stockQuantity>' . (int) $stockQty . '</stockQuantity>';
            $xml[] = '<description>' . e($row['description']) . '</description>';

            $xml[] = '<images>';

            if (! empty($row['main_image'])) {
                $xml[] = '<image>' . e($row['main_image']) . '</image>';
            }

            foreach ($row['additional_images'] as $image) {
                $xml[] = '<image>' . e($image) . '</image>';
            }

            $xml[] = '</images>';

            if ($cargoCompany !== '') {
                $xml[] = '<cargoCompanyName>' . e($cargoCompany) . '</cargoCompanyName>';
            }

            $xml[] = '<shipmentTime>' . e($shipmentTime) . '</shipmentTime>';

            if ($supplierId !== '') {
                $xml[] = '<supplierId>' . e($supplierId) . '</supplierId>';
            }

            $xml[] = '</product>';
        }

        $xml[] = '</products>';

        $content = implode("\n", $xml);

        return new Response($content, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
