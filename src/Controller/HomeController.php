<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Model\PropertyType;
use App\Service\PropertyService;

final class HomeController extends Controller
{
    public function __construct(View $view, Session $session, Auth $auth, private readonly PropertyService $properties)
    {
        parent::__construct($view, $session, $auth);
    }

    /** Ana sayfa: arama formu ve öne çıkan konaklamalar (taslak 01). */
    public function index(Request $request): Response
    {
        return $this->render('home/index', [
            'title' => 'Konaklamanızı bulun',
            'featured' => $this->properties->featured(6, $this->auth->user()?->id),
            'types' => PropertyType::options(),
        ]);
    }
}
