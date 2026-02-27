CREATE TABLE IF NOT EXISTS transport_legs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transport_id INT NOT NULL,
    seq_no INT NOT NULL,
    from_code VARCHAR(20) NOT NULL,
    to_code VARCHAR(20) NOT NULL,
    is_ziarat TINYINT(1) NOT NULL DEFAULT 0,
    ziarat_site VARCHAR(180) NULL,
    notes VARCHAR(255) NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    INDEX idx_tl_transport_seq (transport_id, seq_no)
);
