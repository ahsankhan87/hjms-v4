ALTER TABLE transports
    ADD COLUMN IF NOT EXISTS transport_name VARCHAR(180) NULL AFTER id;

UPDATE transports
SET transport_name = provider_name
WHERE transport_name IS NULL OR transport_name = '';
