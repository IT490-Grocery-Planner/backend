CREATE TABLE IF NOT EXISTS User_Recipes (
	recipeID INT AUTO_INCREMENT,
	name TEXT NOT NULL,
	description TEXT,
	instructions LONGTEXT,
	maxReadyTime TIME,
	PRIMARY KEY(recipeID)
	);
