<?php

namespace App\Libraries;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * Issues and resolves the public share tokens printed as QR codes on vouchers.
 *
 * A token is created the first time an authorised user opens a final voucher and
 * is deliberately opaque: it carries no booking data and can be revoked by
 * blanking `revoked_at` handling in `revoke()`.
 */
class VoucherShareService
{
    private const TOKEN_PATTERN = '/^[a-f0-9]{48}$/';

    /**
     * Returns the existing token for the booking or issues a new one.
     */
    public function tokenForBooking(int $bookingId): string
    {
        if ($bookingId < 1) {
            return '';
        }

        if (! $this->tableReady()) {
            log_message('error', 'Voucher QR skipped: the "voucher_shares" table is missing. Run "php spark migrate".');

            return '';
        }

        $db    = db_connect();
        $row   = $db->table('voucher_shares')->where('booking_id', $bookingId)->get()->getRowArray();
        $token = trim((string) ($row['token'] ?? ''));

        if (preg_match(self::TOKEN_PATTERN, $token) === 1 && trim((string) ($row['revoked_at'] ?? '')) === '') {
            return $token;
        }

        $token = bin2hex(random_bytes(24));
        $now   = date('Y-m-d H:i:s');

        if ($row === null) {
            $db->table('voucher_shares')->insert([
                'booking_id' => $bookingId,
                'token'      => $token,
                'created_at' => $now,
            ]);

            return $token;
        }

        $db->table('voucher_shares')->where('booking_id', $bookingId)->update([
            'token'      => $token,
            'created_at' => $now,
            'revoked_at' => null,
        ]);

        return $token;
    }

    public function bookingIdForToken(string $token): ?int
    {
        $token = trim($token);

        if (preg_match(self::TOKEN_PATTERN, $token) !== 1 || ! $this->tableReady()) {
            return null;
        }

        $row = db_connect()->table('voucher_shares')
            ->select('booking_id, revoked_at')
            ->where('token', $token)
            ->get()
            ->getRowArray();

        if ($row === null || trim((string) ($row['revoked_at'] ?? '')) !== '') {
            return null;
        }

        return (int) $row['booking_id'];
    }

    /**
     * Absolute, login-free URL for the QR code.
     */
    public function urlForBooking(int $bookingId): string
    {
        $token = $this->tokenForBooking($bookingId);

        return $token === '' ? '' : site_url('v/' . $token);
    }

    public function revoke(int $bookingId): void
    {
        if ($bookingId < 1 || ! $this->tableReady()) {
            return;
        }

        db_connect()->table('voucher_shares')
            ->where('booking_id', $bookingId)
            ->update(['revoked_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Inline SVG data URI so the code prints without any extra request.
     */
    public function qrDataUri(string $payload): string
    {
        if ($payload === '') {
            return '';
        }

        if (! $this->qrLibraryAvailable()) {
            log_message('error', 'Voucher QR skipped: chillerlan/php-qrcode classes are not autoloadable. Copy vendor/chillerlan (php-qrcode + php-settings-container) and vendor/composer/*.php, or run "composer install".');

            return '';
        }

        try {
            $options = new QROptions([
                'eccLevel'             => 'L',
                'quietzoneSize'        => 2,
                'svgUseFillAttributes' => true,
                'outputBase64'         => true,
            ]);

            return (string) (new QRCode($options))->render($payload);
        } catch (\Throwable $e) {
            log_message('error', 'Voucher QR generation failed: ' . $e->getMessage());

            return '';
        }
    }

    /**
     * True when the QR library can be used, registering a PSR-4 fallback for
     * installs where the files were copied but the Composer maps were not.
     */
    public function qrLibraryAvailable(): bool
    {
        if (class_exists(QRCode::class) && class_exists(QROptions::class)) {
            return true;
        }

        if (! defined('ROOTPATH')) {
            return false;
        }

        $namespaces = [
            'chillerlan\\QRCode\\'   => ROOTPATH . 'vendor/chillerlan/php-qrcode/src/',
            'chillerlan\\Settings\\' => ROOTPATH . 'vendor/chillerlan/php-settings-container/src/',
        ];

        foreach ($namespaces as $prefix => $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            spl_autoload_register(static function (string $class) use ($prefix, $directory): void {
                if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
                    return;
                }

                $file = $directory . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

                if (is_file($file)) {
                    require_once $file;
                }
            });
        }

        return class_exists(QRCode::class) && class_exists(QROptions::class);
    }

    public function qrDataUriForBooking(int $bookingId): string
    {
        return $this->qrDataUri($this->urlForBooking($bookingId));
    }

    private function tableReady(): bool
    {
        try {
            return db_connect()->tableExists('voucher_shares');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
