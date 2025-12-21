<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Domain\Repository\UserRepository;
use App\Localization\Translator;
use App\View\View;

final class WebAuthController
{
    private UserRepository $userRepository;
    private Translator $translator;
    private View $view;

    /** @var string[] */
    private array $availableLocales;

    /**
     * @param string[] $availableLocales
     */
    public function __construct(
        UserRepository $userRepository,
        Translator $translator,
        array $availableLocales,
        View $view
    ) {
        $this->userRepository   = $userRepository;
        $this->translator       = $translator;
        $this->availableLocales = $availableLocales;
        $this->view             = $view;
    }

    private function t(string $key): string
    {
        return $this->translator->trans($key);
    }

    public function showLogin(): void
    {
        header('Content-Type: text/html; charset=utf-8');

        $locale   = $this->translator->getLocale();
        $redirect = isset($_GET['redirect']) ? (string) $_GET['redirect'] : '/web/polls';
        $errorKey = isset($_GET['error']) ? (string) $_GET['error'] : null;

        $errorText = $errorKey !== null && $errorKey !== ''
            ? $this->t($errorKey)
            : null;

        $this->view->render('login', [
            'locale'        => $locale,
            'redirect'      => $redirect,
            'errorText'     => $errorText,
            'title'         => $this->t('ui.web.login.title'),
            'usernameLabel' => $this->t('ui.web.login.username'),
            'passwordLabel' => $this->t('ui.web.login.password'),
            'languageLabel' => $this->t('ui.web.header.language'),
            'buttonText'    => $this->t('ui.web.login.button'),
            'helperText'    => $this->t('ui.web.login.helper_demousers'),
        ]);
    }

    public function handleLogin(): void
    {
        $username = isset($_POST['username']) ? trim((string) $_POST['username']) : '';
        $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
        $lang     = isset($_POST['lang']) ? (string) $_POST['lang'] : null;
        $redirect = isset($_POST['redirect']) ? (string) $_POST['redirect'] : '/web/polls';

        if ($username === '' || $password === '') {
            $this->redirectToLogin('ui.web.login.error_invalid', $redirect);
            return;
        }

        $user = $this->userRepository->findByUsername($username);
        if ($user === null) {
            $this->redirectToLogin('ui.web.login.error_invalid', $redirect);
            return;
        }

        if (!password_verify($password, $user->getPasswordHash())) {
            $this->redirectToLogin('ui.web.login.error_invalid', $redirect);
            return;
        }

        if ($user->isBanned()) {
            $this->redirectToLogin('ui.web.login.error_banned', $redirect);
            return;
        }

        $_SESSION['user_id']  = $user->getId();
        $_SESSION['username'] = $user->getUsername();

        if ($lang !== null && in_array($lang, $this->availableLocales, true)) {
            setcookie('lang', $lang, [
                'expires'  => time() + 365 * 24 * 60 * 60,
                'path'     => '/',
                'secure'   => false,
                'httponly' => false,
                'samesite' => 'Lax',
            ]);
        }

        if ($redirect === '' || $redirect[0] !== '/') {
            $redirect = '/web/polls';
        }

        header('Location: ' . $redirect);
        exit;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        header('Location: /web/login');
        exit;
    }

    private function redirectToLogin(string $errorKey, string $redirect): void
    {
        if ($redirect === '' || $redirect[0] !== '/') {
            $redirect = '/web/polls';
        }

        $location = '/web/login?error=' . urlencode($errorKey)
            . '&redirect=' . urlencode($redirect);

        header('Location: ' . $location);
        exit;
    }
}
