CREATE TABLE db_metadata (
    one_row_only ENUM('One-column-to-rule-them-all') NOT NULL UNIQUE DEFAULT 'One-column-to-rule-them-all', -- Metadata table should allways contain just one row
    db_version int(11),
    changed_at DATETIME DEFAULT NOW()
) SELECT 0 as db_version;
