<?php

namespace App\Service;

class ApiService
{
    private const BASE_URL = 'https://world.openfoodfacts.org/cgi/search.pl';
    private const PRICES_API = 'https://prices.openfoodfacts.org/api/v1/prices';
    private const AH_API = 'https://api.ah.nl';
    private const AH_UA = 'Appie/9.28 (iPhone17,3; iPhone; CPU OS 26_1 like Mac OS X)';
    private const AH_HEADERS = [
        'Content-Type: application/json', 'x-client-name: appie-ios',
        'x-client-version: 9.28', 'x-application: AHWEBSHOP', 'Accept: application/json',
    ];
    private ?string $ahToken = null;
    private ?int $ahTokenExpires = 0;

    public function fetchAllMarketPrices(array $comparison): array
    {
        $marktPrices = [];
        if (empty($comparison)) return $marktPrices;

        $barcodes = array_filter(array_map(fn($i) => $i['product']->getBarcode(), $comparison));
        $offPrices = !empty($barcodes) ? $this->fetchPricesBatch(array_values($barcodes)) : [];

        $ahNames = [];
        foreach ($comparison as $i => $item) {
            $p = $item['product'];
            $pid = $p->getId();
            if ($p->getBarcode() && isset($offPrices[$p->getBarcode()])) {
                $marktPrices[$pid] = $offPrices[$p->getBarcode()];
            } else {
                $ahNames[$pid] = $p->getName();
            }
        }

        foreach ($this->fetchAhPrices($ahNames) as $pid => $price) {
            $marktPrices[$pid] = $price;
        }

        return $marktPrices;
    }

    public function searchProducts(string $query, int $page = 1, int $pageSize = 20): array
    {
        $url = sprintf('%s?search_terms=%s&search_simple=1&action=process&json=1&page=%d&page_size=%d&lc=nl&cc=NL', self::BASE_URL, urlencode($query), $page, $pageSize);
        $data = json_decode($this->fetch($url), true);
        $products = [];
        foreach ($data['products'] ?? [] as $item) {
            $price = 0.0;
            if (isset($item['ecoscore_data']['price'][0]['price'])) $price = (float) $item['ecoscore_data']['price'][0]['price'];
            $products[] = [
                'name' => $item['product_name'] ?? 'Onbekend product',
                'barcode' => $item['code'] ?? null,
                'description' => $item['generic_name'] ?? $item['product_name'] ?? null,
                'price' => $price,
                'image_url' => $item['image_url'] ?? $item['image_front_small_url'] ?? null,
                'brand' => $item['brands'] ?? null,
                'quantity' => $item['quantity'] ?? null,
                'categories' => $item['categories'] ?? null,
            ];
        }
        return ['products' => $products, 'total' => $data['count'] ?? 0, 'page' => $page, 'page_size' => $pageSize];
    }

    public function getProductByBarcode(string $barcode): ?array
    {
        $data = json_decode($this->fetch(sprintf('https://world.openfoodfacts.org/api/v2/product/%s', urlencode($barcode))), true);
        if (!isset($data['product'])) return null;
        $price = 0.0;
        if (isset($data['product']['ecoscore_data']['price'][0]['price'])) $price = (float) $data['product']['ecoscore_data']['price'][0]['price'];
        return [
            'name' => $data['product']['product_name'] ?? 'Onbekend product',
            'barcode' => $data['product']['code'] ?? $barcode,
            'description' => $data['product']['generic_name'] ?? $data['product']['product_name'] ?? null,
            'price' => $price,
            'image_url' => $data['product']['image_url'] ?? $data['product']['image_front_small_url'] ?? null,
            'brand' => $data['product']['brands'] ?? null,
            'quantity' => $data['product']['quantity'] ?? null,
            'categories' => $data['product']['categories'] ?? null,
        ];
    }

    private function fetch(string $url): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'Supercharged/1.0', CURLOPT_FOLLOWLOCATION => true, CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpCode === 200 && $result !== false ? $result : '{}';
    }

    public function fetchPricesBatch(array $barcodes): array
    {
        if (empty($barcodes)) return [];
        $prices = [];
        foreach (array_chunk(array_unique(array_filter($barcodes)), 10) as $chunk) {
            $data = json_decode($this->fetch(sprintf('%s?product_code=%s&limit=%d&order_by=date&order_direction=desc', self::PRICES_API, implode(',', array_map('urlencode', $chunk)), 10 * count($chunk))), true);
            foreach ($data['items'] ?? [] as $item) {
                $bc = $item['product_code'] ?? null;
                $price = $item['price'] ?? 0;
                if ($bc && $price > 0) $prices[$bc] = isset($prices[$bc]) ? min($prices[$bc], (float) $price) : (float) $price;
            }
        }
        return $prices;
    }

    private function ahRequest(string $url, ?string $token = null, bool $post = false, ?string $body = null): ?array
    {
        $ch = curl_init();
        $headers = self::AH_HEADERS;
        if ($token) $headers[] = 'Authorization: Bearer ' . $token;
        $opts = [
            CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => self::AH_UA, CURLOPT_HTTPHEADER => $headers, CURLOPT_SSL_VERIFYPEER => true,
        ];
        if ($post) { $opts[CURLOPT_POST] = true; $opts[CURLOPT_POSTFIELDS] = $body ?? ''; }
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200 || $result === false) return null;
        $decoded = json_decode($result, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function ahGetToken(): ?string
    {
        if ($this->ahToken && time() < $this->ahTokenExpires) return $this->ahToken;
        $data = $this->ahRequest(self::AH_API . '/mobile-auth/v1/auth/token/anonymous', null, true, '{"clientId":"appie"}');
        if (!empty($data['access_token'])) {
            $this->ahToken = $data['access_token'];
            $this->ahTokenExpires = time() + ($data['expires_in'] ?? 3600) - 60;
            return $this->ahToken;
        }
        return null;
    }

    public function ahSearchProduct(string $query): ?float
    {
        $token = $this->ahGetToken();
        if (!$token) return null;
        $clean = trim(preg_replace('/\s+[\d.,]+\s*(?:[LXKgl]|ml|kg)\b/i', '', $query));
        $data = $this->ahRequest(self::AH_API . '/mobile-services/product/search/v2?query=' . urlencode($clean) . '&sortOn=RELEVANCE&size=5', $token);
        if (empty($data['products'])) return null;
        foreach ($data['products'] as $p) {
            $price = $p['currentPrice'] ?? $p['priceBeforeBonus'] ?? null;
            if ($price > 0 && !preg_match('/^\d+\s*x\s+/i', $p['salesUnitSize'] ?? '')) return (float) $price;
        }
        $first = $data['products'][0];
        $price = $first['currentPrice'] ?? $first['priceBeforeBonus'] ?? 0;
        return $price > 0 ? (float) $price : null;
    }

    public function fetchAhPrices(array $productNames): array
    {
        if (empty($productNames)) return [];
        $prices = [];
        foreach ($productNames as $key => $name) {
            if (empty($name)) continue;
            $price = $this->ahSearchProduct($name);
            if ($price !== null) $prices[$key] = $price;
            usleep(200000);
        }
        return $prices;
    }
}
