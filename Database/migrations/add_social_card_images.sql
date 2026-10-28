-- Image assigned to each card on the Social page (social.php).
--
-- Optional to run by hand: social_ensure_table() in social_cards.php creates
-- the same table the first time an admin opens Social Page Images.
--
-- card_key  matches a 'key' in social_cards(), e.g. 'daycare-3'.
-- img_path  document-root-relative upload, e.g. 'media/social/SOCIAL_<hex>.jpg';
--           NULL means the admin removed the image and the card shows the logo.
-- No row for a card means it shows its built-in image.
CREATE TABLE IF NOT EXISTS social_card_images (
    card_key   VARCHAR(64)  NOT NULL PRIMARY KEY,
    img_path   VARCHAR(255) NULL,
    updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
