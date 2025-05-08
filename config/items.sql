-- Create items table
CREATE TABLE IF NOT EXISTS `items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `item_price` decimal(10,2) NOT NULL,
  `item_description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Insert default items
INSERT INTO `items` (`item_name`, `item_price`, `item_description`) VALUES
('Additional Guest', 200.00, 'Fee for extra guest beyond room capacity'),
('Towel', 50.00, 'Extra towel'),
('Soap', 25.00, 'Extra soap'),
('Shampoo', 30.00, 'Extra shampoo'),
('Toothbrush', 20.00, 'Extra toothbrush'),
('Toothpaste', 25.00, 'Extra toothpaste'),
('Slippers', 40.00, 'Extra slippers'),
('Extra Bed', 200.00, 'Additional bed in room'),
('Laundry', 150.00, 'Per load of laundry service'),
('Room Service', 100.00, 'Room service fee'),
('Late Checkout', 300.00, 'Extended checkout time fee'); 