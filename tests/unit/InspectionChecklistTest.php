<?php

use App\Libraries\InspectionChecklist;
use CodeIgniter\Test\CIUnitTestCase;

final class InspectionChecklistTest extends CIUnitTestCase
{
    public function testMissingChecklistFieldsAreStoredAsUnchecked(): void
    {
        $data = InspectionChecklist::normalize(['operator_name' => 'Driver']);

        $this->assertSame('Driver', $data['operator_name']);
        $this->assertSame(0, $data['cert_attendance']);
        $this->assertSame(0, $data['tarifa']);
    }

    public function testPresentChecklistFieldsAreStoredAsChecked(): void
    {
        $data = InspectionChecklist::normalize([
            'outside_route' => '1',
            'horn'          => 'on',
        ]);

        $this->assertSame(1, $data['outside_route']);
        $this->assertSame(1, $data['horn']);
        $this->assertSame(0, $data['tarifa']);
    }
}
