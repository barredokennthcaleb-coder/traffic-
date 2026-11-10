        <div class="report-header">
            <div class="report-header-seal">
                <img src="<?= base_url('img/pic-1.png') ?>" alt="City Logo" class="report-seal-img">
            </div>
            <div class="report-header-title">
                <div class="rep-sub">Republic of the Philippines</div>
                <div class="rep-office">OFFICE OF THE CITY MAYOR</div>
                <div class="rep-city">Kabankalan City, Negros Occidental</div>
                <div class="rep-dept">CITY TRAFFIC MANAGEMENT &amp; TRANSPORT REGULATION OFFICE (CTRAMO)</div>
                <h2 class="rep-main-title">VIOLATOR APPREHENSION &amp; SANCTION REPORT</h2>
                <div class="rep-period">
                    <?= esc($modeLabel) ?>: <strong><?= esc($periodLabel) ?></strong>
                </div>
            </div>
        </div>

        <div class="report-meta-bar d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div><strong>Generated:</strong> <?= date('F d, Y h:i A') ?></div>
            <div><strong>Prepared By:</strong> System Admin (<?= esc(session()->get('username') ?? 'Admin') ?>)</div>
        </div>

        <!-- ═══ Violator identity ═══ -->
        <div class="vpr-idblock">
            <div class="vpr-id-name">
                <span class="vpr-id-avatar" aria-hidden="true"><?= esc(mb_strtoupper(mb_substr($fullName, 0, 1))) ?></span>
                <div>
                    <div class="vpr-id-fullname"><?= esc($fullName) ?></div>
                    <div class="vpr-id-sub">
                        <?= $license !== '' ? 'License ' . esc($license) : 'No license on file' ?>
                        <span class="vpr-dot">&middot;</span>
                        <?= $plate !== '' ? 'Plate ' . esc(strtoupper($plate)) : 'No plate on file' ?>
                        <?php if ($vehicle !== ''): ?>
                            <span class="vpr-dot">&middot;</span><?= esc($vehicle) ?>
                        <?php endif; ?>
                        <?php if ($mtop !== ''): ?>
                            <span class="vpr-dot">&middot;</span>MTOP <?= esc(strtoupper($mtop)) ?>
                        <?php endif; ?>
                    </div>
                    <?php if ($address !== ''): ?>
                        <div class="vpr-id-sub"><?= esc($address) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="vpr-standing vpr-standing--<?= esc($overallTone) ?>">
                <span class="vpr-standing-label">Standing</span>
                <span class="vpr-standing-value">
                    <?= $overallTone === 'paid' ? 'All citations settled' : ($overallTone === 'cancelled' ? 'Citations cancelled' : 'Pending settlement') ?>
                </span>
            </div>
        </div>

        <!-- ═══ Key figures ═══ -->
        <div class="vsum-kpi-grid">
            <div class="vsum-kpi">
                <div class="vsum-kpi-label">Apprehensions</div>
                <div class="vsum-kpi-value"><?= number_format($attempts) ?></div>
                <div class="vsum-kpi-sub">citations issued to this person</div>
            </div>
            <div class="vsum-kpi">
                <div class="vsum-kpi-label">Offense Entries</div>
                <div class="vsum-kpi-value"><?= number_format($entriesCount) ?></div>
                <div class="vsum-kpi-sub">
                    <?= number_format($entriesCount) ?> entries across <?= number_format($attempts) ?> citation<?= $attempts === 1 ? '' : 's' ?>
                </div>
            </div>
            <div class="vsum-kpi">
                <div class="vsum-kpi-label">Assessed</div>
                <div class="vsum-kpi-value">&#8369;<?= number_format($assessed, 2) ?></div>
                <div class="vsum-kpi-sub">paid plus still due; voided entries excluded</div>
            </div>
            <div class="vsum-kpi">
                <div class="vsum-kpi-label">Outstanding</div>
                <div class="vsum-kpi-value">&#8369;<?= number_format($balance, 2) ?></div>
                <div class="vsum-kpi-sub"><?= number_format($pendingEntries) ?> pending entr<?= $pendingEntries === 1 ? 'y' : 'ies' ?></div>
            </div>
        </div>

        <div class="vsum-recon">
            <i class="bi bi-link-45deg"></i>
            <span>
                <strong><?= number_format($entriesCount) ?> offense entries</strong>
                on <strong><?= number_format($attempts) ?> citation<?= $attempts === 1 ? '' : 's' ?></strong>.
                Sanction status:
                <strong><?= number_format($paidEntries) ?> paid</strong>,
                <strong><?= number_format($pendingEntries) ?> pending</strong><?php if ($cancelledEntries > 0): ?>,
                <strong><?= number_format($cancelledEntries) ?> cancelled (void)</strong><?php endif; ?>.
                Demerit points on record: <strong><?= number_format($points) ?></strong>.
            </span>
        </div>

        <!-- ═══ I. The tally grid ═══
             The office's own apprehension sheet: months down the rows, the days
             of the month across, one mark per citation recorded. -->
        <div class="report-section">
            <h5 class="report-sec-title">
                <i class="bi bi-table me-2"></i>I. Daily Traffic Apprehension Record
            </h5>
            <p class="vsum-sec-note">
                <?= $mode === 'daily' ? esc($month_names[$month] . ' ' . $year) : 'Calendar Year ' . (int) $year ?>
                &middot; one mark per apprehension (citation) recorded on each day &mdash;
                this sheet counts <strong>tickets, not offense entries</strong>.
            </p>

            <div class="table-responsive vsum-matrix-scroll">
                <table class="table table-bordered table-sm report-table vpr-matrix">
                    <thead>
                        <tr>
                            <th class="vpr-mx-name">MONTH</th>
                            <?php for ($day = 1; $day <= 31; $day++): ?>
                                <th class="vpr-mx-day"><?= $day ?></th>
                            <?php endfor; ?>
                            <th class="vpr-mx-total">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $matrixMonths = $mode === 'daily' ? [$month] : range(1, 12);
                        $dayFooter    = [];

                        foreach ($matrixMonths as $mNo):
                            $monthMarks = $calendar[$mNo] ?? [];
                            $monthTotal = (int) ($calendar_totals[$mNo] ?? 0);
                        ?>
                            <tr class="<?= $monthMarks === [] ? 'vpr-mx-idle' : '' ?>">
                                <td class="vpr-mx-name">
                                    <?= esc($mode === 'daily' ? $month_names[$mNo] : mb_substr($month_names[$mNo], 0, 3)) ?>
                                </td>
                                <?php for ($day = 1; $day <= 31; $day++): ?>
                                    <?php
                                    $marks = (int) ($monthMarks[$day] ?? 0);
                                    if ($marks > 0) {
                                        $dayFooter[$day] = ($dayFooter[$day] ?? 0) + $marks;
                                    }
                                    ?>
                                    <td class="vpr-mx-day<?= $marks > 0 ? ' vpr-mx-hit' : '' ?>"><?= $marks > 0 ? $marks : '' ?></td>
                                <?php endfor; ?>
                                <td class="vpr-mx-total"><?= $monthTotal > 0 ? $monthTotal : '&mdash;' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="report-total-row vpr-mx-foot">
                            <td class="vpr-mx-name">TOTAL</td>
                            <?php for ($day = 1; $day <= 31; $day++): ?>
                                <td class="vpr-mx-day"><?= (int) ($dayFooter[$day] ?? 0) ?: '' ?></td>
                            <?php endfor; ?>
                            <td class="vpr-mx-total"><?= number_format($mode === 'daily' ? (int) ($calendar_totals[$month] ?? 0) : $attempts) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ═══ II. Period apprehension detail ═══ -->
        <div class="report-section">
            <h5 class="report-sec-title">
                <i class="bi bi-clock-history me-2"></i>II. <?= esc($modeLabel) ?>
            </h5>
            <p class="vsum-sec-note">
                <?= esc($periodLabel) ?> &middot;
                <?= $mode === 'daily'
                    ? 'every apprehension recorded in this month, with the exact time and the place it happened'
                    : 'every month of this year that carries apprehensions, with where and when they happened' ?>.
                One apprehension is one citation, even when it carries several offenses.
            </p>

            <div class="table-responsive report-table-scroll">
            <?php if ($period_rows === []): ?>
                <div class="vpr-empty">
                    <i class="bi bi-calendar-x"></i>
                    No apprehensions recorded for <?= esc($periodLabel) ?>.
                </div>
            <?php elseif ($mode === 'daily'): ?>
                <table class="table table-bordered table-sm report-table vpr-daily-table">
                    <colgroup>
                        <col><col><col><col><col><col><col><col><col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Ticket No.</th>
                            <th>Violation(s)</th>
                            <th>Where Committed</th>
                            <th>Apprehending Officer</th>
                            <th class="text-end">Fine</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $n = 1; ?>
                        <?php foreach ($period_rows as $row): ?>
                            <tr>
                                <td class="text-center"><?= $n++ ?></td>
                                <td><?= esc($row['date']) ?></td>
                                <td><?= esc($row['time']) ?></td>
                                <td class="vpr-mono"><?= esc($row['ticket_id']) ?></td>
                                <td><?= esc(implode('; ', $row['offenses'])) ?></td>
                                <td class="<?= $row['has_location'] ? '' : 'vpr-none' ?>"><?= esc($row['location']) ?></td>
                                <td><?= esc($row['officer']) ?></td>
                                <td class="text-end">&#8369;<?= number_format((float) $row['amount'], 2) ?></td>
                                <td class="text-center">
                                    <span class="vpr-pill vpr-pill--<?= strtolower($row['status']) ?>"><?= esc($row['status']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="report-total-row">
                            <td colspan="4">TOTAL FOR <?= esc(mb_strtoupper($periodLabel)) ?></td>
                            <td colspan="3"><?= number_format($periodApprehensions) ?> apprehension<?= $periodApprehensions === 1 ? '' : 's' ?> &middot; <?= number_format($periodEntries) ?> offense entr<?= $periodEntries === 1 ? 'y' : 'ies' ?></td>
                            <td class="text-end">&#8369;<?= number_format($periodAssessed, 2) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <table class="table table-bordered table-sm report-table vpr-month-table">
                    <colgroup>
                        <col><col><col><col><col><col><col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-center">Apprehensions</th>
                            <th class="text-center">Offenses</th>
                            <th>Where Committed</th>
                            <th>Time(s)</th>
                            <th class="text-end">Assessed</th>
                            <th class="text-end">Outstanding</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($period_rows as $row): ?>
                            <tr>
                                <td class="fw-bold"><?= esc($row['short_label']) ?></td>
                                <td class="text-center"><?= number_format((int) $row['apprehensions']) ?></td>
                                <td class="text-center"><?= number_format((int) $row['entries']) ?></td>
                                <td><?= esc($row['locations']) ?></td>
                                <td><?= esc($row['times']) ?></td>
                                <td class="text-end">&#8369;<?= number_format((float) $row['assessed'], 2) ?></td>
                                <td class="text-end">&#8369;<?= number_format((float) $row['pending'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="report-total-row">
                            <td>TOTAL <?= (int) $year ?></td>
                            <td class="text-center"><?= number_format($periodApprehensions) ?></td>
                            <td class="text-center"><?= number_format($periodEntries) ?></td>
                            <td colspan="2"><?= esc($periodLabel) ?></td>
                            <td class="text-end">&#8369;<?= number_format($periodAssessed, 2) ?></td>
                            <td class="text-end">&#8369;<?= number_format($periodPending, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
            </div>
        </div>

        <!-- ═══ II. Every violation on record, with its sanction status ═══ -->
        <div class="report-section">
            <h5 class="report-sec-title">
                <i class="bi bi-list-check me-2"></i>III. Violations on Record &amp; Sanction Status
            </h5>
            <p class="vsum-sec-note">
                Every infraction ever recorded for this person, newest first &mdash; settled and
                voided ones included, because paying a fine clears the balance, not the record.
            </p>

            <div class="table-responsive report-table-scroll">
            <?php if ($infractions === []): ?>
                <div class="vpr-empty"><i class="bi bi-inbox"></i> No violations on record.</div>
            <?php else: ?>
                <table class="table table-bordered table-sm report-table vpr-record-table">
                    <colgroup>
                        <col><col><col><col><col><col><col><col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>Date &amp; Time</th>
                            <th>Ticket No.</th>
                            <th>Violation</th>
                            <th>Where Committed</th>
                            <th>Officer</th>
                            <th class="text-end">Fine</th>
                            <th class="text-center">Sanction</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $n = 1; ?>
                        <?php foreach ($infractions as $row): ?>
                            <tr>
                                <td class="text-center"><?= $n++ ?></td>
                                <td>
                                    <?= esc($row['date']) ?><span class="vpr-time"><?= esc($row['time']) ?></span>
                                </td>
                                <td class="vpr-mono"><?= esc($row['ticket_id']) ?></td>
                                <td><?= esc($row['type']) ?></td>
                                <td class="<?= $row['location'] === '' ? 'vpr-none' : '' ?>">
                                    <?= esc($row['location'] !== '' ? $row['location'] : 'Not recorded') ?>
                                </td>
                                <td><?= esc($row['officer'] !== '' ? $row['officer'] : 'Not recorded') ?></td>
                                <td class="text-end">&#8369;<?= number_format((float) $row['amount'], 2) ?></td>
                                <td class="text-center">
                                    <span class="vpr-pill vpr-pill--<?= strtolower($row['status']) ?>"><?= esc($row['status']) ?></span>
                                    <?php if ($row['status'] === 'Paid' && ($row['receipt_number'] !== '' || $row['paid_date'] !== '')): ?>
                                        <span class="vpr-pill-note">
                                            <?= $row['receipt_number'] !== '' ? 'OR ' . esc($row['receipt_number']) : '' ?>
                                            <?= $row['paid_date'] !== '' ? '&middot; ' . esc(date('M d, Y', strtotime($row['paid_date']))) : '' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="report-total-row">
                            <td colspan="4">TOTAL &mdash; <?= number_format($entriesCount) ?> offense entr<?= $entriesCount === 1 ? 'y' : 'ies' ?></td>
                            <td colspan="2"></td>
                            <td class="text-end">&#8369;<?= number_format($assessed, 2) ?></td>
                            <td class="text-center"><?= number_format($paidEntries) ?> / <?= number_format($entriesCount) ?> settled</td>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
            </div>
        </div>

        <!-- ═══ III. Sanction status summary ═══ -->
        <div class="report-section">
            <h5 class="report-sec-title">
                <i class="bi bi-shield-check me-2"></i>IV. Sanction Status Summary
            </h5>

            <div class="table-responsive report-table-scroll">
                <table class="table table-bordered table-sm report-table vpr-sanction-table">
                    <colgroup>
                        <col><col><col><col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th class="text-center">Entries</th>
                            <th class="text-end">Amount</th>
                            <th>Meaning</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="vpr-pill vpr-pill--paid">Paid</span></td>
                            <td class="text-center"><?= number_format($status_ledger['Paid']['entries']) ?></td>
                            <td class="text-end">&#8369;<?= number_format((float) $status_ledger['Paid']['amount'], 2) ?></td>
                            <td>Sanction served &mdash; settled at the cashier.</td>
                        </tr>
                        <tr>
                            <td><span class="vpr-pill vpr-pill--pending">Pending</span></td>
                            <td class="text-center"><?= number_format($status_ledger['Pending']['entries']) ?></td>
                            <td class="text-end">&#8369;<?= number_format((float) $status_ledger['Pending']['amount'], 2) ?></td>
                            <td>Sanction still due &mdash; the person has an unsettled citation.</td>
                        </tr>
                        <tr>
                            <td><span class="vpr-pill vpr-pill--cancelled">Cancelled</span></td>
                            <td class="text-center"><?= number_format($status_ledger['Cancelled']['entries']) ?></td>
                            <td class="text-end">&#8369;<?= number_format((float) $status_ledger['Cancelled']['amount'], 2) ?></td>
                            <td>Voided citation &mdash; carries no fine and no demerit points.</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="report-total-row">
                            <td>TOTAL</td>
                            <td class="text-center"><?= number_format($entriesCount) ?></td>
                            <td class="text-end">&#8369;<?= number_format($assessed, 2) ?></td>
                            <td>Assessed total: paid &#8369;<?= number_format($paidTotal, 2) ?> + outstanding &#8369;<?= number_format($balance, 2) ?>.</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if ($place_counts !== []): ?>
                <p class="vsum-sec-note">
                    <strong>Where this person was apprehended:</strong>
                    <?php $places = []; ?>
                    <?php foreach ($place_counts as $place => $count): ?>
                        <?php $places[] = esc($place) . ' (' . $count . ')'; ?>
                    <?php endforeach; ?>
                    <?= implode('; ', $places) ?>.
                </p>
            <?php endif; ?>

            <p class="vsum-sec-note">
                <strong>Demerit points:</strong> <?= number_format($points) ?> on record
                (voided entries excluded). Outstanding sanction:
                <strong>&#8369;<?= number_format($balance, 2) ?></strong>
                across <?= number_format($pendingEntries) ?> pending entr<?= $pendingEntries === 1 ? 'y' : 'ies' ?>.
            </p>
        </div>

        <!-- ═══ Sign-off ═══ -->
        <div class="report-sign-block">
            <div class="row">
                <div class="col-6">
                    <div class="sig-container">
                        <div class="sig-line"></div>
                        <div class="fw-bold">Prepared By</div>
                        <div class="text-muted small">CTRAMO Records Officer</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="sig-container">
                        <div class="sig-line"></div>
                        <div class="fw-bold">Verified By</div>
                        <div class="text-muted small">City Traffic Management Officer</div>
                    </div>
                </div>
            </div>
        </div>

