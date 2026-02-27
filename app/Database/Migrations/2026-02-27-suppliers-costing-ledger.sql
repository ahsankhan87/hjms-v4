CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(180) NOT NULL,
    supplier_type VARCHAR(40) NOT NULL,
    contact_person VARCHAR(120) NULL,
    phone VARCHAR(40) NULL,
    email VARCHAR(120) NULL,
    address VARCHAR(255) NULL,
    opening_balance DECIMAL(14,2) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    INDEX idx_suppliers_type (supplier_type)
);

CREATE TABLE IF NOT EXISTS package_cost_sheets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    version_no INT NOT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    visa_sar DECIMAL(14,2) NOT NULL DEFAULT 0,
    visa_ex_rate DECIMAL(14,4) NOT NULL DEFAULT 1,
    transport_sar DECIMAL(14,2) NOT NULL DEFAULT 0,
    transport_ex_rate DECIMAL(14,4) NOT NULL DEFAULT 1,
    ticket_pkr DECIMAL(14,2) NOT NULL DEFAULT 0,
    makkah_room_rate_sar DECIMAL(14,2) NOT NULL DEFAULT 0,
    makkah_ex_rate DECIMAL(14,4) NOT NULL DEFAULT 1,
    makkah_nights INT NOT NULL DEFAULT 0,
    madina_room_rate_sar DECIMAL(14,2) NOT NULL DEFAULT 0,
    madina_ex_rate DECIMAL(14,4) NOT NULL DEFAULT 1,
    madina_nights INT NOT NULL DEFAULT 0,
    other_pkr DECIMAL(14,2) NOT NULL DEFAULT 0,
    profit_pkr DECIMAL(14,2) NOT NULL DEFAULT 0,
    notes VARCHAR(255) NULL,
    created_by INT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    INDEX idx_cost_sheet_pkg_ver (package_id, version_no),
    INDEX idx_cost_sheet_publish (package_id, is_published)
);

CREATE TABLE IF NOT EXISTS package_cost_sheet_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cost_sheet_id INT NOT NULL,
    component_code VARCHAR(30) NOT NULL,
    supplier_id INT NULL,
    purchase_amount_pkr DECIMAL(14,2) NOT NULL DEFAULT 0,
    remarks VARCHAR(255) NULL,
    created_at DATETIME NULL,
    INDEX idx_sheet_items_sheet (cost_sheet_id),
    INDEX idx_sheet_items_supplier (supplier_id)
);

CREATE TABLE IF NOT EXISTS package_price_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cost_sheet_id INT NOT NULL,
    sharing_type VARCHAR(30) NOT NULL,
    total_cost_pkr DECIMAL(14,2) NOT NULL DEFAULT 0,
    sell_price_pkr DECIMAL(14,2) NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    INDEX idx_price_lines_sheet (cost_sheet_id),
    INDEX idx_price_lines_type (sharing_type)
);

CREATE TABLE IF NOT EXISTS supplier_ledger_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    entry_date DATE NOT NULL,
    entry_type VARCHAR(30) NOT NULL,
    debit_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    credit_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    reference_type VARCHAR(40) NULL,
    reference_id INT NULL,
    description VARCHAR(255) NULL,
    created_at DATETIME NULL,
    INDEX idx_supplier_ledger_supplier_date (supplier_id, entry_date),
    INDEX idx_supplier_ledger_ref (reference_type, reference_id)
);
