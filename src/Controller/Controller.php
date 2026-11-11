<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Exception\ForbiddenException;
use App\Model\User;

/**
 * Controller: isteği alır, servisi çağırır, görünümü seçer. SQL yazmaz, hesap yapmaz.
 */
abstract class Controller
{
    public function __construct(
        protected readonly View $view,
        protected readonly Session $session,
        protected readonly Auth $auth,
    ) {
    }

    /** @param array<string, mixed> $data */
    protected function render(string $template, array $data = [], int $status = 200): Response
    {
        $data['flashes'] = $this->session->takeFlashes();

        return Response::html($this->view->render($template, $data), $status);
    }

    /**
     * Sayfa bölümü (kısmi yenileme ve canlı arama): yerleşim olmadan yalnızca parça.
     *
     * @param array<string, mixed> $data
     */
    protected function fragment(string $template, array $data = [], int $status = 200): Response
    {
        return Response::html($this->view->render($template, $data, null), $status);
    }

    /** @param array<string, mixed> $query */
    protected function redirect(string $path, array $query = []): Response
    {
        return Response::redirect($this->view->url($path, $query));
    }

    protected function flash(string $type, string $message): void
    {
        $this->session->flash($type, $message);
    }

    protected function user(): User
    {
        return $this->auth->user() ?? throw new ForbiddenException('Bu işlem için giriş yapmalısınız.');
    }

    protected function userId(): int
    {
        return (int) $this->user()->id;
    }

    /** Yalnızca uygulama içi adreslere geri dönülür (açık yönlendirme engeli). */
    protected function safePath(string $path, string $fallback): string
    {
        return str_starts_with($path, '/') && !str_starts_with($path, '//') && !str_contains($path, '\\') ? $path : $fallback;
    }
}
