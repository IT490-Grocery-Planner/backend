CREATE TABLE IF NOT EXISTS Sessions (
	sessionID INT NOT NULL,
	email VARCHAR(255),
	creationTime TIMESTAMP NOT NULL CURRENT_TIMESTAMP,
	PRIMARY KEY (sessionID)

);
