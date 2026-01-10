-- Sample data for testing the complete system

-- Update existing products with stock quantities
UPDATE products SET stock_quantity = 50, min_stock_level = 10 WHERE id = 25;
UPDATE products SET stock_quantity = 100, min_stock_level = 20 WHERE id = 26;
UPDATE products SET stock_quantity = 30, min_stock_level = 10 WHERE id = 27;
UPDATE products SET stock_quantity = 5, min_stock_level = 10 WHERE id = 28;
UPDATE products SET stock_quantity = 200, min_stock_level = 50 WHERE id = 30;
UPDATE products SET stock_quantity = 15, min_stock_level = 5 WHERE id = 31;
UPDATE products SET stock_quantity = 80, min_stock_level = 20 WHERE id = 32;
UPDATE products SET stock_quantity = 150, min_stock_level = 30 WHERE id = 33;
UPDATE products SET stock_quantity = 60, min_stock_level = 15 WHERE id = 34;
UPDATE products SET stock_quantity = 40, min_stock_level = 10 WHERE id = 35;
UPDATE products SET stock_quantity = 70, min_stock_level = 15 WHERE id = 36;
UPDATE products SET stock_quantity = 25, min_stock_level = 10 WHERE id = 38;

-- Insert sample suppliers
INSERT INTO suppliers (name, contact_person, email, phone, address) VALUES
('ABC Tools Supplier', 'John Smith', 'john@abctools.com', '0771234567', '123 Main St, Colombo'),
('Hardware Wholesale Ltd', 'Sarah Johnson', 'sarah@hardwarewholesale.com', '0772345678', '456 Market Rd, Kandy'),
('BuildMart Suppliers', 'Mike Brown', 'mike@buildmart.com', '0773456789', '789 Industrial Ave, Galle'),
('ElectroSupply Co', 'Emma Wilson', 'emma@electrosupply.com', '0774567890', '321 Electric St, Negombo');

-- Assign suppliers to products (adjust IDs based on your supplier IDs)
INSERT INTO product_suppliers (product_id, supplier_id, supply_price) VALUES
(25, 1, 3000.00),  -- Drilling Machine
(26, 3, 1200.00),  -- Spray Paint
(27, 3, 600.00),   -- Safety Helmet
(28, 3, 2200.00),  -- Cement Bag
(30, 4, 280.00),   -- LED Bulb
(31, 4, 8500.00),  -- Ceiling Fan
(32, 2, 650.00),   -- PVC pipe
(33, 2, 55.00),    -- Pipe Fitting
(34, 3, 200.00),   -- Safety Gloves
(35, 1, 1000.00),  -- Screwdriver Set
(36, 1, 400.00),   -- Measuring Tape
(38, 1, 2800.00);  -- Hammer

-- Create an admin user (password: admin123)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@adheehardware.com', 'admin123', 'admin');

-- Insert sample orders for testing
INSERT INTO orders (user_id, total_amount, status, created_at) VALUES
(9, 5200.00, 'completed', '2025-04-15 10:30:00'),
(9, 2200.00, 'processing', '2025-04-16 14:20:00'),
(10, 3850.00, 'pending', '2025-04-17 09:15:00');

-- Insert order items for the sample orders
-- Order 1 items
INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES
(1, 25, 1, 3500.00, 3500.00),
(1, 26, 1, 1500.00, 1500.00),
(1, 34, 1, 250.00, 250.00);

-- Order 2 items
INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES
(2, 27, 2, 700.00, 1400.00),
(2, 36, 1, 500.00, 500.00),
(2, 34, 1, 250.00, 250.00);

-- Order 3 items
INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES
(3, 30, 10, 350.00, 3500.00),
(3, 34, 1, 250.00, 250.00);
