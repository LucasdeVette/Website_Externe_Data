<?php

namespace App\Service;

class ApiService
{
    private const BASE_URL = 'https://world.openfoodfacts.org/cgi/search.pl';

    public function searchProducts(string $query, int $page = 1, int $pageSize = 20): array
    {
        $url = sprintf(
            '%s?search_terms=%s&search_simple=1&action=process&json=1&page=%d&page_size=%d&lc=nl&cc=NL',
            self::BASE_URL,
            urlencode($query),
            $page,
            $pageSize
        );

        $response = $this->fetch($url);
        $data = json_decode($response, true);

        $products = [];
        if (isset($data['products'])) {
            foreach ($data['products'] as $item) {
                $products[] = [
                    'name'        => $item['product_name'] ?? 'Onbekend product',
                    'barcode'     => $item['code'] ?? null,
                    'description' => $item['generic_name'] ?? $item['product_name'] ?? null,
                    'price'       => $this->extractPrice($item),
                    'image_url'   => $item['image_url'] ?? $item['image_front_small_url'] ?? null,
                    'brand'       => $item['brands'] ?? null,
                    'quantity'    => $item['quantity'] ?? null,
                    'categories'  => $item['categories'] ?? null,
                ];
            }
        }

        return [
            'products'  => $products,
            'total'     => $data['count'] ?? 0,
            'page'      => $page,
            'page_size' => $pageSize,
        ];
    }

    public function getProductByBarcode(string $barcode): ?array
    {
        $url = sprintf('https://world.openfoodfacts.org/api/v2/product/%s', urlencode($barcode));
        $response = $this->fetch($url);
        $data = json_decode($response, true);

        if (!isset($data['product'])) {
            return null;
        }

        return [
            'name'        => $data['product']['product_name'] ?? 'Onbekend product',
            'barcode'     => $data['product']['code'] ?? $barcode,
            'description' => $data['product']['generic_name'] ?? $data['product']['product_name'] ?? null,
            'price'       => $this->extractPrice($data['product']),
            'image_url'   => $data['product']['image_url'] ?? $data['product']['image_front_small_url'] ?? null,
            'brand'       => $data['product']['brands'] ?? null,
            'quantity'    => $data['product']['quantity'] ?? null,
            'categories'  => $data['product']['categories'] ?? null,
        ];
    }

    private function fetch(string $url): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'Supercharged/1.0 (supermarkt-app; contact@supercharged.nl)',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $result === false) {
            return json_encode(['products' => [], 'count' => 0]);
        }

        return $result;
    }

    private function extractPrice(array $item): float
    {
        if (isset($item['ecoscore_data']['price'][0]['price'])) {
            return (float) $item['ecoscore_data']['price'][0]['price'];
        }
        return 0.00;
    }
}
