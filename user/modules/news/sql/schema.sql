
-- path: /user/modules/news/sql/schema.sql
--
-- ChAoS MVC News Module
-- Initial database schema: 1.0.0
--
-- [AI:GPT-6 | 2026-10-22 UTC]

CREATE TABLE IF NOT EXISTS news_schema (
    id TINYINT UNSIGNED NOT NULL,
    schema_version VARCHAR(64) NOT NULL,
    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO news_schema (
    id,
    schema_version
)
VALUES (
    1,
    '1.0.0'
)
ON DUPLICATE KEY UPDATE
    id = VALUES(id);

CREATE TABLE IF NOT EXISTS news_articles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,

    title VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL,

    category ENUM(
        'release',
        'security',
        'development',
        'announcement'
    ) NOT NULL DEFAULT 'announcement',

    summary VARCHAR(300) NOT NULL DEFAULT '',
    body_markdown MEDIUMTEXT NOT NULL,

    status ENUM(
        'draft',
        'published'
    ) NOT NULL DEFAULT 'draft',

    published_at DATETIME DEFAULT NULL,

    created_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY news_articles_slug_unique (slug),

    KEY news_articles_public_index (
        status,
        published_at
    ),

    KEY news_articles_category_index (
        category,
        status,
        published_at
    )

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- [End AI:GPT-6 | 2026-10-22]
