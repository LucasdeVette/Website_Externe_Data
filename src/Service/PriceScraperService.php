<?php

namespace App\Service;

class PriceScraperService
{
    private const AH_AUTH_URL = 'https://api.ah.nl/mobile-auth/v1/auth/token/anonymous';
    private const AH_SEARCH_URL = 'https://api.ah.nl/mobile-services/product/search/v2';
    private const JUMBO_SEARCH_URL = 'https://www.jumbo.com/api/search';
    private const PLUS_SEARCH_URL = 'https://www.plus.nl/api/products/search';
    private const DIRK_SEARCH_URL = 'https://www.dirk.nl/api/search';
    private const OFF_SEARCH_URL = 'https://world.openfoodfacts.org/cgi/search.pl';
    private const OFF_PRODUCT_URL = 'https://world.openfoodfacts.org/api/v2/product';
    private const OFF_PRICES_URL = 'https://prices.openfoodfacts.org/api/v1/prices';

    private ?string $ahToken = null;
    private int $ahTokenExpires = 0;

    public function fetchPrice(string $productName, ?string $barcode, string $storeName): ?float
    {
        $storeLower = mb_strtolower(trim($storeName));

        if (str_contains($storeLower, 'albert') || str_contains($storeLower, 'ah')) {
            return $this->fetchFromAh($productName);
        }

        if (str_contains($storeLower, 'jumbo')) {
            return $this->fetchFromJumbo($productName, $barcode);
        }

        if (str_contains($storeLower, 'plus')) {
            return $this->fetchFromPlus($productName);
        }

        if (str_contains($storeLower, 'dirk')) {
            return $this->fetchFromDirk($productName);
        }

        return null;
    }

    public static function getSupportedStorePatterns(): array
    {
        return ['albert', 'ah', 'jumbo', 'plus', 'dirk'];
    }

    public function fetchAllPrices(array $products, array $stores): array
    {
        $results = [];
        foreach ($products as $product) {
            foreach ($stores as $store) {
                try {
                    $price = $this->fetchPrice($product->getName(), $product->getBarcode(), $store->getName());
                    if ($price !== null) {
                        $results[] = [
                            'product_id'   => $product->getId(),
                            'product_name' => $product->getName(),
                            'store_id'     => $store->getId(),
                            'store_name'   => $store->getName(),
                            'price'        => $price,
                        ];
                    }
                } catch (\Throwable $e) {
                    error_log('PriceScraper error: ' . $e->getMessage() . ' for ' . $product->getName() . ' @ ' . $store->getName());
                }
            }
        }
        return $results;
    }

    private function getAhToken(): ?string
    {
        if ($this->ahToken && time() < $this->ahTokenExpires - 60) {
            return $this->ahToken;
        }

        $data = $this->fetchJson(self::AH_AUTH_URL, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS    => json_encode(['clientId' => 'appie']),
            CURLOPT_HTTPHEADER    => [
                'Content-Type: application/json',
                'User-Agent: Appie/8.22.3',
            ],
        ]);

        if (!$data || empty($data['access_token'])) {
            return null;
        }

