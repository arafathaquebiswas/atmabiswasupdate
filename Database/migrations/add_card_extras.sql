-- Cards an admin added to a section of the Social page (social.php) or the
-- Events page (Events.php), up to 8 cards per section in all.
--
-- Optional to run by hand: cards_ensure_tables() in page_cards.php creates
-- both tables the first time an admin opens that page's images screen.
--
-- id           makes the card's key "<segment_key>-x<id>", e.g. 'winter-x12';
--              its title and description are in the page's *_card_texts table
--              and its image in *_card_images under that key.
-- segment_key  the section it belongs to: a segment 'key' in social_cards()
--              or events_cards(), e.g. 'women-rights'.
CREATE TABLE IF NOT EXISTS social_card_extras (
    id          INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    segment_key VARCHAR(64)  NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY segment_key (segment_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_card_extras (
    id          INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    segment_key VARCHAR(64)  NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY segment_key (segment_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
