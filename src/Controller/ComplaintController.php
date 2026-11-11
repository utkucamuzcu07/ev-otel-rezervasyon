<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Exception\ValidationException;
use App\Service\ComplaintService;

/**
 * Şikâyet oluşturma ve izleme (FR-27). Gönderilen şikâyet yönetim ekibinin Şikâyetler kutusuna düşer;
 * kullanıcı Şikâyetlerim sayfasına yönlendirilir ve durumu oradan izler.
 */
final class ComplaintController extends Controller
{
    public function __construct(View $view, Session $session, Auth $auth, private readonly ComplaintService $complaints)
    {
        parent::__construct($view, $session, $auth);
    }

    public function index(Request $request): Response
    {
        return $this->render('complaint/index', ['title' => 'Şikâyetlerim', 'rows' => $this->complaints->forSender($this->userId())]);
    }

    public function create(Request $request): Response
    {
        return $this->form(['ilan' => $request->query('ilan'), 'rezervasyon' => $request->query('rezervasyon'), 'aciklama' => ''], []);
    }

    public function store(Request $request): Response
    {
        try {
            $complaint = $this->complaints->create($this->userId(), $request->nullableInt('ilan'), $request->input('rezervasyon'), $request->input('aciklama'));
        } catch (ValidationException $e) {
            return $this->form(['ilan' => $request->input('ilan'), 'rezervasyon' => $request->input('rezervasyon'), 'aciklama' => $request->input('aciklama')], $e->errors(), 422);
        }
        $this->flash('success', sprintf(
            'Şikâyetiniz alındı (#%d) ve yönetim ekibinin Şikâyetler kutusuna düştü. Durumunu bu sayfadan izleyebilirsiniz; yönetici yanıtlayınca bildirim alırsınız.',
            $complaint->id
        ));

        return $this->redirect('/sikayetlerim');
    }

    /**
     * @param array<string, string> $old
     * @param array<string, string> $errors
     */
    private function form(array $old, array $errors, int $status = 200): Response
    {
        return $this->render('complaint/create', [
            'title' => 'Şikâyet oluştur',
            'errors' => $errors,
            'old' => $old,
            'properties' => $this->complaints->propertyOptions(),
        ], $status);
    }
}
