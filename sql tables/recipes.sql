CREATE TABLE IF NOT EXISTS Recipes (
	recipeID INT AUTO_INCREMENT,
	name TEXT NOT NULL,
	description TEXT,
	instructions LONGTEXT,
	maxReadyTime TIME,
	PRIMARY KEY (recipeID)	
);
