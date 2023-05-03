CREATE TABLE IF NOT EXISTS Recipe_Ingredients (
	recipeID INT NOT NULL,
	ingredientID INT NOT NULL,
	amount INT DEFAULT NULL,
	unit VARCHAR(10) DEFAULT NULL,
	FOREIGN KEY (recipeID) REFERENCES Recipes(recipeID),
	FOREIGN KEY (ingredientID) REFERENCES Ingredients(ingredientID)
	);
