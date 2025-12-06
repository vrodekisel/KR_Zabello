<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Domain\Repository\UserRepository;
use App\Domain\Entity\User;

class AuthController
{
    private UserRepository $userRepository;
    /** @var array<string, mixed> */
    private array $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(UserRepository $userRepository, array $config)
    {
        $this->userRepository = $userRepository;
        $this->config = $config;
    }

    public function register(): void
    {
        $input = $this->getJsonInput();

        $username = isset($input['username']) ? trim((string)$input['username']) : '';
        $password = isset($input['password']) ? (string)$input['password'] : '';

        if ($username === '' || $password === '') {
            $this->jsonError('auth.error.missing_fields', 400);
            return;
        }

        $existing = $this->userRepository->findByUsername($username);
        if ($existing !== null) {
            $this->jsonError('auth.error.username_taken', 409);
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $user = new User(
            null,
            $username,
            $passwordHash,
            false,
            new \DateTimeImmutable()
        );

        $this->userRepository->save($user);

        $this->jsonResponse([
            'message' => 'auth.register.success',
            'data' => [
                'username' => $user->getUsername(),
            ],
        ], 201);
    }

    public function login(): void
    {
        $input = $this->getJsonInput();

        $username = isset($input['username']) ? trim((string)$input['username']) : '';
        $password = isset($input['password']) ? (string)$input['password'] : '';

        if ($username === '' || $password === '') {
            $this->jsonError('auth.error.missing_fields', 400);
            return;
        }

        $user = $this->userRepository->findByUsername($username);
        if ($user === null) {
            $this->jsonError('auth.error.invalid_credentials', 401);
            return;
        }

        if (!password_verify($password, $user->getPasswordHash())) {
            $this->jsonError('auth.error.invalid_credentials', 401);
            return;
        }

        $token = $this->generateToken($user->getId());

        $this->jsonResponse([
            'message' => 'auth.login.success',
            'data' => [
                'token' => $token,
                'user' => [
                    'id'       => $user->getId(),
                    'username' => $user->getUsername(),
                ],
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return [];
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return [];
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function jsonError(string $messageKey, int $statusCode): void
    {
        $this->jsonResponse(
            [
                'error' => $messageKey,
            ],
            $statusCode
        );
    }

    private function generateToken(?int $userId): string
    {
        if ($userId === null) {
            return '';
        }

        $secret = (string)($this->config['app_key'] ?? 'local_dev_app_key');
        $data   = (string)$userId;
        $hash   = hash('sha256', $data . $secret);

        return base64_encode($data . ':' . $hash);
    }

    /**
     * Вспомогательный метод для других контроллеров
     * (можно будет вынести в отдельный сервис, если захочешь).
     */
    public function getUserIdFromToken(): ?int
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (stripos($header, 'Bearer ') !== 0) {
            return null;
        }

        $encoded = trim(substr($header, 7));
        if ($encoded === '') {
            return null;
        }

        $decoded = base64_decode($encoded, true);
        if ($decoded === false) {
            return null;
        }

        $parts = explode(':', $decoded, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$idPart, $hashPart] = $parts;
        if (!ctype_digit($idPart)) {
            return null;
        }

        $secret = (string)($this->config['app_key'] ?? 'local_dev_app_key');
        $expectedHash = hash('sha256', $idPart . $secret);

        if (!hash_equals($expectedHash, $hashPart)) {
            return null;
        }

        return (int)$idPart;
    }
}
