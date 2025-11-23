-- Table structure for WhatsApp groups
CREATE TABLE `whatsapp_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(500) DEFAULT NULL,
  `whatsapp_link` varchar(500) NOT NULL,
  `state_code` varchar(5) DEFAULT NULL,
  `city_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_state_city` (`state_code`, `city_name`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `whatsapp_groups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `whatsapp_groups_ibfk_2` FOREIGN KEY (`state_code`) REFERENCES `states` (`code`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add indexes for better performance
CREATE INDEX idx_state_code ON whatsapp_groups(state_code);
CREATE INDEX idx_created_at ON whatsapp_groups(created_at);