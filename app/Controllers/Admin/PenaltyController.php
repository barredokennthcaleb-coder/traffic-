<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ViolatorModel;
use App\Models\ViolatorRecord;

class PenaltyController extends BaseController
{
    protected $violationRecord;
    protected $violatorModel;

    public function __construct()
    {
        $this->violationRecord = new ViolatorRecord();
        $this->violatorModel   = new ViolatorModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Penalty Management',
            'pending_violations' => $this->violationRecord
                ->select('violations.*, users.username as officer_name, GROUP_CONCAT(CONCAT(violation_type, "::", penalty_amount) SEPARATOR "||") as concatenated_violations, SUM(penalty_amount) as total_penalty_sum, SUM(points) as total_points_sum')
                ->join('users', 'users.id = violations.officer_id', 'left')
                ->where('violations.status', 'Pending')
                ->groupBy('violations.ticket_id')
                ->orderBy('violations.violation_date', 'DESC')
                ->paginate(10, 'penalties'),
            'pager' => $this->violationRecord->pager,
        ];
        return view('admin/penalties/index', $data);
    }

    public function all()
    {
        $filters = [
            'search'   => trim((string) $this->request->getGet('search')),
            'status'   => trim((string) $this->request->getGet('status')),
            'attempts' => trim((string) $this->request->getGet('attempts')),
            'sort'     => trim((string) $this->request->getGet('sort')),
        ];

        $perPage = 10;
        $page    = max(1, (int) ($this->request->getGet('page_violators') ?? 1));
        $result  = $this->violatorModel->getViolatorList($filters, $perPage, $page);

        $data = [
            'title'      => 'All Violators',
            'violators'  => $result['rows'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'perPage'    => $result['per_page'],
            'stats'      => $result['stats'],
            'counts'     => $this->violatorModel->statusCounts(),
            'pagerHtml'  => service('pager')->makeLinks($page, $perPage, $result['total'], 'bootstrap_pagination', 0, 'violators'),
            'search'     => $filters['search'],
            'status'     => $filters['status'],
            'attempts'   => $filters['attempts'],
            'sort'       => $filters['sort'] ?: 'last',
        ];

        return view('admin/penalties/all', $data);
    }

    /**
     * Violator dossier with the complete linked ticket history.
     */
    public function violatorProfile($id)
    {
        $profile = $this->violatorModel->getProfile((int) $id);

        if (! $profile) {
            return redirect()->to(base_url('penalties/all'))->with('error', 'Violator record not found.');
        }

        $data = [
            'title'         => 'Violator Profile - ' . ($profile['violator']['driver_name'] ?: 'Record'),
            'violator'      => $profile['violator'],
            'summary'       => $profile['summary'],
            'tickets'       => $profile['tickets'],
            'vehicles'      => $profile['vehicles'],
            'backUrl'       => base_url('penalties/all'),
            'backLabel'     => 'All Violators',
            'ticketViewBase'=> base_url('penalties/view/'),
            'recordUrl'     => base_url('officer/violations?violator_id=' . (int) $profile['violator']['id']),
            'isAdmin'       => true,
            'cancelBase'    => base_url('penalties/cancel/'),
        ];

        return view('violators/profile', $data);
    }

    public function pay($id = null)
    {
        $violation = $this->violationRecord->find($id);

        if (!$violation || $violation['status'] != 'Pending') {
            return redirect()->to(base_url('penalties'))->with('error', 'Violation not found or already paid.');
        }

        // Each infraction on the ticket is collected on its own receipt line,
        // so the receipt lists every violation with its own fine.
        $infractions = $this->violationRecord->getTicketInfractions($violation['ticket_id'] ?? '');
        $ticketTotal = array_sum(array_map(
            static fn($infraction) => (float) $infraction['penalty_amount'],
            $infractions
        ));

        $violation['penalty_amount'] = $infractions === [] ? (float) $violation['penalty_amount'] : $ticketTotal;

        $data = [
            'title' => 'Record Payment',
            'violation' => $violation,
            'infractions' => $infractions,
        ];
        return view('admin/penalties/pay', $data);
    }

    public function store()
    {
        $rules = [
            'violation_id'  => 'required|integer',
            'payment_method'=> 'required|in_list[Cash,Check,Money Order]',
            'transaction_id'=> 'required|max_length[100]',
            'remarks'       => 'permit_empty|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $violationId   = (int) $this->request->getPost('violation_id');
        $paymentMethod = (string) $this->request->getPost('payment_method');
        $remarks       = trim((string) ($this->request->getPost('remarks') ?? ''));
        $transactionId = trim((string) $this->request->getPost('transaction_id'));

        $violation = $this->violationRecord->find($violationId);

        if (! $violation) {
            return redirect()->to(base_url('penalties'))->with('error', 'Violation not found.');
        }

        if (($violation['status'] ?? '') !== 'Pending') {
            return redirect()->to(base_url('penalties'))->with('error', 'This ticket is not pending payment.');
        }

        if ($this->violationRecord->recordPayment($violationId, $paymentMethod, $remarks, $transactionId)) {
            return redirect()->to(base_url('penalties'))->with('payment_settled', 'Payment has been settled successfully!');
        }

        return redirect()->back()->with('error', 'Failed to record payment.');
    }

    public function history()
    {
        $data = [
            'title' => 'Payment History',
            'payments' => $this->violationRecord->select('violations.*, users.username as officer_name, GROUP_CONCAT(CONCAT(violation_type, "::", penalty_amount) SEPARATOR "||") as concatenated_violations, SUM(penalty_amount) as total_penalty_sum, SUM(points) as total_points_sum')
                                                 ->join('users', 'users.id = violations.officer_id', 'left')
                                                 ->where('violations.status', 'Paid')
                                                 ->groupBy('violations.ticket_id')
                                                 ->orderBy('violations.paid_date', 'DESC')
                                                 ->paginate(10, 'history'),
            'pager' => $this->violationRecord->pager,
        ];
        return view('admin/penalties/history', $data);
    }

    public function reverse($id = null)
    {
        $violation = $this->violationRecord->find($id);

        if (!$violation || $violation['status'] !== 'Paid') {
            return redirect()->to(base_url('penalties/history'))->with('error', 'Violation not found or not in Paid status.');
        }

        $updateData = [
            'status' => 'Pending',
            'paid_date' => null,
            'payment_method' => null,
            'receipt_number' => null
        ];

        if (!empty($violation['ticket_id'])) {
            if ($this->violationRecord->where('ticket_id', $violation['ticket_id'])->set($updateData)->update()) {
                return redirect()->to(base_url('penalties/history'))->with('success', 'Payment has been reversed and ticket is now Pending.');
            }
        } else {
            if ($this->violationRecord->update($id, $updateData)) {
                return redirect()->to(base_url('penalties/history'))->with('success', 'Payment has been reversed and ticket is now Pending.');
            }
        }
        
        return redirect()->to(base_url('penalties/history'))->with('error', 'Failed to reverse payment.');
    }

    public function view($id)
    {
        $violation = $this->violationRecord->getDetailedViolation($id);

        if (!$violation) {
            return redirect()->to(base_url('penalties/all'))->with('error', 'Violation not found.');
        }

        $allViolations = [];
        if (!empty($violation['ticket_id'])) {
            $allViolations = $this->violationRecord
                ->select('violations.*, vt.violation_name')
                ->join('violation_types vt', 'vt.id = violations.violation_type_id', 'left')
                ->where('violations.ticket_id', $violation['ticket_id'])
                ->findAll();
        }

        $data = [
            'title'          => 'Violation Details',
            'violation'      => $violation,
            'all_violations' => $allViolations,
        ];
        return view('admin/penalties/view', $data);
    }

    public function cancel($id)
    {
        $violation = $this->violationRecord->find($id);

        if (!$violation) {
            return redirect()->to(base_url('penalties/all'))->with('error', 'Violation not found.');
        }

        if ($violation['status'] !== 'Pending') {
            return redirect()->to(base_url('penalties/all'))->with('error', 'Only pending violations can be cancelled.');
        }

        $reason = $this->request->getPost('reason') ?? 'Cancelled by admin';

        if ($this->violationRecord->cancelViolation($id, $reason)) {
            return redirect()->to(base_url('penalties/all'))->with('success', 'Violation cancelled successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to cancel violation.');
        }
    }

    public function delete($id = null)
    {
        $violation = $this->violationRecord->find($id);

        if (!$violation) {
            return redirect()->to(base_url('penalties'))->with('error', 'Violation record not found.');
        }

        $violatorId = (int) ($violation['violator_id'] ?? 0);
        $deleted    = false;

        if (!empty($violation['ticket_id'])) {
            $deleted = $this->violationRecord->where('ticket_id', $violation['ticket_id'])->delete();
        } else {
            $deleted = $this->violationRecord->delete($id);
        }

        if ($deleted) {
            $this->violatorModel->pruneIfOrphan($violatorId);

            return redirect()->to(base_url('penalties'))->with('success', 'Violation ticket deleted successfully.');
        }

        return redirect()->back()->with('error', 'Failed to delete violation record.');
    }

    public function search()
    {
        $keyword = $this->request->getGet('q');

        if (empty($keyword)) {
            return redirect()->to(base_url('penalties/all'));
        }

        $data = [
            'title' => 'Search Results',
            'violations' => $this->violationRecord->searchViolations($keyword),
            'keyword' => $keyword,
        ];
        return view('admin/penalties/search', $data);
    }
}
