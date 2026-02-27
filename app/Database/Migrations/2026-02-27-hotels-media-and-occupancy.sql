ALTER TABLE hotels
    ADD COLUMN IF NOT EXISTS image_url VARCHAR(255) NULL AFTER address,
    ADD COLUMN IF NOT EXISTS video_url VARCHAR(255) NULL AFTER image_url,
    ADD COLUMN IF NOT EXISTS youtube_url VARCHAR(255) NULL AFTER video_url,
    ADD COLUMN IF NOT EXISTS map_url VARCHAR(255) NULL AFTER youtube_url;

ALTER TABLE package_hotels
    ADD COLUMN IF NOT EXISTS hotel_room_id INT NULL AFTER hotel_id;

CREATE INDEX IF NOT EXISTS idx_package_hotels_room_dates
    ON package_hotels (hotel_room_id, check_in_date, check_out_date);