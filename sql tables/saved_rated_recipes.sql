CREATE TABLE IF NOT EXISTS Saved_Rated_Recipes (
	email VARCHAR(255) NOT NULL,
	recipeID INT NOT NULL,
	title VARCHAR(255),
	imgURL VARCHAR(255),
	sourceURL VARCHAR(255),
	rating TINYINT DEFAULT NULL,
	PRIMARY KEY (email, recipeID),
	FOREIGN KEY (email) REFERENCES Users(email)
	);
