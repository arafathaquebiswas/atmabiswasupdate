-- Image, title and description an admin set for a card on the Events page
-- (Events.php). Same shape as the Social page's tables.
--
-- Optional to run by hand: cards_ensure_tables() in page_cards.php creates
-- both tables the first time an admin opens Events Page Images.
--
-- card_key     matches a 'key' in events_cards(), e.g. 'winter-2'.
-- img_path     document-root-relative upload, e.g. 'media/events/EVENT_<hex>.jpg';
--              NULL means the admin removed the image and the card shows the logo.
-- title        shown instead of the card's built-in title; never empty.
-- description  shown instead of the built-in description; '' hides it.
-- No row for a card means it shows its built-in image / text.
CREATE TABLE IF NOT EXISTS event_card_images (
    card_key   VARCHAR(64)  NOT NULL PRIMARY KEY,
    img_path   VARCHAR(255) NULL,
    updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_card_texts (
    card_key    VARCHAR(64)  NOT NULL PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    description TEXT         NOT NULL,
    updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
