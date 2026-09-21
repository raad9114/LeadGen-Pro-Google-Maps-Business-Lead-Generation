-- ============================================================
-- LeadGen Pro — Seed Data
-- ============================================================

-- -----------------------------------------------------------
-- Default Admin User (password: admin123)
-- Change this immediately after first login!
-- -----------------------------------------------------------
INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `role`, `status`) VALUES
('admin', 'admin@leadgen.local', '$2y$12$LJ3m4ys3Gzf0G8K0Y4oOmejqPxELMpSOQvgXxwT9WCHCgU.dGqjKu', 'Administrator', 'admin', 'active');

-- -----------------------------------------------------------
-- Business Categories (30+ predefined)
-- -----------------------------------------------------------
INSERT INTO `business_categories` (`name`, `slug`, `icon`, `variants`, `sort_order`) VALUES
('Restaurant', 'restaurant', 'bi-cup-hot', '["restaurant","family restaurant","fine dining restaurant"]', 1),
('Cafe', 'cafe', 'bi-cup', '["cafe","coffee shop","tea house"]', 2),
('Hotel', 'hotel', 'bi-building', '["hotel","motel","resort","guest house"]', 3),
('Hospital', 'hospital', 'bi-hospital', '["hospital","medical center","clinic"]', 4),
('Clinic', 'clinic', 'bi-heart-pulse', '["clinic","medical clinic","health clinic"]', 5),
('Dentist', 'dentist', 'bi-emoji-smile', '["dentist","dental clinic","dental care"]', 6),
('Pharmacy', 'pharmacy', 'bi-capsule', '["pharmacy","drugstore","medicine shop"]', 7),
('School', 'school', 'bi-mortarboard', '["school","primary school","high school"]', 8),
('College', 'college', 'bi-book', '["college","community college"]', 9),
('University', 'university', 'bi-bank', '["university","institution"]', 10),
('Real Estate Agency', 'real-estate-agency', 'bi-house-door', '["real estate agency","property dealer","real estate company"]', 11),
('Travel Agency', 'travel-agency', 'bi-airplane', '["travel agency","tour operator","travel company"]', 12),
('Software Company', 'software-company', 'bi-code-slash', '["software company","IT company","tech company"]', 13),
('Digital Marketing Agency', 'digital-marketing-agency', 'bi-megaphone', '["digital marketing agency","marketing agency","SEO agency"]', 14),
('Law Firm', 'law-firm', 'bi-briefcase', '["law firm","attorney","lawyer","legal services"]', 15),
('Accounting Firm', 'accounting-firm', 'bi-calculator', '["accounting firm","accountant","CPA","bookkeeper"]', 16),
('Gym', 'gym', 'bi-trophy', '["gym","fitness center","health club"]', 17),
('Beauty Salon', 'beauty-salon', 'bi-scissors', '["beauty salon","hair salon","beauty parlor"]', 18),
('Spa', 'spa', 'bi-droplet', '["spa","wellness center","massage"]', 19),
('Car Dealer', 'car-dealer', 'bi-car-front', '["car dealer","auto dealer","car showroom"]', 20),
('Car Repair', 'car-repair', 'bi-wrench', '["car repair","auto repair","garage","mechanic"]', 21),
('Electronics Store', 'electronics-store', 'bi-phone', '["electronics store","computer store","mobile shop"]', 22),
('Furniture Store', 'furniture-store', 'bi-lamp', '["furniture store","furniture shop","home furnishing"]', 23),
('Clothing Store', 'clothing-store', 'bi-bag', '["clothing store","fashion store","boutique","apparel"]', 24),
('Supermarket', 'supermarket', 'bi-cart', '["supermarket","grocery store","department store"]', 25),
('Grocery Store', 'grocery-store', 'bi-basket', '["grocery store","convenience store","mini market"]', 26),
('Construction Company', 'construction-company', 'bi-bricks', '["construction company","builder","contractor"]', 27),
('Architect', 'architect', 'bi-rulers', '["architect","architecture firm","architectural design"]', 28),
('Interior Design Company', 'interior-design-company', 'bi-palette', '["interior design company","interior designer","home decorator"]', 29),
('Photography Studio', 'photography-studio', 'bi-camera', '["photography studio","photographer","photo studio"]', 30),
('Event Management Company', 'event-management-company', 'bi-calendar-event', '["event management company","event planner","wedding planner"]', 31);