        $this->ahToken = $data['access_token'];
        $this->ahTokenExpires = time() + ($data['expires_in'] ?? 7200);
        return $this->ahToken;
    }

    private function fetchFromAh(string $productName): ?float
    {
        $token = $this->getAhToken();
        if (!$token) return null;

        $query = preg_replace('/\s*\d+x?\d*\s*(g|kg|l|ml|stuks?|pack)?\s*$/i', '', $productName);
        $query = trim($query);

        $url = self::AH_SEARCH_URL . '?' . http_build_query([
            'query'  => $query,
            'size'   => 8,
            'sortOn' => 'RELEVANCE',
        ]);

        $data = $this->fetchJson($url, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'X-Application: AHWEBSHOP',
                'User-Agent: Appie/8.22.3',
                'Content-Type: application/json',
            ],
        ]);

        if (!$data || empty($data['products'])) {
            return null;
        }

        $words   = array_values(array_filter(explode(' ', mb_strtolower($productName)), fn($w) => mb_strlen($w) >= 2));
        $bestScore = -1;
        $bestPrice = null;

        foreach ($data['products'] as $p) {
            $price = $p['currentPrice'] ?? 0;
            if ($price <= 0) continue;
            if (empty($p['availableOnline'])) continue;

            $title  = mb_strtolower($p['title'] ?? '');
            $brand  = mb_strtolower($p['brand'] ?? '');
            $isPack = (bool) preg_match('/\d+-?pack|\d+\s*stuks/i', $title);

            $matchScore = 0;
            foreach ($words as $w) {
                if (str_contains($title, $w)) {
                    $matchScore += $isPack && !str_contains(mb_strtolower($productName), 'pack') ? 0.3 : 1;
                }
            }

            if ($matchScore > $bestScore) {
                $bestScore = $matchScore;
                $bestPrice = (float) $price;
            }
        }

        return $bestPrice;
    }

    private function fetchFromJumbo(string $productName, ?string $barcode): ?float
    {
        $query = $barcode ?: $productName;
        $url = self::JUMBO_SEARCH_URL . '?searchType=keyword&searchTerms=' . urlencode($query);

        $data = $this->fetchJson($url, [
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept: application/json',
            ],
        ]);

        if (!$data || empty($data['products'])) {
            return null;
        }

        $words = explode(' ', mb_strtolower($productName));
        $bestScore = 0;
        $bestPrice = null;

        foreach ($data['products'] as $p) {
            $title = mb_strtolower($p['title'] ?? '');
            $prices = $p['prices'] ?? [];
            $price = $prices['price']['amount'] ?? null;
            if (!$price) continue;

            $matchScore = 0;
            foreach ($words as $w) {
                if (strlen($w) < 2) continue;
                if (str_contains($title, $w)) {
                    $matchScore++;
                }
            }
            if ($matchScore > $bestScore) {
                $bestScore = $matchScore;
                $bestPrice = (float) $price / 100;
            }
        }

        return $bestPrice;
    }

    private function fetchFromPlus(string $productName): ?float
    {
        $query = preg_replace('/\s*\d+x?\d*\s*(g|kg|l|ml|stuks?|pack)?\s*$/i', '', $productName);
        $query = trim($query);

        $url = self::PLUS_SEARCH_URL . '?' . http_build_query([
            'q' => $query,
            'pageSize' => 8,
        ]);

        $data = $this->fetchJson($url, [
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept: application/json',
                'Referer: https://www.plus.nl/',
            ],
        ]);

        if (!$data) {
            return null;
        }

        $products = $data['products'] ?? $data['results'] ?? $data['items'] ?? null;
        if (!$products || !is_array($products)) {
            return null;
        }

        $words = array_values(array_filter(explode(' ', mb_strtolower($productName)), fn($w) => mb_strlen($w) >= 2));
        $bestScore = -1;
        $bestPrice = null;

        foreach ($products as $p) {
            $title = mb_strtolower($p['title'] ?? $p['name'] ?? $p['productName'] ?? '');
            $price = $p['price']['now'] ?? $p['price'] ?? $p['prices']['price'] ?? null;
            if (is_array($price)) {
                $price = $price['amount'] ?? null;
            }
            if (!$price || (float) $price <= 0) continue;

            $matchScore = 0;
            foreach ($words as $w) {
                if (str_contains($title, $w)) {
                    $matchScore++;
                }
            }

            if ($matchScore > $bestScore) {
                $bestScore = $matchScore;
                $bestPrice = (float) $price;
            }
        }

        return $bestPrice;
    }

    private function fetchFromDirk(string $productName): ?float
    {
        $query = preg_replace('/\s*\d+x?\d*\s*(g|kg|l|ml|stuks?|pack)?\s*$/i', '', $productName);
        $query = trim($query);

        $url = self::DIRK_SEARCH_URL . '?' . http_build_query([
            'q' => $query,
            'limit' => 8,
        ]);

        $data = $this->fetchJson($url, [
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept: application/json',
                'Referer: https://www.dirk.nl/',
            ],
        ]);

        if (!$data) {
            return null;
        }

        $products = $data['products'] ?? $data['results'] ?? $data['items'] ?? null;
        if (!$products || !is_array($products)) {
            return null;
        }

        $words = array_values(array_filter(explode(' ', mb_strtolower($productName)), fn($w) => mb_strlen($w) >= 2));
        $bestScore = -1;
        $bestPrice = null;

        foreach ($products as $p) {
            $title = mb_strtolower($p['title'] ?? $p['name'] ?? $p['productName'] ?? '');
            $price = $p['price']['now'] ?? $p['price'] ?? $p['prices']['price'] ?? null;
            if (is_array($price)) {
                $price = $price['amount'] ?? null;
            }
            if (!$price || (float) $price <= 0) continue;

            $matchScore = 0;
            foreach ($words as $w) {
                if (str_contains($title, $w)) {
                    $matchScore++;
                }
            }

            if ($matchScore > $bestScore) {
                $bestScore = $matchScore;
                $bestPrice = (float) $price;
            }
        }

        return $bestPrice;
    }

    private function findBarcodeByName(string $productName): ?string
    {
        $url = sprintf(
            '%s?search_terms=%s&search_simple=1&action=process&json=1&page_size=3&lc=nl&cc=NL',
            self::OFF_SEARCH_URL,
            urlencode($productName)
        );

        $data = $this->fetchJson($url);
        if (!$data || empty($data['products'])) {
            return null;
        }

        foreach ($data['products'] as $p) {
            if (!empty($p['code'])) {
                return $p['code'];
            }
        }
        return null;
    }

    private function fetchFromOffPrices(string $barcode, string $storeName): ?float
    {
        $url = sprintf('%s?product_code=%s&limit=5', self::OFF_PRICES_URL, urlencode($barcode));
        $data = $this->fetchJson($url);

        if (!$data || empty($data['items'])) {
            return null;
        }

        $storeLower = mb_strtolower(trim($storeName));
        foreach ($data['items'] as $item) {
            if (!empty($item['price'])) {
                $location = $item['location'] ?? [];
                $locationName = mb_strtolower($location['name'] ?? '');
                if (str_contains($locationName, $storeLower) || str_contains($storeLower, $locationName)) {
                    return (float) $item['price'];
                }
            }
        }

        if (!empty($data['items'][0]['price'])) {
            return (float) $data['items'][0]['price'];
        }

        return null;
    }

    private function fetchFromOffProduct(string $barcode, string $storeName): ?float
    {
        $url = sprintf('%s/%s.json?fields=product_name,stores,ecoscore_data', self::OFF_PRODUCT_URL, urlencode($barcode));
        $data = $this->fetchJson($url);

        if (!$data || !isset($data['product'])) {
            return null;
        }

        $product = $data['product'];

        $stores = $product['stores'] ?? '';
        $storeLower = mb_strtolower(trim($storeName));
        $productStores = array_map('trim', explode(',', mb_strtolower($stores)));

        if (!empty($stores) && !in_array($storeLower, array_map('mb_strtolower', $productStores))) {
            return null;
        }

        if (isset($product['ecoscore_data']['price'][0]['price'])) {
            return (float) $product['ecoscore_data']['price'][0]['price'];
        }

        return null;
    }

    private function fetchJson(string $url, array $extraOpts = []): ?array
    {
        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
        ];

        foreach ($extraOpts as $k => $v) {
            $opts[$k] = $v;
        }

        if (!isset($opts[CURLOPT_USERAGENT])) {
            $opts[CURLOPT_USERAGENT] = 'Supercharged/1.0 (supermarkt-app; contact@supercharged.nl)';
        }

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $result === false) {
            return null;
        }

        $decoded = json_decode($result, true);
        return is_array($decoded) ? $decoded : null;
    }
}
