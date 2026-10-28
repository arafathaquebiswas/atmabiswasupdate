-- Title and description an admin set for a card on the Social page (social.php).
--
-- Optional to run by hand: social_ensure_table() in social_cards.php creates
-- the same table the first time an admin opens Social Page Images.
--
-- card_key     matches a 'key' in social_cards(), e.g. 'daycare-3'.
-- title        shown instead of the card's built-in title; never empty.
-- description  shown instead of the built-in description; '' hides it.
-- No row for a card means it shows its built-in title and description.
CREATE TABLE IF NOT EXISTS social_card_texts (
    card_key    VARCHAR(64)  NOT NULL PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    description TEXT         NOT NULL,
    updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
