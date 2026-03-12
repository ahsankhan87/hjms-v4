-- Add dynamic room costing support
ALTER TABLE hotel_rooms
    ADD COLUMN room_cost DECIMAL(12,2) NULL AFTER room_type;

-- Optional direct link from package costs to hotel room
ALTER TABLE package_costs
    ADD COLUMN hotel_room_id INT NULL AFTER package_id;

CREATE INDEX idx_package_costs_hotel_room_id ON package_costs (hotel_room_id);
