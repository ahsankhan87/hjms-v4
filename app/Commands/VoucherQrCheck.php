<?php

namespace App\Commands;

use App\Libraries\VoucherShareService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Diagnoses the voucher QR code on a deployed server.
 *
 * Usage: php spark voucher:qr-check [bookingId]
 */
class VoucherQrCheck extends BaseCommand
{
    protected $group       = 'Voucher';
    protected $name        = 'voucher:qr-check';
    protected $description = 'Checks the voucher QR code setup (library, share table, public URL).';
    protected $usage       = 'voucher:qr-check [bookingId]';

    public function run(array $params)
    {
        $bookingId = (int) ($params[0] ?? 0);
        $service   = new VoucherShareService();
        $ok        = true;

        $this->line('PHP version      ', PHP_VERSION);
        $this->flag('ext-mbstring', extension_loaded('mbstring'));
        $this->flag('ext-json', extension_loaded('json'));
        $this->flag('ext-gd (optional)', extension_loaded('gd'));

        $library = $service->qrLibraryAvailable();
        $this->flag('QR library autoloadable', $library, 'copy vendor/chillerlan/* and vendor/composer/*.php, or run composer install');
        $this->line('  QRCode class     ', $library ? class_exists(\chillerlan\QRCode\QRCode::class) ? 'found' : 'missing' : 'not loaded');
        $this->line('  QROptions class  ', $library ? class_exists(\chillerlan\QRCode\QROptions::class) ? 'found' : 'missing' : 'not loaded');

        $tableExists = false;
        try {
            $tableExists = db_connect()->tableExists('voucher_shares');
        } catch (\Throwable $e) {
            $this->line('  DB error         ', $e->getMessage());
        }
        $this->flag('voucher_shares table', $tableExists, 'run php spark migrate');

        if ($bookingId < 1) {
            $this->line('', 'Pass a booking id to test the token and QR payload: php spark voucher:qr-check 123');

            return $ok && $library && $tableExists ? EXIT_SUCCESS : EXIT_ERROR;
        }

        $token = $service->tokenForBooking($bookingId);
        $this->flag('share token for #' . $bookingId, $token !== '');
        if ($token !== '') {
            $this->line('  token            ', $token);
        }

        $url = $service->urlForBooking($bookingId);
        $this->flag('public URL', $url !== '');
        if ($url !== '') {
            $this->line('  url              ', $url);
        }

        $qr = $service->qrDataUri($url);
        $this->flag('QR data URI', $qr !== '');
        if ($qr !== '') {
            $this->line('  qr size          ', strlen($qr) . ' bytes');
            $this->line('  qr prefix        ', substr($qr, 0, 40) . '...');
        }

        return ($library && $tableExists && $token !== '' && $qr !== '') ? EXIT_SUCCESS : EXIT_ERROR;
    }

    private function line(string $label, string $value): void
    {
        CLI::write(str_pad($label, 28) . ': ' . $value);
    }

    private function flag(string $label, bool $ok, string $hint = ''): void
    {
        CLI::write(
            str_pad($label, 28) . ': ' . ($ok ? 'OK' : 'FAIL') . ($ok || $hint === '' ? '' : ' -> ' . $hint),
            $ok ? 'green' : 'red'
        );
    }
}
