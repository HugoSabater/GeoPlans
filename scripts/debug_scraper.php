<?php
require_once __DIR__ . '/../vendor/autoload.php';
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

$client = new Client(['verify' => false]);
$url = 'https://www.teatromadrid.com/cartelera';
$html = (string) $client->get($url)->getBody();
$crawler = new Crawler($html);

echo "--- DEBUG CARD LIST ITEMS ---\n";
// Buscar LIs dentro de card
$crawler->filter('.card li')->each(function ($node, $i) {
    if ($i > 4)
        return;
    echo "LI $i:\n";
    echo "   Text: " . substr(strip_tags($node->html()), 0, 50) . "...\n";
    $a = $node->filter('a')->count() ? $node->filter('a')->attr('href') : 'No link';
    echo "   Link: $a\n";
    $img = $node->filter('img')->count() ? "YES" : "NO";
    echo "   Image: $img\n";
});
