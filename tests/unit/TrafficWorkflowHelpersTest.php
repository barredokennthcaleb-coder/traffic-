<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class TrafficWorkflowHelpersTest extends CIUnitTestCase
{
    public function testNormalizesSerialOnlyTicketNumbersUsingTheSelectedYear(): void
    {
        $this->assertSame('2026-0000123', normalize_tct_number('123', 2026));
    }

    public function testNormalizesTicketNumbersWithAYearPrefix(): void
    {
        $this->assertSame('2024-0030268', normalize_tct_number('2024-30268'));
    }

    public function testRejectsMalformedTicketNumbers(): void
    {
        $this->assertNull(normalize_tct_number('2024/30268'));
        $this->assertNull(normalize_tct_number('12345678', 2026));
        $this->assertNull(normalize_tct_number('', 2026));
    }

    public function testUsesCorrectSanctionOrdinalForTeenCounts(): void
    {
        $this->assertSame('11th Sanction', sanction_label(11));
        $this->assertSame('12th Sanction', sanction_label(12));
        $this->assertSame('13th Sanction', sanction_label(13));
    }

    public function testUsesCorrectSanctionOrdinalForOtherCounts(): void
    {
        $this->assertSame('1st Sanction', sanction_label(1));
        $this->assertSame('2nd Sanction', sanction_label(2));
        $this->assertSame('3rd Sanction', sanction_label(3));
        $this->assertSame('21st Sanction', sanction_label(21));
        $this->assertSame('No Sanction Record', sanction_label(0));
    }

    public function testTricycleIconRendersInlineSvgThatInheritsTextColor(): void
    {
        $icon = tricycle_icon();

        $this->assertStringContainsString('<svg', $icon);
        $this->assertStringContainsString('viewBox="0 0 16 16"', $icon);
        $this->assertStringContainsString('fill="currentColor"', $icon);
        $this->assertStringContainsString('class="tricycle-icon"', $icon);
        $this->assertStringContainsString('aria-hidden="true"', $icon);
    }

    public function testTricycleIconCarriesEscapedSpacingClasses(): void
    {
        $this->assertStringContainsString('class="tricycle-icon me-1"', tricycle_icon('me-1'));

        // The optional class is escaped, so a crafted value can never break
        // out of the attribute even if a future caller feeds it user input.
        $this->assertStringNotContainsString('"><script', tricycle_icon('"><script'));
        $this->assertStringContainsString('&quot;&gt;&lt;script', tricycle_icon('"><script'));
    }
}
