<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SeasonModel;

class SeasonController extends BaseController
{
    public function index(): string
    {
        $model = new SeasonModel();

        return view('portal/seasons/index', [
            'title'       => 'HJMS ERP | Seasons',
            'headerTitle' => 'Season Management',
            'activePage'  => 'seasons',
            'userEmail'   => (string) session('user_email'),
            'rows'        => $model->orderBy('year_start', 'DESC')->orderBy('id', 'DESC')->findAll(),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createSeason()
    {
        $payload = [
            'year_start' => (int) $this->request->getPost('year_start'),
            'year_end'   => (int) $this->request->getPost('year_end'),
            'name'       => trim((string) $this->request->getPost('name')),
            'is_active'  => (int) ($this->request->getPost('is_active') ? 1 : 0),
        ];

        if (! $this->validateData($payload, [
            'year_start' => 'required|integer|greater_than[2000]',
            'year_end'   => 'required|integer|greater_than[2000]',
            'name'       => 'permit_empty|max_length[120]',
        ])) {
            return redirect()->to('/app/seasons')->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($payload['year_end'] <= $payload['year_start']) {
            return redirect()->to('/app/seasons')->withInput()->with('error', 'Year End must be greater than Year Start.');
        }

        $name = $payload['name'] !== '' ? $payload['name'] : ($payload['year_start'] . ' - ' . $payload['year_end'] . ' Season');

        try {
            $model = new SeasonModel();
            $db = db_connect();

            $db->transStart();
            if ($payload['is_active'] === 1) {
                $db->table('seasons')->set(['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')])->update();
            }

            $model->insert([
                'name'       => $name,
                'year_start' => $payload['year_start'],
                'year_end'   => $payload['year_end'],
                'is_active'  => $payload['is_active'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $db->transComplete();

            if (! $db->transStatus()) {
                throw new \RuntimeException('Failed to create season.');
            }

            return redirect()->to('/app/seasons')->with('success', 'Season created successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/seasons')->withInput()->with('error', $e->getMessage());
        }
    }

    public function activateSeason()
    {
        $seasonId = (int) $this->request->getPost('season_id');
        if ($seasonId < 1) {
            return redirect()->back()->with('error', 'Valid season is required.');
        }

        if (! activate_season($seasonId)) {
            return redirect()->back()->with('error', 'Failed to activate season.');
        }

        return redirect()->back()->with('success', 'Season activated successfully.');
    }
}
