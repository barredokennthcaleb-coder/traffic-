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
                ->select('violations.*, ' . ViolatorRecord::officerNameSql() . ' as officer_name, GROUP_CONCAT(CONCAT(violation_type, "::", penalty_amount) SEPARATOR "||") as concatenated_violations, SUM(penalty_amount) as total_penalty_sum, SUM(points) as total_points_sum')
                ->join('users', 'users.id = violations.officer_id', 'left')
                ->where('violations.status', 'Pending')
                ->groupBy('violations.ticket_id')
                ->orderBy('violations.violation_date', 'DESC')
                ->paginate(10, 'penalties'),
            'pager' => $this->violationRecord->pager,
        ];
        return view('admin/penalties/index', $data);
    }    /**
     * The older "All Violators" list, kept only as an alias.
     *
     * It paginated ten at a time and dropped fully settled people, so the same
     * registry appeared to hold different numbers depending on the screen you
     * opened it from. Every violator now lives on one page in the directory,
     * which keeps everyone on record and counts exactly what it lists.
     */
    public function all()
    {
        return redirect()->to(base_url('violators'));
    }

    /**
     * Violator dossier with the complete linked ticket history.
     */
    public function violatorProfile($id)
    {
        $profile = $this->violatorModel->getProfile((int) $id);

        if (! $profile) {
            return redirect()->to(base_url('violators'))->with('error', 'Violator record not found.');
        }

        // A record opened from an enforcer profile returns to that profile;
        // from anywhere else it returns to the Violators registry.
        $from       = (string) $this->request->getGet('from');
        $enforcerId = (int) $this->request->getGet('enforcer_id');

        if ($from === 'enforcer' && $enforcerId > 0) {
            $backUrl   = base_url('users/view-enforcer/' . $enforcerId);
            $backLabel = 'Enforcer Profile';
        } else {
            // The payment working list hides people whose fines are settled,
            // so the registry is the safe default back target.
            $backUrl   = base_url('violators');
            $backLabel = 'Violators';
        }

        $data = [
            'title'         => 'Violator Profile - ' . ($profile['violator']['driver_name'] ?: 'Record'),
            'violator'      => $profile['violator'],
            'summary'       => $profile['summary'],
            'tickets'       => $profile['tickets'],
            'vehicles'      => $profile['vehicles'],
            'backUrl'       => $backUrl,
            'backLabel'     => $backLabel,
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

        $nextOrcrNumber = $this->violationRecord->getNextOrcrNumber();
        if ($nextOrcrNumber === null) {
            return redirect()->to(base_url('penalties'))->with('error', 'Official Receipt (OR) numbering is not initialized. Run the database migrations.');
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
            'next_orcr_number' => $nextOrcrNumber,
        ];
        return view('admin/penalties/pay', $data);
    }

    public function store()
    {
        $rules = [
            'violation_id'                 => 'required|integer',
            'payment_method'               => 'required|in_list[Cash,Check,Money Order]',
            'receipt_number'               => 'required|max_length[18]',
            'remarks'                      => 'permit_empty|max_length[500]',
            'collecting_officer_name'      => 'required|max_length[255]',
            'collecting_officer_signature' => 'required|max_length[5000000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $violationId   = (int) $this->request->getPost('violation_id');
        $paymentMethod = (string) $this->request->getPost('payment_method');
        $remarks       = trim((string) ($this->request->getPost('remarks') ?? ''));
        $receiptNumber = trim((string) $this->request->getPost('receipt_number'));

        $collectingOfficerName      = preg_replace('/\s+/', ' ', trim((string) $this->request->getPost('collecting_officer_name')));
        $collectingOfficerSignature = trim((string) $this->request->getPost('collecting_officer_signature'));

        if (! ctype_digit($receiptNumber) || (int) $receiptNumber < 1) {
            return redirect()->back()->withInput()->with('error', 'Enter a valid numeric Official Receipt (OR) number greater than zero.');
        }

        // Only accept an image data URL produced by the signature pad/uploader,
        // never arbitrary posted markup that could be echoed on the receipt.
        if (
            $collectingOfficerSignature === ''
            || preg_match('/^data:image\/(?:png|jpeg|webp);base64,[A-Za-z0-9+\/=\s]+$/', $collectingOfficerSignature) !== 1
        ) {
            return redirect()->back()->withInput()->with('error', 'Please apply the collecting officer e-signature before recording the payment.');
        }

        $violation = $this->violationRecord->find($violationId);

        if (! $violation) {
            return redirect()->to(base_url('penalties'))->with('error', 'Violation not found.');
        }

        if (($violation['status'] ?? '') !== 'Pending') {
            return redirect()->to(base_url('penalties'))->with('error', 'This ticket is not pending payment.');
        }

        if ($this->violationRecord->recordPayment($violationId, $paymentMethod, $remarks, $receiptNumber, $collectingOfficerName, $collectingOfficerSignature)) {
            return redirect()->to(base_url('penalties'))->with('payment_settled', 'Payment has been settled successfully!');
        }

        return redirect()->back()->with('error', 'Failed to record payment. Check that the Official Receipt (OR) number is unused and try again.');
    }

    public function history()
    {
        // Payment timestamps are stored in UTC while the office works in
        // Philippine time: days on this screen are local calendar days and each
        // row's clock time is converted for display.
        $localTz = new \DateTimeZone('Asia/Manila');
        $utcTz   = new \DateTimeZone('UTC');
        $today = new \DateTimeImmutable('today', $localTz);
        $period = trim((string) $this->request->getGet('period'));
        $monthParam = trim((string) $this->request->getGet('month'));
        if ($period === '') {
            $period = $monthParam !== '' ? 'month' : 'year';
        }
        $dateParam = trim((string) $this->request->getGet('date'));
        $filterStart = null;
        $filterEnd = null;
        $periodLabel = null;
        $selectedDate = $today->format('Y-m-d');
        $selectedMonth = $today->format('Y-m');
        $currentYear = (int) $today->format('Y');

        if ($period === 'year') {
            $yearStart = new \DateTimeImmutable(sprintf('%04d-01-01 00:00:00', $currentYear), $localTz);
            $yearEnd = $yearStart->modify('+1 year');
            $filterStart = $yearStart->setTimezone($utcTz)->format('Y-m-d H:i:s');
            $filterEnd = $yearEnd->setTimezone($utcTz)->format('Y-m-d H:i:s');
            $periodLabel = (string) $currentYear;
        } elseif ($period === 'week') {
            $weekDate = $dateParam !== ''
                ? \DateTimeImmutable::createFromFormat('!Y-m-d', $dateParam, $localTz)
                : false;
            $dateErrors = \DateTimeImmutable::getLastErrors();

            if ($weekDate === false || $weekDate->format('Y-m-d') !== $dateParam
                || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) {
                return redirect()->to(base_url('penalties/history'))->with('error', 'Choose a valid date to filter the payment history.');
            }

            $weekStart = $weekDate->modify('monday this week')->setTime(0, 0);
            $weekEnd = $weekStart->modify('+5 days');
            $filterStart = $weekStart->setTimezone($utcTz)->format('Y-m-d H:i:s');
            $filterEnd = $weekEnd->setTimezone($utcTz)->format('Y-m-d H:i:s');
            $selectedDate = $weekDate->format('Y-m-d');
            $periodLabel = $weekStart->format('M d, Y') . ' - ' . $weekEnd->modify('-1 day')->format('M d, Y');
        } elseif ($period === 'month') {
            $monthKey = $monthParam !== '' ? $monthParam : $dateParam;
            $monthDate = preg_match('/^\d{4}-\d{2}$/', $monthKey) === 1
                ? \DateTimeImmutable::createFromFormat('!Y-m-d', $monthKey . '-01', $localTz)
                : false;
            $dateErrors = \DateTimeImmutable::getLastErrors();

            if ($monthDate === false || $monthDate->format('Y-m') !== $monthKey
                || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) {
                return redirect()->to(base_url('penalties/history'))->with('error', 'Choose a valid month to filter the payment history.');
            }

            $monthEnd = $monthDate->modify('+1 month');
            $filterStart = $monthDate->setTimezone($utcTz)->format('Y-m-d H:i:s');
            $filterEnd = $monthEnd->setTimezone($utcTz)->format('Y-m-d H:i:s');
            $selectedMonth = $monthDate->format('Y-m');
            $periodLabel = $monthDate->format('F Y');
            $period = 'month';
        } elseif ($period !== '') {
            return redirect()->to(base_url('penalties/history'))->with('error', 'Choose a valid year, week, or month filter.');
        }

        $summaryQuery = $this->violationRecord
            ->select('COUNT(DISTINCT violations.ticket_id) as total_transactions, IFNULL(SUM(violations.penalty_amount), 0) as total_revenue')
            ->where('violations.status', 'Paid');
        if ($filterStart !== null) {
            $summaryQuery->where('violations.paid_date >=', $filterStart)
                         ->where('violations.paid_date <', $filterEnd);
        }
        $periodStats = $summaryQuery->get()->getRowArray();

        $yearRevenueRow = $this->violationRecord
            ->select('IFNULL(SUM(violations.penalty_amount), 0) as year_revenue')
            ->where('violations.status', 'Paid')
            ->where('violations.paid_date >=', (new \DateTimeImmutable(sprintf('%04d-01-01 00:00:00', $currentYear), $localTz))->setTimezone($utcTz)->format('Y-m-d H:i:s'))
            ->where('violations.paid_date <', (new \DateTimeImmutable(sprintf('%04d-01-01 00:00:00', $currentYear + 1), $localTz))->setTimezone($utcTz)->format('Y-m-d H:i:s'))
            ->get()
            ->getRowArray();

        $paymentsQuery = $this->violationRecord
            ->select('violations.*, ' . ViolatorRecord::officerNameSql() . ' as officer_name, GROUP_CONCAT(CONCAT(violation_type, "::", penalty_amount) SEPARATOR "||") as concatenated_violations, SUM(penalty_amount) as total_penalty_sum, SUM(points) as total_points_sum')
            ->join('users', 'users.id = violations.officer_id', 'left')
            ->where('violations.status', 'Paid');

        if ($filterStart !== null) {
            $paymentsQuery->where('violations.paid_date >=', $filterStart)
                          ->where('violations.paid_date <', $filterEnd);
        }

        $data = [
            'title'          => 'Payment History',
            'payments'       => $paymentsQuery
                ->groupBy('violations.ticket_id')
                ->orderBy('violations.paid_date', 'DESC')
                ->paginate(10, 'history'),
            'pager'          => $this->violationRecord->pager,
            'period_stats'   => [
                'transactions' => (int) ($periodStats['total_transactions'] ?? 0),
                'revenue'      => (float) ($periodStats['total_revenue'] ?? 0),
            ],
            'year_revenue'    => (float) ($yearRevenueRow['year_revenue'] ?? 0),
            'current_year'    => $currentYear,
            'selected_period' => $period,
            'selected_date'   => $selectedDate,
            'selected_month'  => $selectedMonth,
            'period_label'    => $periodLabel,
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
            'status'                       => 'Pending',
            'paid_date'                    => null,
            'payment_method'               => null,
            'receipt_number'               => null,
            'collecting_officer_name'      => null,
            'collecting_officer_signature' => null,
        ];

        if (!empty($violation['ticket_id'])) {
            // Only Paid rows are reversed: Cancelled infractions on the ticket
            // must never be resurrected to Pending.
            if ($this->violationRecord->where('ticket_id', $violation['ticket_id'])->where('status', 'Paid')->set($updateData)->update()) {
                return redirect()->to(base_url('penalties/history'))->with('success', 'Payment has been reversed and ticket is now Pending.');
            }
        } else {
            if ($this->violationRecord->where('id', $id)->where('status', 'Paid')->set($updateData)->update()) {
                return redirect()->to(base_url('penalties/history'))->with('success', 'Payment has been reversed and ticket is now Pending.');
            }
        }
        
        return redirect()->to(base_url('penalties/history'))->with('error', 'Failed to reverse payment.');
    }

    public function receipt($ticketId = null)
    {
        $violation = $this->violationRecord
            ->where('ticket_id', $ticketId)
            ->where('status', 'Paid')
            ->first();

        if (!$violation) {
            return redirect()->to(base_url('penalties/history'))->with('error', 'Paid receipt not found.');
        }

        return view('user/receipt', [
            'title'            => 'Official Receipt',
            'violation'        => $violation,
            'infractions'      => $this->violationRecord->getTicketInfractions($violation['ticket_id']),
            'is_admin_receipt' => true,
            'auto_print'       => $this->request->getGet('print') === '1',
        ]);
    }

    public function updateReceiptNumber($id = null)
    {
        $postedReceiptNumber = $this->request->getPost('receipt_number');
        $receiptNumber = is_string($postedReceiptNumber) ? trim($postedReceiptNumber) : '';
        if ($receiptNumber === '' || mb_strlen($receiptNumber) > 50) {
            return redirect()->to(base_url('penalties/history'))->with('error', 'Enter a valid Official Receipt (OR) number (up to 50 characters).');
        }

        $violation = $this->violationRecord->find($id);
        if (!$violation || ($violation['status'] ?? '') !== 'Paid') {
            return redirect()->to(base_url('penalties/history'))->with('error', 'Paid citation ticket not found.');
        }

        $ticketId = trim((string) ($violation['ticket_id'] ?? ''));
        if ($ticketId === '') {
            return redirect()->to(base_url('penalties/history'))->with('error', 'This payment is not linked to a citation ticket.');
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $lockSuffix = strcasecmp($db->DBDriver, 'MySQLi') === 0 ? ' FOR UPDATE' : '';
            $sequence = $db->query(
                'SELECT last_number, number_width FROM orcr_sequences WHERE id = 1' . $lockSuffix
            )->getRowArray();
            if (! $sequence) {
                $db->transRollback();
                return redirect()->to(base_url('penalties/history'))->with('error', 'Official Receipt (OR) sequence is not initialized. Run the database migrations.');
            }

            $duplicateQuery = $db->table('violations')
                ->where('status', 'Paid')
                ->where('ticket_id !=', $ticketId);
            if (ctype_digit($receiptNumber)) {
                $numericReceiptColumn = strcasecmp($db->DBDriver, 'MySQLi') === 0
                    ? 'CAST(receipt_number AS UNSIGNED)'
                    : 'CAST(receipt_number AS INTEGER)';
                $duplicateQuery->where($numericReceiptColumn . ' =', (int) $receiptNumber, false);
            } else {
                $duplicateQuery->where('receipt_number', $receiptNumber);
            }
            if ($duplicateQuery->countAllResults() > 0) {
                $db->transRollback();
                return redirect()->to(base_url('penalties/history'))->with('error', 'That Official Receipt (OR) number is already assigned to another citation.');
            }

            $updated = (new ViolatorRecord())
                ->where('ticket_id', $ticketId)
                ->where('status', 'Paid')
                ->set('receipt_number', $receiptNumber)
                ->update();

            if (ctype_digit($receiptNumber)) {
                $editedNumber = (int) $receiptNumber;
                $lastNumber = (int) $sequence['last_number'];
                $numberWidth = max((int) $sequence['number_width'], strlen($receiptNumber));
                if ($editedNumber > $lastNumber || $numberWidth > (int) $sequence['number_width']) {
                    $db->table('orcr_sequences')->where('id', 1)->update([
                        'last_number'  => max($lastNumber, $editedNumber),
                        'number_width' => $numberWidth,
                        'updated_at'   => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            if ($updated && $db->transStatus()) {
                $db->transCommit();
                return redirect()->to(base_url('penalties/history'))->with('success', 'Official Receipt (OR) number updated for this citation ticket.');
            }

            $db->transRollback();
            return redirect()->to(base_url('penalties/history'))->with('error', 'Failed to update the Official Receipt (OR) number.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Official Receipt (OR) update failed for ticket {ticket}: {message}', [
                'ticket'  => $ticketId,
                'message' => $e->getMessage(),
            ]);
            return redirect()->to(base_url('penalties/history'))->with('error', 'Could not update the Official Receipt (OR) number due to a database error.');
        }
    }

    public function view($id)
    {
        $violation = $this->violationRecord->getDetailedViolation($id);

        if (!$violation) {
            return redirect()->to(base_url('violators'))->with('error', 'Violation not found.');
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
            return redirect()->to(base_url('violators'))->with('error', 'Violation not found.');
        }

        if ($violation['status'] !== 'Pending') {
            return redirect()->to(base_url('violators'))->with('error', 'Only pending violations can be cancelled.');
        }

        $reason = $this->request->getPost('reason');
        $reason = is_string($reason) ? trim($reason) : '';
        $reason = mb_substr($reason !== '' ? $reason : 'Cancelled by admin', 0, 500);

        if ($this->violationRecord->cancelViolation($id, $reason)) {
            return redirect()->to(base_url('violators'))->with('success', 'Violation cancelled successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to cancel violation.');
        }
    }

    public function delete($id = null)
    {
        $returnToHistory = $this->request->getPost('return_to_history') === '1';
        $redirectUrl = base_url($returnToHistory ? 'penalties/history' : 'penalties');
        $violation = $this->violationRecord->find($id);

        if (!$violation) {
            return redirect()->to($redirectUrl)->with('error', 'Violation record not found.');
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

            return redirect()->to($redirectUrl)->with('success', 'Violation ticket deleted successfully.');
        }

        return redirect()->to($redirectUrl)->with('error', 'Failed to delete violation record.');
    }

    public function search()
    {
        $keyword = $this->request->getGet('q');

        if (empty($keyword)) {
            return redirect()->to(base_url('violators'));
        }

        $data = [
            'title' => 'Search Results',
            'violations' => $this->violationRecord->searchViolations($keyword),
            'keyword' => $keyword,
        ];
        return view('admin/penalties/search', $data);
    }
}
