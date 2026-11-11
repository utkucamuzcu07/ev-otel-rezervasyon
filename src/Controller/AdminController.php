<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Exception\ValidationException;
use App\Model\ComplaintStatus;
use App\Model\ModerationStatus;
use App\Model\PropertyStatus;
use App\Model\Role;
use App\Model\UserStatus;
use App\Service\AuditService;
use App\Service\ComplaintService;
use App\Service\ConsistencyAuditService;
use App\Service\PropertyService;
use App\Service\ReportService;
use App\Service\ReviewService;
use App\Service\ScheduledTaskService;
use App\Service\UserService;
use App\Support\ClockInterface;
use App\Support\DateRange;
use App\Support\Settings;
use InvalidArgumentException;

/** Yönetici paneli (UC-09): kullanıcılar, ilanlar, şikâyetler, yorumlar, rapor ve denetim kaydı. */
final class AdminController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Auth $auth,
        private readonly UserService $users,
        private readonly PropertyService $properties,
        private readonly ComplaintService $complaints,
        private readonly ReviewService $reviews,
        private readonly ReportService $reports,
        private readonly AuditService $audit,
        private readonly ScheduledTaskService $task,
        private readonly ConsistencyAuditService $consistency,
        private readonly ClockInterface $clock,
        private readonly Settings $settings,
    ) {
        parent::__construct($view, $session, $auth);
    }

    public function dashboard(Request $request): Response
    {
        $today = $this->clock->today();

        return $this->render('admin/dashboard', [
            'title' => 'Yönetici paneli',
            'userCount' => $this->users->search('', null, null, 1, 1)->total,
            'propertyCount' => $this->properties->adminList('', PropertyStatus::Aktif, 1, 1)->total,
            'newComplaints' => $this->complaints->adminList(ComplaintStatus::Yeni, 1, 1)->total,
            'report' => $this->reports->build(null, $today->modify('first day of this month'), $today->modify('last day of this month')),
            'violations' => $this->consistency->violations(),
        ]);
    }

    /** Zamanlanmış görevi elle çalıştırma (demo için; normalde dakikada bir bin/gorev.php). */
    public function runTask(Request $request): Response
    {
        $result = $this->task->run();
        $this->flash('success', sprintf('Zamanlanmış görev çalıştı: %d bekletme süresi doldu, %d konaklama tamamlandı.', $result['expired'], $result['completed']));

        return $this->redirect('/yonetim');
    }

    public function users(Request $request): Response
    {
        return $this->render('admin/users', [
            'title' => 'Kullanıcılar',
            'page' => $this->userPage($request),
            'q' => $request->query,
            'roles' => Role::options(),
            'statuses' => UserStatus::options(),
        ]);
    }

    /** Bonus: yazarken sonuç veren canlı arama (kullanıcı tablosu). */
    public function usersFragment(Request $request): Response
    {
        $this->session->close();

        return $this->fragment('admin/_users_table', ['page' => $this->userPage($request), 'q' => $request->query]);
    }

    public function setUserStatus(Request $request, string $id): Response
    {
        $status = UserStatus::tryFrom($request->input('durum'));
        try {
            if ($status === null) {
                throw ValidationException::single('durum', 'Geçersiz durum.');
            }
            $this->users->setStatus($this->userId(), (int) $id, $status, $request->input('neden'));
            $this->flash('success', 'Hesap durumu güncellendi.');
        } catch (ValidationException $e) {
            $this->flash('danger', $e->getMessage());
        }

        return $this->redirect('/yonetim/kullanicilar');
    }

    public function properties(Request $request): Response
    {
        $status = PropertyStatus::tryFrom($request->query('durum'));
        $page = $this->properties->adminList($request->query('q'), $status, $request->int('sayfa', 1), 10);
        $owners = [];
        foreach ($page->items as $property) {
            $owners[$property->ownerId] ??= $this->properties->ownerName($property->ownerId);
        }

        return $this->render('admin/properties', [
            'title' => 'İlanlar',
            'page' => $page,
            'owners' => $owners,
            'q' => $request->query,
            'statuses' => PropertyStatus::options(),
        ]);
    }

    /** FR-28 + FR-39: ilan kaldırma ve gelecek rezervasyonların iptali. */
    public function removeProperty(Request $request, string $id): Response
    {
        try {
            $count = $this->properties->removeByAdmin($this->userId(), (int) $id, $request->input('neden'));
            $this->flash('success', sprintf('İlan kaldırıldı. %d gelecek rezervasyon iptal edildi ve ödenen tutarların tamamı iade edildi.', $count));
        } catch (ValidationException $e) {
            $this->flash('danger', $e->getMessage());
        }

        return $this->redirect('/yonetim/ilanlar');
    }

    public function complaints(Request $request): Response
    {
        $status = ComplaintStatus::tryFrom($request->query('durum'));

        return $this->render('admin/complaints', [
            'title' => 'Şikâyetler',
            'page' => $this->complaints->adminList($status, $request->int('sayfa', 1), 10),
            'status' => $status->value ?? '',
            'statuses' => ComplaintStatus::options(),
        ]);
    }

    public function updateComplaint(Request $request, string $id): Response
    {
        $status = ComplaintStatus::tryFrom($request->input('durum'));
        try {
            if ($status === null) {
                throw ValidationException::single('durum', 'Geçersiz durum.');
            }
            $this->complaints->update($this->userId(), (int) $id, $status, $request->input('not'));
            $this->flash('success', 'Şikâyet güncellendi.');
        } catch (ValidationException $e) {
            $this->flash('danger', $e->getMessage());
        }

        return $this->redirect('/yonetim/sikayetler');
    }

    public function reviews(Request $request): Response
    {
        $status = ModerationStatus::tryFrom($request->query('durum'));

        return $this->render('admin/reviews', [
            'title' => 'Yorum denetimi',
            'page' => $this->reviews->adminList($status, $request->int('sayfa', 1), 10),
            'status' => $status->value ?? '',
            'statuses' => ModerationStatus::options(),
        ]);
    }

    public function moderateReview(Request $request, string $id): Response
    {
        $status = ModerationStatus::tryFrom($request->input('durum'));
        try {
            if ($status === null) {
                throw ValidationException::single('durum', 'Geçersiz durum.');
            }
            $this->reviews->moderate($this->userId(), (int) $id, $status, $request->input('neden'));
            $this->flash('success', 'Yorum denetimi kaydedildi.');
        } catch (ValidationException $e) {
            $this->flash('danger', $e->getMessage());
        }

        return $this->redirect('/yonetim/yorumlar');
    }

    /** FR-30: tüm ilanların doluluk ve gelir raporu; bonus: CSV. */
    public function report(Request $request): Response
    {
        $today = $this->clock->today();
        $errors = [];
        try {
            $from = $request->query('baslangic') !== '' ? DateRange::parseDate($request->query('baslangic')) : $today->modify('first day of this month');
            $to = $request->query('bitis') !== '' ? DateRange::parseDate($request->query('bitis')) : $today->modify('last day of this month');
            $report = $this->reports->build(null, $from, $to);
        } catch (InvalidArgumentException | ValidationException $e) {
            $errors = $e instanceof ValidationException ? $e->errors() : ['baslangic' => 'Geçerli tarihler girin.'];
            $from = $today->modify('first day of this month');
            $to = $today->modify('last day of this month');
            $report = $this->reports->build(null, $from, $to);
        }
        if ($request->query('csv') === '1' && $errors === []) {
            return Response::csv('doluluk-gelir-tum-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.csv', $this->reports->toCsvRows($report));
        }

        return $this->render('reports/report', [
            'title' => 'Doluluk ve gelir raporu',
            'report' => $report,
            'errors' => $errors,
            'action' => '/yonetim/rapor',
            'scope' => 'Tüm ilanlar',
        ]);
    }

    public function audit(Request $request): Response
    {
        return $this->render('admin/audit', [
            'title' => 'Denetim kaydı',
            'page' => $this->audit->paginate($request->int('sayfa', 1), 20, $request->query('tur')),
            'type' => $request->query('tur'),
        ]);
    }

    /** @return \App\Support\Paginator<\App\Model\User> */
    private function userPage(Request $request): \App\Support\Paginator
    {
        return $this->users->search(
            $request->query('q'),
            Role::tryFrom($request->query('rol')),
            UserStatus::tryFrom($request->query('durum')),
            $request->int('sayfa', 1),
            $this->settings->perPage * 2,
        );
    }
}
