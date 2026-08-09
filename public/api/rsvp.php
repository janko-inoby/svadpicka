<?php

declare(strict_types=1);

use Svadpicka\RsvpRepository;
use Svadpicka\RsvpValidator;

require dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Nepovolená metóda.']);
    exit;
}

try {
    if (!empty($_POST['website'])) {
        throw new RuntimeException('Neplatná požiadavka.');
    }

    $token = trim((string) ($_POST['token'] ?? ''));
    if (!preg_match('/^[A-Za-z0-9_-]{16,128}$/', $token)) {
        throw new InvalidArgumentException('Neplatný osobný odkaz.');
    }

    $repository = new RsvpRepository();
    $guest = $repository->findActiveGuest($token);
    if ($guest === null) {
        throw new InvalidArgumentException('Pozvánka sa nenašla alebo už nie je aktívna.');
    }

    $repository->save($guest['row'], RsvpValidator::fromRequest($_POST));
    echo json_encode(['ok' => true, 'message' => 'Ďakujeme, odpoveď máme zapísanú.']);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log($e->__toString());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Niečo sa pokazilo. Skús to, prosím, ešte raz.']);
}

