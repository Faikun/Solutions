-- --------------------------------------------------------
-- WorldSkills Kazakhstan 2025
-- Module B
-- Beginner friendly SQL dump example
-- --------------------------------------------------------

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    external_id VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE adverts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    text TEXT NOT NULL,
    status VARCHAR(50) NOT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    views_count INT UNSIGNED NOT NULL DEFAULT 0,
    published_at TIMESTAMP NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_adverts_category FOREIGN KEY (category_id) REFERENCES categories(id),
    CONSTRAINT fk_adverts_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE advert_photos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    advert_id BIGINT UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_photos_advert FOREIGN KEY (advert_id) REFERENCES adverts(id) ON DELETE CASCADE
);

CREATE TABLE advert_paid_services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    advert_id BIGINT UNSIGNED NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    connected_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_services_advert FOREIGN KEY (advert_id) REFERENCES adverts(id) ON DELETE CASCADE
);
