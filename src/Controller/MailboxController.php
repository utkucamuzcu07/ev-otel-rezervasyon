<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Exception\NotFoundException;
use App\Service\Mail\FileMailbox;

/**
 * Geliştirme aracı: simüle posta kutusu. E-posta gerçek adrese gitmediği için parola sıfırlama
 * bağlantısı burada açılır. İletilerde sıfırlama jetonu bulunduğundan sayfa yalnızca ayar açıkken
 * (EVOTEL_POSTA_KUTUSU) ve istek bu bilgisayardan geldiğinde görünür; aksi halde 404 döner.
 */
final class MailboxController extends Controller
{
    public function __construct(View $view, Session $session, Auth $auth, private readonly FileMailbox $mailbox)
    {
        parent::__construct($view, $session, $auth);
    }

    public function index(Request $request): Response
    {
        $this->guard($request);

        return $this->noStore($this->render('mail/index', ['title' => 'Posta kutusu', 'messages' => $this->mailbox->latest()]));
    }

    public function show(Request $request, string $id): Response
    {
        $this->guard($request);
        $message = $this->mailbox->find($id) ?? throw new NotFoundException('İleti bulunamadı.');

        return $this->noStore($this->render('mail/show', ['title' => $message->subject, 'message' => $message]));
    }

    private function guard(Request $request): void
    {
        if (!$this->mailbox->enabled() || !$request->isFromLoopback()) {
            throw new NotFoundException('Aradığınız sayfa bulunamadı.');
        }
    }

    /** Sıfırlama jetonu içeren sayfa tarayıcı önbelleğinde kalmasın. */
    private function noStore(Response $response): Response
    {
        $response->headers['Cache-Control'] = 'no-store';

        return $response;
    }
}
