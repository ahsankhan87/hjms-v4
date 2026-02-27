<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

class Validation extends BaseConfig
{
    // --------------------------------------------------------------------
    // Setup
    // --------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var list<string>
     */
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public array $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------

    public array $authLogin = [
        'email'    => 'required|valid_email',
        'password' => 'required|min_length[8]',
    ];

    public array $authPasswordResetRequest = [
        'email' => 'required|valid_email',
    ];

    public array $authPasswordReset = [
        'token'    => 'required|min_length[32]',
        'password' => 'required|min_length[8]',
    ];

    public array $roleCreate = [
        'name' => 'required|alpha_dash|min_length[3]|max_length[50]',
    ];

    public array $permissionCreate = [
        'name' => 'required|alpha_dash|min_length[3]|max_length[100]',
    ];

    public array $rolePermissionSync = [
        'permission_ids'   => 'permit_empty',
        'permission_ids.*' => 'required|integer',
    ];

    public array $userRoleAssign = [
        'role_id' => 'required|integer',
    ];
}
