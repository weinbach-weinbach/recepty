<?php 
include('includes/header.php'); 
include('db.php'); 
?>

<div class="search-results">
    <?php 
    echo "<h2>Za menej ako " . htmlspecialchars(isset($_GET['prep-time']) ? $_GET['prep-time'] : '', ENT_QUOTES, 'UTF-8') . " minút môžete mať na tanieri napríklad:</h2>";

    if (isset($_GET['ingredients']) && !empty($_GET['ingredients'])) {
        // Sanitize and trim ingredients from the query string
        $inputIngredients = array_map('trim', explode(',', $_GET['ingredients'])); 

        // Fetch ingredient ID mappings from the database
        $ingredientIdMapping = [];
        $idMappingQuery = "SELECT id, name FROM ingredients";
        $idMappingResult = $conn->query($idMappingQuery);

        if ($idMappingResult) {
            while ($row = $idMappingResult->fetch_assoc()) {
                $ingredientIdMapping[$row['name']] = $row['id'];
            }
        }

        // Convert input ingredient names to IDs
        $ingredientIds = [];
        foreach ($inputIngredients as $ingredient) {
            $ingredient = trim($ingredient);
            if (isset($ingredientIdMapping[$ingredient])) {
                $ingredientIds[] = $ingredientIdMapping[$ingredient];
            }
        }

        // Get preparation time filter with validation
        $maxPrepTime = isset($_GET['prep-time']) ? intval($_GET['prep-time']) : 30;
        if ($maxPrepTime < 0) {
            $maxPrepTime = 30; // default value
        }

        // Get minimum rating filter from the query string, default to no filter
        $minRating = isset($_GET['min-rating']) ? floatval($_GET['min-rating']) : 0;

        if (count($ingredientIds) > 0) {
            // Prepare SQL query
            $sql = "SELECT id, title, description, image, prep_time, average_rating, ingredients 
                    FROM recipes 
                    WHERE prep_time <= ?";

            if ($minRating > 0) {
                // Only include recipes with an average rating greater than or equal to the minimum rating
                $sql .= " AND average_rating >= ?";
            }

            // Prepare and bind the statement
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                die("Error preparing statement: " . $conn->error);
            }

            if ($minRating > 0) {
                $stmt->bind_param("id", $maxPrepTime, $minRating); // 'i' for integer (prep time), 'd' for double (min rating)
            } else {
                $stmt->bind_param("i", $maxPrepTime);
            }

            // Execute the statement
            $stmt->execute();
            if ($stmt->error) {
                die("Execution error: " . $stmt->error);
            }

            // Fetch the result
            $result = $stmt->get_result();
            if (!$result) {
                die("Error fetching results: " . $conn->error);
            }

            // Process the results
            if ($result) {
                $recipes = [];
                while ($row = $result->fetch_assoc()) {
                    // Use the existing average_rating directly
                    $averageRating = $row['average_rating'];

                    // Convert comma-separated ingredient IDs into an array
                    $recipeIngredients = array_map('trim', explode(',', $row['ingredients'])); 

                    // Check if the recipe ingredients intersect with the searched ingredients
                    $matchingIngredients = array_intersect($ingredientIds, $recipeIngredients);
                    $matches = count($matchingIngredients); 

                    if ($matches > 0) {
                        $recipes[] = [
                            'id' => $row['id'],
                            'title' => $row['title'],
                            'description' => $row['description'],
                            'image' => $row['image'],
                            'prep_time' => $row['prep_time'],
                            'matches' => $matches,
                            'total' => count($recipeIngredients),
                            'average_rating' => $averageRating // Directly use the existing average_rating
                        ];
                    }
                }

                // Sort recipes by the number of matched ingredients in descending order
                usort($recipes, function($a, $b) {
                    return $b['matches'] - $a['matches'];
                });

                // Display recipes
                if (!empty($recipes)) {
                    foreach ($recipes as $recipe) {
                        $ratingDisplay = isset($recipe['average_rating']) ? round($recipe['average_rating'], 1) . '/5 ★' : 'Zatiaľ bez hodnotenia';
                        echo '<div class="recipe-card">
                                <div class="rating-corner">' . htmlspecialchars($ratingDisplay, ENT_QUOTES, 'UTF-8') . '</div>
                                <img src="images/' . htmlspecialchars($recipe['image'], ENT_QUOTES, 'UTF-8') . '" alt="Recipe Image">
                                <h3>' . htmlspecialchars($recipe['title'], ENT_QUOTES, 'UTF-8') . '</h3>
                                <p>' . htmlspecialchars(substr($recipe['description'], 0, 52), ENT_QUOTES, 'UTF-8') . '...</p>
                                <p>Už máte ' . htmlspecialchars($recipe['matches'], ENT_QUOTES, 'UTF-8') . '/' . htmlspecialchars($recipe['total'], ENT_QUOTES, 'UTF-8') . ' ingrediencií</p>
                                <a href="recipe.php?id=' . htmlspecialchars($recipe['id'], ENT_QUOTES, 'UTF-8') . '">Pozrieť recept</a>
                              </div>';
                    }
                } else {
                    echo "Nenašli sa žiadne recepty.";
                }
            } else {
                echo "Error fetching recipes: " . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8');
            }
        } else {
            echo "No valid ingredients selected.";
        }
    } else {
        echo "Nezadali ste ingrediencie.";
    }
    ?>
</div>


<style>

.recipe-card {
    position: relative; /* Make the card the reference for absolute positioning */
    border: 1px solid #ccc;
    padding: 15px;
    margin-bottom: 20px;
    overflow: hidden; /* Ensures content doesn't overflow the card */
}

.rating-corner {
    position: absolute;
    top: 20px; /* Adjust as needed */
    left: 20px; /* Adjust as needed */
    background-color: rgba(255, 255, 255, 0.8); /* Light background for readability */
    padding: 5px;
    border-radius: 3px;
    font-size: 0.9em; /* Adjust size as needed */
    color: #333;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2); /* Optional: adds shadow for better visibility */
}

    
</style>

<?php include('includes/footer.php'); ?>
