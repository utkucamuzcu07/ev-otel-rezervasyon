<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Exception\ValidationException;
use App\Service\ReservationService;
use App\Service\ReviewService;

/** Tamamlanan konaklamaya puan ve yorum (FR-24). */
final class ReviewController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Auth $auth,
        private readonly ReviewService $reviews,
        private readonly ReservationService $reservations,
    ) {
        parent::__construct($view, $session, $auth);
    }

    public function create(Request $request, string $no): Response
    {
        $r = $this->reservations->viewFor($no, $this->user());
        if (!$r->canReview) {
            $this->flash('warning', 'Bu rezervasyon için yorum yapılamaz (yalnızca tamamlanan konaklamalara bir kez yorum yapılabilir).');

            return $this->redirect('/rezervasyon/' . $no);
        }

        return $this->render('review/create', ['title' => 'Yorum yaz', 'r' => $r, 'errors' => [], 'old' => []]);
    }

    public function store(Request $request, string $no): Response
    {
        try {
            $this->reviews->create($this->userId(), $no, $request->int('puan'), $request->input('yorum'));
        } catch (ValidationException $e) {
            return $this->render('review/create', [
                'title' => 'Yorum yaz',
                'r' => $this->reservations->viewFor($no, $this->user()),
                'errors' => $e->errors(),
                'old' => ['puan' => $request->input('puan'), 'yorum' => $request->input('yorum')],
            ], 422);
        }
        $this->flash('success', 'Yorumunuz için teşekkürler!');

        return $this->redirect('/rezervasyon/' . $no);
    }
}