-- -----------------------------------------------------------
-- Countries
-- -----------------------------------------------------------
INSERT INTO `countries` (`name`, `code`) VALUES
('Bangladesh', 'BD'),
('India', 'IN'),
('United States', 'US'),
('United Kingdom', 'GB'),
('United Arab Emirates', 'AE'),
('Saudi Arabia', 'SA'),
('Canada', 'CA'),
('Australia', 'AU'),
('Malaysia', 'MY'),
('Singapore', 'SG');

-- -----------------------------------------------------------
-- Cities (Bangladesh — Dhaka + a few major cities)
-- -----------------------------------------------------------
INSERT INTO `cities` (`country_id`, `name`, `latitude`, `longitude`) VALUES
((SELECT id FROM countries WHERE code='BD'), 'Dhaka', 23.8103, 90.4125),
((SELECT id FROM countries WHERE code='BD'), 'Chittagong', 22.3569, 91.7832),
((SELECT id FROM countries WHERE code='BD'), 'Sylhet', 24.8949, 91.8687),
((SELECT id FROM countries WHERE code='BD'), 'Rajshahi', 24.3745, 88.6042),
((SELECT id FROM countries WHERE code='BD'), 'Khulna', 22.8456, 89.5403);

-- -----------------------------------------------------------
-- Areas (Dhaka — 15 areas as specified)
-- -----------------------------------------------------------
INSERT INTO `areas` (`city_id`, `name`, `latitude`, `longitude`) VALUES
((SELECT id FROM cities WHERE name='Dhaka' LIMIT 1), 'Mirpur', 23.8223, 90.3654),
((SELECT id FROM cities WHERE name='Dhaka' LIMIT 1), 'Uttara', 23.8759, 90.3795),
((SELECT id FROM cities WHERE name='Dhaka' LIMIT 1), 'Gulshan', 23.7925, 90.4078),
((SELECT id FROM cities WHERE name='Dhaka' LIMIT 1), 'Banani', 23.7937, 90.4066),
((SELECT id FROM cities WHERE name='Dhaka' LIMIT 1), 'Dhanmondi', 23.7461, 90.3742),
((SELECT id FROM cities WHERE name='Dhaka' LIMIT 1), 'Mohammadpur', 23.7662, 90.3589),
((SELECT id FROM cities WHERE name='Dhaka' LIMIT 1), 'Bashundhara', 23.8193, 90.4272),
((SELECT id FROM cities WHERE name='Dhaka' LIMIT 1), 'Badda', 23.7806, 90.4260),
((SELECT id FROM cities WHERE name='Dhaka' LIMIT 1), 'Motijheel', 23.7331, 90.4178),
((SELECT id FROM cities WHERE name='Dhaka' LIMIT 1), 'Paltan', 23.7350, 90.4130),
((SELECT id FROM cities WHERE name='Dhaka' LIMIT 1), 'Farmgate', 23.7572, 90.3873),
((SELECT id FROM cities WHERE name='Dhaka' LIMIT 1), 'Tejgaon', 23.7629, 90.3926),
((SELECT id FROM cities WHERE name='Dhaka' LIMIT 1), 'Mohakhali', 23.7781, 90.4040),
((SELECT id FROM cities WHERE name='Dhaka' LIMIT 1), 'Wari', 23.7156, 90.4089),
((SELECT id FROM cities WHERE name='Dhaka' LIMIT 1), 'Old Dhaka', 23.7104, 90.4074);

-- -----------------------------------------------------------
-- Default Settings
-- -----------------------------------------------------------
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('google_places_api_key', '', 'api'),
('google_maps_js_api_key', '', 'api'),
('max_results_per_search', '60', 'search'),
('max_pages_to_crawl', '5', 'email'),
('crawl_timeout_seconds', '5', 'email'),
('requests_per_minute', '30', 'api'),
('enable_email_enrichment', '1', 'email'),
('auto_search_emails', '1', 'email'),
('default_country', 'Bangladesh', 'search'),
('cost_per_text_search', '0.032', 'api'),
('cost_per_place_details', '0.017', 'api');
