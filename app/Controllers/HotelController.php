<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\HotelModel;
use App\Models\HotelRoomModel;

class HotelController extends BaseController
{
    public function index(): string
    {
        $db = db_connect();

        $rows = $db->table('hotels h')
            ->select('h.*, COUNT(hr.id) AS room_types, COALESCE(SUM(hr.total_rooms),0) AS total_rooms, COALESCE(SUM(hr.allocated_rooms),0) AS allocated_rooms')
            ->join('hotel_rooms hr', 'hr.hotel_id = h.id', 'left')
            ->groupBy('h.id')
            ->orderBy('h.id', 'DESC')
            ->get()
            ->getResultArray();

        return view('portal/hotels/index', [
            'title'       => 'HJMS ERP | Hotels',
            'headerTitle' => 'Hotel Management',
            'activePage'  => 'hotels',
            'userEmail'   => (string) session('user_email'),
            'rows'        => $rows,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function add(): string
    {
        return view('portal/hotels/add', [
            'title'       => 'HJMS ERP | Add Hotel',
            'headerTitle' => 'Hotel Management',
            'activePage'  => 'hotels',
            'userEmail'   => (string) session('user_email'),
            'defaultRoomTypes' => $this->defaultRoomTypes(),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function edit(int $id)
    {
        $hotelModel = new HotelModel();
        $roomModel = new HotelRoomModel();

        $row = $hotelModel->find($id);
        if (empty($row)) {
            return redirect()->to('/hotels')->with('error', 'Hotel not found.');
        }

        $rooms = $roomModel->where('hotel_id', $id)->orderBy('id', 'DESC')->findAll();

        return view('portal/hotels/edit', [
            'title'       => 'HJMS ERP | Edit Hotel',
            'headerTitle' => 'Hotel Management',
            'activePage'  => 'hotels',
            'userEmail'   => (string) session('user_email'),
            'row'         => $row,
            'rooms'       => $rooms,
            'galleryImages' => $this->parseHotelGallery($row['image_gallery'] ?? null),
            'defaultRoomTypes' => $this->defaultRoomTypes(),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function details(int $id)
    {
        $hotelModel = new HotelModel();
        $roomModel = new HotelRoomModel();

        $row = $hotelModel->find($id);
        if (empty($row)) {
            return redirect()->to('/hotels')->with('error', 'Hotel not found.');
        }

        $rooms = $roomModel->where('hotel_id', $id)->orderBy('room_type', 'ASC')->findAll();

        return view('portal/hotels/details', [
            'title'       => 'HJMS ERP | Hotel Details',
            'headerTitle' => 'Hotel Details',
            'activePage'  => 'hotels',
            'userEmail'   => (string) session('user_email'),
            'row'         => $row,
            'rooms'       => $rooms,
            'galleryImages' => $this->parseHotelGallery($row['image_gallery'] ?? null),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function roomingList(): string
    {
        $rows = db_connect()->table('hotel_rooms hr')
            ->select('hr.*, h.name AS hotel_name, h.city AS hotel_city, h.star_rating')
            ->join('hotels h', 'h.id = hr.hotel_id', 'inner')
            ->orderBy('h.name', 'ASC')
            ->orderBy('hr.room_type', 'ASC')
            ->get()
            ->getResultArray();

        return view('portal/hotels/rooming_list', [
            'title'       => 'HJMS ERP | Rooming List',
            'headerTitle' => 'Hotel Management',
            'activePage'  => 'hotels',
            'userEmail'   => (string) session('user_email'),
            'rows'        => $rows,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createHotel()
    {
        $payload = [
            'name'        => (string) $this->request->getPost('name'),
            'city'        => (string) $this->request->getPost('city'),
            'distance_m'  => (string) $this->request->getPost('distance_m'),
            'star_rating' => (string) $this->request->getPost('star_rating'),
            'address'     => (string) $this->request->getPost('address'),
            'image_url'   => trim((string) $this->request->getPost('image_url')),
            'video_url'   => trim((string) $this->request->getPost('video_url')),
            'youtube_url' => trim((string) $this->request->getPost('youtube_url')),
            'map_url'     => trim((string) $this->request->getPost('map_url')),
        ];

        $imageData = $this->handleHotelImageUploads();
        if (! empty($imageData['error'])) {
            return redirect()->to('/hotels/add')->withInput()->with('error', (string) $imageData['error']);
        }

        $uploadedImages = is_array($imageData['images'] ?? null) ? $imageData['images'] : [];

        if (! $this->validateData($payload, [
            'name'        => 'required|max_length[180]',
            'city'        => 'permit_empty|max_length[100]',
            'distance_m'  => 'permit_empty|integer|greater_than_equal_to[0]',
            'star_rating' => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[7]',
            'address'     => 'permit_empty',
            'image_url'   => 'permit_empty|valid_url_strict|max_length[255]',
            'video_url'   => 'permit_empty|valid_url_strict|max_length[255]',
            'youtube_url' => 'permit_empty|valid_url_strict|max_length[255]',
            'map_url'     => 'permit_empty|valid_url_strict|max_length[255]',
        ])) {
            return redirect()->to('/hotels/add')->withInput()->with('errors', $this->validator->getErrors());
        }

        $coverImageUrl = $payload['image_url'];
        if ($coverImageUrl === '' && ! empty($uploadedImages)) {
            $coverImageUrl = (string) ($uploadedImages[0] ?? '');
        }

        try {
            $model = new HotelModel();
            $model->insert([
                'name'        => $payload['name'],
                'city'        => $payload['city'] !== '' ? $payload['city'] : null,
                'distance_m'  => $payload['distance_m'] !== '' ? (int) $payload['distance_m'] : null,
                'star_rating' => $payload['star_rating'] !== '' ? (int) $payload['star_rating'] : 3,
                'address'     => $payload['address'] !== '' ? $payload['address'] : null,
                'image_url'   => $coverImageUrl !== '' ? $coverImageUrl : null,
                'image_gallery' => ! empty($uploadedImages) ? json_encode($uploadedImages) : null,
                'video_url'   => $payload['video_url'] !== '' ? $payload['video_url'] : null,
                'youtube_url' => $payload['youtube_url'] !== '' ? $payload['youtube_url'] : null,
                'map_url'     => $payload['map_url'] !== '' ? $payload['map_url'] : null,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/hotels')->with('success', 'Hotel created successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/hotels/add')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateHotel()
    {
        $hotelId = (int) $this->request->getPost('hotel_id');

        $payload = [
            'name'        => (string) $this->request->getPost('name'),
            'city'        => (string) $this->request->getPost('city'),
            'distance_m'  => (string) $this->request->getPost('distance_m'),
            'star_rating' => (string) $this->request->getPost('star_rating'),
            'address'     => (string) $this->request->getPost('address'),
            'image_url'   => trim((string) $this->request->getPost('image_url')),
            'video_url'   => trim((string) $this->request->getPost('video_url')),
            'youtube_url' => trim((string) $this->request->getPost('youtube_url')),
            'map_url'     => trim((string) $this->request->getPost('map_url')),
        ];

        if ($hotelId < 1) {
            return redirect()->to('/hotels')->withInput()->with('error', 'Valid hotel ID is required.');
        }

        $model = new HotelModel();
        $existing = $model->find($hotelId);
        if (! is_array($existing)) {
            return redirect()->to('/hotels')->with('error', 'Hotel not found.');
        }

        $imageData = $this->handleHotelImageUploads();
        if (! empty($imageData['error'])) {
            return redirect()->to('/hotels/' . $hotelId . '/edit')->withInput()->with('error', (string) $imageData['error']);
        }

        $uploadedImages = is_array($imageData['images'] ?? null) ? $imageData['images'] : [];

        if (! $this->validateData($payload, [
            'name'        => 'required|max_length[180]',
            'city'        => 'permit_empty|max_length[100]',
            'distance_m'  => 'permit_empty|integer|greater_than_equal_to[0]',
            'star_rating' => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[7]',
            'address'     => 'permit_empty',
            'image_url'   => 'permit_empty|valid_url_strict|max_length[255]',
            'video_url'   => 'permit_empty|valid_url_strict|max_length[255]',
            'youtube_url' => 'permit_empty|valid_url_strict|max_length[255]',
            'map_url'     => 'permit_empty|valid_url_strict|max_length[255]',
        ])) {
            return redirect()->to('/hotels/' . $hotelId . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $existingGallery = $this->parseHotelGallery($existing['image_gallery'] ?? null);
            $mergedGallery = array_values(array_unique(array_merge($existingGallery, $uploadedImages)));

            $coverImageUrl = $payload['image_url'] !== ''
                ? $payload['image_url']
                : trim((string) ($existing['image_url'] ?? ''));

            if ($coverImageUrl === '' && ! empty($mergedGallery)) {
                $coverImageUrl = (string) $mergedGallery[0];
            }

            $data = [
                'name'        => $payload['name'],
                'city'        => $payload['city'] !== '' ? $payload['city'] : null,
                'distance_m'  => $payload['distance_m'] !== '' ? (int) $payload['distance_m'] : null,
                'star_rating' => $payload['star_rating'] !== '' ? (int) $payload['star_rating'] : 3,
                'address'     => $payload['address'] !== '' ? $payload['address'] : null,
                'image_url'   => $coverImageUrl !== '' ? $coverImageUrl : null,
                'image_gallery' => ! empty($mergedGallery) ? json_encode($mergedGallery) : null,
                'video_url'   => $payload['video_url'] !== '' ? $payload['video_url'] : null,
                'youtube_url' => $payload['youtube_url'] !== '' ? $payload['youtube_url'] : null,
                'map_url'     => $payload['map_url'] !== '' ? $payload['map_url'] : null,
                'updated_at'  => date('Y-m-d H:i:s'),
            ];

            $model->update($hotelId, $data);

            return redirect()->to('/hotels')->with('success', 'Hotel updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/hotels/' . $hotelId . '/edit')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteHotelImage()
    {
        $hotelId = (int) $this->request->getPost('hotel_id');
        $imageUrl = trim((string) $this->request->getPost('image_url'));

        if ($hotelId < 1 || $imageUrl === '') {
            return redirect()->to('/hotels')->with('error', 'Valid hotel and image are required.');
        }

        try {
            $model = new HotelModel();
            $row = $model->find($hotelId);
            if (! is_array($row)) {
                return redirect()->to('/hotels')->with('error', 'Hotel not found.');
            }

            $gallery = $this->parseHotelGallery($row['image_gallery'] ?? null);
            $updatedGallery = array_values(array_filter($gallery, static fn($url) => $url !== $imageUrl));

            $coverImage = trim((string) ($row['image_url'] ?? ''));
            if ($coverImage === $imageUrl) {
                $coverImage = $updatedGallery[0] ?? '';
            }

            $model->update($hotelId, [
                'image_gallery' => ! empty($updatedGallery) ? json_encode($updatedGallery) : null,
                'image_url' => $coverImage !== '' ? $coverImage : null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->deleteHotelImageFile($imageUrl);

            return redirect()->to('/hotels/' . $hotelId . '/edit')->with('success', 'Image removed from gallery.');
        } catch (\Throwable $e) {
            return redirect()->to('/hotels/' . $hotelId . '/edit')->with('error', $e->getMessage());
        }
    }

    public function deleteHotel()
    {
        $hotelId = (int) $this->request->getPost('hotel_id');

        if ($hotelId < 1) {
            return redirect()->to('/hotels')->with('error', 'Valid hotel ID is required for delete.');
        }

        try {
            $hotelModel = new HotelModel();
            $roomModel = new HotelRoomModel();

            $roomModel->where('hotel_id', $hotelId)->delete();
            $deleted = $hotelModel->delete($hotelId);

            if (! $deleted) {
                return redirect()->to('/hotels')->with('error', 'Hotel not found or already removed.');
            }

            return redirect()->to('/hotels')->with('success', 'Hotel deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/hotels')->with('error', $e->getMessage());
        }
    }

    public function createRoom()
    {
        $payload = [
            'hotel_id'         => (int) $this->request->getPost('hotel_id'),
            'room_type'        => (string) $this->request->getPost('room_type'),
            'total_rooms'      => (string) $this->request->getPost('total_rooms'),
            'allocated_rooms'  => (string) $this->request->getPost('allocated_rooms'),
        ];

        if (! $this->validateData($payload, [
            'hotel_id'        => 'required|integer',
            'room_type'       => 'required|max_length[80]',
            'total_rooms'     => 'required|integer|greater_than_equal_to[0]',
            'allocated_rooms' => 'permit_empty|integer|greater_than_equal_to[0]',
        ])) {
            return redirect()->to('/hotels/' . $payload['hotel_id'] . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        $totalRooms = (int) $payload['total_rooms'];
        $allocatedRooms = $payload['allocated_rooms'] !== '' ? (int) $payload['allocated_rooms'] : 0;

        if ($allocatedRooms > $totalRooms) {
            return redirect()->to('/hotels/' . $payload['hotel_id'] . '/edit')->withInput()->with('error', 'Allocated rooms cannot be greater than total rooms.');
        }

        try {
            $roomModel = new HotelRoomModel();
            $roomModel->insert([
                'hotel_id'        => $payload['hotel_id'],
                'room_type'       => $payload['room_type'],
                'total_rooms'     => $totalRooms,
                'allocated_rooms' => $allocatedRooms,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/hotels/' . $payload['hotel_id'] . '/edit')->with('success', 'Room type added successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/hotels/' . $payload['hotel_id'] . '/edit')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateRoom()
    {
        $roomId = (int) $this->request->getPost('room_id');
        $hotelId = (int) $this->request->getPost('hotel_id');

        $payload = [
            'room_type'       => (string) $this->request->getPost('room_type'),
            'total_rooms'     => (string) $this->request->getPost('total_rooms'),
            'allocated_rooms' => (string) $this->request->getPost('allocated_rooms'),
        ];

        if ($roomId < 1 || $hotelId < 1) {
            return redirect()->to('/hotels')->with('error', 'Valid room and hotel IDs are required.');
        }

        if (! $this->validateData($payload, [
            'room_type'       => 'required|max_length[80]',
            'total_rooms'     => 'required|integer|greater_than_equal_to[0]',
            'allocated_rooms' => 'required|integer|greater_than_equal_to[0]',
        ])) {
            return redirect()->to('/hotels/' . $hotelId . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        $totalRooms = (int) $payload['total_rooms'];
        $allocatedRooms = (int) $payload['allocated_rooms'];

        if ($allocatedRooms > $totalRooms) {
            return redirect()->to('/hotels/' . $hotelId . '/edit')->withInput()->with('error', 'Allocated rooms cannot be greater than total rooms.');
        }

        try {
            $roomModel = new HotelRoomModel();
            $roomModel->update($roomId, [
                'room_type'       => $payload['room_type'],
                'total_rooms'     => $totalRooms,
                'allocated_rooms' => $allocatedRooms,
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/hotels/' . $hotelId . '/edit')->with('success', 'Room allocation updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/hotels/' . $hotelId . '/edit')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteRoom()
    {
        $roomId = (int) $this->request->getPost('room_id');
        $hotelId = (int) $this->request->getPost('hotel_id');

        if ($roomId < 1 || $hotelId < 1) {
            return redirect()->to('/hotels')->with('error', 'Valid room and hotel IDs are required for delete.');
        }

        try {
            $roomModel = new HotelRoomModel();
            $deleted = $roomModel->delete($roomId);

            if (! $deleted) {
                return redirect()->to('/hotels/' . $hotelId . '/edit')->with('error', 'Room type not found or already removed.');
            }

            return redirect()->to('/hotels/' . $hotelId . '/edit')->with('success', 'Room type deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/hotels/' . $hotelId . '/edit')->with('error', $e->getMessage());
        }
    }

    private function defaultRoomTypes(): array
    {
        return ['sharing 4 bed', 'sharing 5 bed', 'quad', 'triple', 'double', 'single'];
    }

    private function parseHotelGallery($rawGallery): array
    {
        if (! is_string($rawGallery) || trim($rawGallery) === '') {
            return [];
        }

        $decoded = json_decode($rawGallery, true);
        if (! is_array($decoded)) {
            return [];
        }

        $gallery = [];
        foreach ($decoded as $url) {
            if (! is_string($url)) {
                continue;
            }

            $normalizedUrl = trim($url);
            if ($normalizedUrl === '') {
                continue;
            }

            $gallery[] = $normalizedUrl;
        }

        return array_values(array_unique($gallery));
    }

    private function deleteHotelImageFile(string $imageUrl): void
    {
        $appBase = rtrim((string) base_url(), '/');
        $assetPrefix = $appBase . '/assets/uploads/hotels/';
        if (! str_starts_with($imageUrl, $assetPrefix)) {
            return;
        }

        $fileName = basename((string) parse_url($imageUrl, PHP_URL_PATH));
        if ($fileName === '') {
            return;
        }

        $filePath = rtrim((string) FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'hotels' . DIRECTORY_SEPARATOR . $fileName;
        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }

    private function handleHotelImageUploads(): array
    {
        $files = $this->request->getFiles();
        $imageFiles = $files['hotel_images'] ?? null;

        if ($imageFiles === null) {
            return ['images' => []];
        }

        if (! is_array($imageFiles)) {
            $imageFiles = [$imageFiles];
        }

        $uploaded = [];

        foreach ($imageFiles as $file) {
            if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if (! $file->isValid()) {
                return ['error' => 'One of the uploaded hotel images is invalid.'];
            }

            $maxSize = 5 * 1024 * 1024;
            if ((int) $file->getSize() > $maxSize) {
                return ['error' => 'Each hotel image must be 5MB or smaller.'];
            }

            $mimeType = strtolower((string) $file->getMimeType());
            $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (! in_array($mimeType, $allowed, true)) {
                return ['error' => 'Allowed hotel image formats are JPG, PNG, and WEBP.'];
            }

            $uploadDir = rtrim((string) FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'hotels';
            if (! is_dir($uploadDir) && ! @mkdir($uploadDir, 0775, true) && ! is_dir($uploadDir)) {
                return ['error' => 'Could not create hotel upload directory.'];
            }

            $extension = strtolower((string) $file->getExtension());
            if ($extension === '') {
                $extension = 'jpg';
            }

            $newName = 'hotel-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
            $file->move($uploadDir, $newName, true);

            $uploaded[] = base_url('assets/uploads/hotels/' . $newName);
        }

        return ['images' => $uploaded];
    }
}
