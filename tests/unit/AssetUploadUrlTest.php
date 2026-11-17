<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AssetUploadUrlTest extends CIUnitTestCase
{
    public function testRebuildsStoredAbsoluteUrlAgainstCurrentBaseUrl(): void
    {
        $stored = 'http://localhost:9999/old-folder/assets/uploads/hotels/hotel-1.webp';

        $this->assertSame(base_url('assets/uploads/hotels/hotel-1.webp'), asset_upload_url($stored, 'hotels'));
    }

    public function testKeepsExternalUrlUntouched(): void
    {
        $stored = 'https://cdn.example.com/photos/hotel.jpg';

        $this->assertSame($stored, asset_upload_url($stored, 'hotels'));
    }

    public function testResolvesRelativeUploadPath(): void
    {
        $this->assertSame(
            base_url('assets/uploads/hotels/hotel-2.jpg'),
            asset_upload_url('assets/uploads/hotels/hotel-2.jpg', 'hotels')
        );
    }

    public function testResolvesBareFileNameInsideSubdirectory(): void
    {
        $this->assertSame(
            base_url('assets/uploads/hotels/hotel-3.jpg'),
            asset_upload_url('hotel-3.jpg', 'hotels')
        );
    }

    public function testEmptyValueStaysEmpty(): void
    {
        $this->assertSame('', asset_upload_url('', 'hotels'));
    }
}
