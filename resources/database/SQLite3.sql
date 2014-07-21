CREATE TABLE index (
    id TEXT PRIMARY KEY NOT NULL,
    type INTEGER NOT NULL
);
CREATE TABLE teleport (
    id TEXT PRIMARY KEY NOT NULL,
    level TEXT NOT NULL,
    target TEXT NOT NULL,
    description TEXT NOT NULL
);
CREATE TABLE command (
    id TEXT PRIMARY KEY NOT NULL,
    command TEXT NOT NULL,
    description TEXT NOT NULL
);
