<?php
session_start();
include 'db.php';


// Check if the admin is logged in
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

// Form for adding ingredients
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_ingredient'])) {
    $ingredientName = trim($_POST['ingredient_name']);
    if (!empty($ingredientName)) {
        // Check if the ingredient already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ingredients WHERE name = ?");
        $stmt->bind_param("s", $ingredientName);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            // Ingredient already exists
            echo "<script>alert('Ingredient already exists!');</script>";
        } else {
            // Ingredient does not exist, proceed to insert
            $stmt = $conn->prepare("INSERT INTO ingredients (name) VALUES (?)");
            $stmt->bind_param("s", $ingredientName);
            $stmt->execute();
            $stmt->close();
            header('Location: admin.php');
            exit();
        }
    }
}

// Updating ingredients
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_ingredient'])) {
    $id = intval($_POST['ingredient_id']);
    $newName = trim($_POST['ingredient_name']);
    if (!empty($newName)) {
        $stmt = $conn->prepare("UPDATE ingredients SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $newName, $id);
        $stmt->execute();
        header('Location: admin.php');
        exit();
    }
}

// Deleting ingredients
if (isset($_GET['delete_ingredient'])) {
    $id = intval($_GET['delete_ingredient']);
    $stmt = $conn->prepare("DELETE FROM ingredients WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header('Location: admin.php');
    exit();
}

// Form for adding recipes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_recipe'])) {
    $title = trim($_POST['title']);
    $image = $_FILES['image']['name'];
    $instructions = trim($_POST['instructions']);
    $ingredients = trim($_POST['ingredients']); // IDs as comma-separated string
    $prepTime = trim($_POST['prep_time']); // New field

    // Image upload
    $targetDir = "images/";
    $targetFile = $targetDir . basename($image);
    move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO recipes (title, image, description, ingredients, prep_time) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $title, $image, $instructions, $ingredients, $prepTime);
    $stmt->execute();
    header('Location: admin.php');
    exit();
}


// Updating recipes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_recipe'])) {
    $id = intval($_POST['recipe_id']);
    $title = trim($_POST['title']);
    $instructions = trim($_POST['instructions']);
    $ingredients = trim($_POST['ingredients']); 
    $prepTime = trim($_POST['prep_time']); // New field

    // Check if a new image is uploaded
    $image = $_FILES['image']['name'];
    if ($image) {
        $targetDir = "images/";
        $targetFile = $targetDir . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
        $stmt = $conn->prepare("UPDATE recipes SET title = ?, image = ?, description = ?, ingredients = ?, prep_time = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $title, $image, $instructions, $ingredients, $prepTime, $id);
    } else {
        $stmt = $conn->prepare("UPDATE recipes SET title = ?, description = ?, ingredients = ?, prep_time = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $title, $instructions, $ingredients, $prepTime, $id);
    }

    $stmt->execute();
    header('Location: admin.php');
    exit();
}


// Deleting recipes
if (isset($_GET['delete_recipe'])) {
    $id = intval($_GET['delete_recipe']);
    $stmt = $conn->prepare("DELETE FROM recipes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header('Location: admin.php');
    exit();
}

// Fetch ingredients for displaying
$ingredientsResult = $conn->query("SELECT * FROM ingredients");

// Fetch recipes for displaying
$recipesResult = $conn->query("SELECT * FROM recipes");

// Check if an ingredient or recipe edit form should be displayed
$editIngredientId = isset($_GET['edit_ingredient']) ? intval($_GET['edit_ingredient']) : null;
$editRecipeId = isset($_GET['edit_recipe']) ? intval($_GET['edit_recipe']) : null;

$ingredientToEdit = null;
$recipeToEdit = null;

// Fetch ingredient details if editing
if ($editIngredientId) {
    $stmt = $conn->prepare("SELECT * FROM ingredients WHERE id = ?");
    $stmt->bind_param("i", $editIngredientId);
    $stmt->execute();
    $ingredientToEdit = $stmt->get_result()->fetch_assoc();
}

// Fetch recipe details if editing
if ($editRecipeId) {
    $stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ?");
    $stmt->bind_param("i", $editRecipeId);
    $stmt->execute();
    $recipeToEdit = $stmt->get_result()->fetch_assoc();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Admin Panel</h1>

        <div class="section">
            <h2>Manage Ingredients</h2>
            <form method="POST" action="admin.php">
                <input type="text" name="ingredient_name" placeholder="Add new ingredient" required>
                <button type="submit" name="add_ingredient">Add Ingredient</button>
            </form>

            <ul class="ingredient-list">
                <?php 
                // Reset and fetch ingredients again for displaying in both sections
                $ingredientsResult->data_seek(0);
                while ($ingredient = $ingredientsResult->fetch_assoc()): ?>
                    <li>
                        <?php echo htmlspecialchars($ingredient['name']); ?>
                        <a href="admin.php?delete_ingredient=<?php echo $ingredient['id']; ?>" class="delete-btn">Delete</a>
                        <a href="admin.php?edit_ingredient=<?php echo $ingredient['id']; ?>" class="edit-btn">Edit</a>
                    </li>
                <?php endwhile; ?>
            </ul>

            <!-- Edit Ingredient Form -->
            <?php if ($ingredientToEdit): ?>
                <form method="POST" action="admin.php" style="margin-top: 10px;">
                    <input type="hidden" name="ingredient_id" value="<?php echo $ingredientToEdit['id']; ?>">
                    <input type="text" name="ingredient_name" value="<?php echo htmlspecialchars($ingredientToEdit['name']); ?>" required>
                    <button type="submit" name="edit_ingredient">Update Ingredient</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>Manage Recipes</h2>
            <form id="recipeForm" method="POST" action="admin.php" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Recipe Title" required>
    <input type="file" name="image" required>
    <textarea name="instructions" placeholder="Instructions" required></textarea>
    <input type="text" name="prep_time" placeholder="Preparation Time" required> <!-- New field -->

    <!-- Dropdown for selecting ingredients -->
    <select id="ingredientDropdown">
        <option value="">Select Ingredient</option>
        <?php 
        // Fetch and display ingredients
        $ingredientsResult->data_seek(0);
        while ($ingredient = $ingredientsResult->fetch_assoc()): ?>
            <option value="<?php echo $ingredient['id']; ?>"><?php echo htmlspecialchars($ingredient['name']); ?></option>
        <?php endwhile; ?>
    </select>
    <button type="button" id="addIngredientBtn">Add Ingredient</button>

    <!-- Display selected ingredients -->
    <ul id="selectedIngredients"></ul>

    <!-- Hidden input to store ingredient IDs -->
    <input type="hidden" name="ingredients" id="ingredientsInput">

    <button type="submit" name="add_recipe">Add Recipe</button>
</form>


            <ul class="recipe-list">
                <?php while ($recipe = $recipesResult->fetch_assoc()): ?>
                    <li>
                        <?php echo htmlspecialchars($recipe['title']); ?>
                        <a href="admin.php?delete_recipe=<?php echo $recipe['id']; ?>" class="delete-btn">Delete</a>
                        <a href="admin.php?edit_recipe=<?php echo $recipe['id']; ?>" class="edit-btn">Edit</a>
                    </li>
                <?php endwhile; ?>
            </ul>

            <!-- Edit Recipe Form -->
            <?php if ($recipeToEdit): ?>
                <form method="POST" action="admin.php" enctype="multipart/form-data" style="margin-top: 10px;">
    <input type="hidden" name="recipe_id" value="<?php echo $recipeToEdit['id']; ?>">
    <input type="text" name="title" value="<?php echo htmlspecialchars($recipeToEdit['title']); ?>" required>
    <?php if ($recipeToEdit['image']): ?>
        <img src="./images/<?php echo $recipeToEdit['image']; ?>" width="200px">
    <?php endif; ?>
    <input type="file" name="image">
    <textarea name="instructions" required><?php echo htmlspecialchars($recipeToEdit['description']); ?></textarea>
    <input type="text" name="prep_time" value="<?php echo htmlspecialchars($recipeToEdit['prep_time']); ?>" required> <!-- New field -->

    <!-- Repeat dropdown and list for editing -->
    <select id="ingredientDropdownEdit">
        <option value="">Select Ingredient</option>
        <?php 
        // Fetch and display ingredients
        $ingredientsResult->data_seek(0);
        while ($ingredient = $ingredientsResult->fetch_assoc()): ?>
            <option value="<?php echo $ingredient['id']; ?>"><?php echo htmlspecialchars($ingredient['name']); ?></option>
        <?php endwhile; ?>
    </select>
    <button type="button" id="addIngredientBtnEdit">Add Ingredient</button>

    <!-- Display selected ingredients -->
    <ul id="selectedIngredientsEdit">
        <?php
        $currentIngredients = explode(',', $recipeToEdit['ingredients']);
        foreach ($currentIngredients as $ingredientId) {
            $ingredientId = intval($ingredientId);
            $ingredientResult = $conn->query("SELECT name FROM ingredients WHERE id = $ingredientId");
            if ($ingredientRow = $ingredientResult->fetch_assoc()) {
                echo "<li data-id='$ingredientId'>" . htmlspecialchars($ingredientRow['name']) . 
                "<button type='button' class='remove-btn'>Remove</button></li>";
            }
        }
        ?>
    </ul>

    <!-- Hidden input to store ingredient IDs -->
    <input type="hidden" name="ingredients" id="ingredientsInputEdit" value="<?php echo $recipeToEdit['ingredients'] ?>">

    <button type="submit" name="edit_recipe">Update Recipe</button>
</form>

            <?php endif; ?>
        </div>
    </div>
    

    
    
    
    

    <script>
document.addEventListener('DOMContentLoaded', function() {
    sortDropdownOptions('ingredientDropdown');
    sortDropdownOptions('ingredientDropdownEdit');
});

// Alphabet order sorting function
function sortDropdownOptions(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const options = Array.from(dropdown.options);

    // Define Slovak alphabetical order including diacritics
    const slovakAlphabet = [
        'a', 'á', 'ä', 'b', 'c', 'č', 'd', 'ď', 'dz', 'dž', 'e', 'é', 'f', 'g', 'h', 'ch', 
        'i', 'í', 'j', 'k', 'l', 'ĺ', 'ľ', 'm', 'n', 'ň', 'o', 'ó', 'ô', 'p', 'q', 'r', 
        'ŕ', 's', 'š', 't', 'ť', 'u', 'ú', 'v', 'w', 'x', 'y', 'ý', 'z', 'ž'
    ];

    options.sort((a, b) => {
        const textA = a.text.toLowerCase();
        const textB = b.text.toLowerCase();

        // Compare character by character according to Slovak alphabet
        for (let i = 0; i < Math.max(textA.length, textB.length); i++) {
            const charA = textA[i] || '';
            const charB = textB[i] || '';
            
            const indexA = slovakAlphabet.indexOf(charA);
            const indexB = slovakAlphabet.indexOf(charB);
            
            // Handle characters not in the Slovak alphabet
            if (indexA === -1 || indexB === -1) {
                return charA.localeCompare(charB);
            }

            // Compare based on Slovak alphabetical index
            if (indexA !== indexB) {
                return indexA - indexB;
            }
        }
        return 0; // Return 0 if both strings are equal
    });

    // Clear the dropdown and append sorted options
    dropdown.innerHTML = '';
    options.forEach(option => dropdown.appendChild(option));
}

document.getElementById('addIngredientBtn').addEventListener('click', function() {
    const dropdown = document.getElementById('ingredientDropdown');
    const selectedValue = dropdown.value;
    const selectedText = dropdown.options[dropdown.selectedIndex].text;

    if (selectedValue) {
        const selectedIngredients = document.querySelectorAll('#selectedIngredients li');
        for (let item of selectedIngredients) {
            if (item.dataset.id === selectedValue) {
                alert('Ingredient already added.');
                return;
            }
        }

        const li = document.createElement('li');
        li.textContent = selectedText;
        li.dataset.id = selectedValue;

        const removeBtn = document.createElement('button');
        removeBtn.textContent = 'Remove';
        removeBtn.style.marginLeft = '10px';
        removeBtn.onclick = function() {
            li.remove();
            updateIngredientsInput();
        };
        li.appendChild(removeBtn);

        document.getElementById('selectedIngredients').appendChild(li);

        updateIngredientsInput();
    } else {
        alert('Please select an ingredient.');
    }
});

function updateIngredientsInput() {
    const selectedIngredients = document.querySelectorAll('#selectedIngredients li');
    const ingredientIds = Array.from(selectedIngredients).map(item => item.dataset.id);
    document.getElementById('ingredientsInput').value = ingredientIds.join(',');
}

document.getElementById('addIngredientBtnEdit').addEventListener('click', function() {
    const dropdown = document.getElementById('ingredientDropdownEdit');
    const selectedValue = dropdown.value;
    const selectedText = dropdown.options[dropdown.selectedIndex].text;

    if (selectedValue) {
        const selectedIngredients = document.querySelectorAll('#selectedIngredientsEdit li');
        for (let item of selectedIngredients) {
            if (item.dataset.id === selectedValue) {
                alert('Ingredient already added.');
                return;
            }
        }

        const li = document.createElement('li');
        li.textContent = selectedText;
        li.dataset.id = selectedValue;

        const removeBtn = document.createElement('button');
        removeBtn.textContent = 'Remove';
        removeBtn.style.marginLeft = '10px';
        removeBtn.onclick = function() {
            li.remove();
            updateIngredientsInputEdit();
        };
        li.appendChild(removeBtn);

        document.getElementById('selectedIngredientsEdit').appendChild(li);

        updateIngredientsInputEdit();
    } else {
        alert('Please select an ingredient.');
    }
});

function updateIngredientsInputEdit() {
    const selectedIngredients = document.querySelectorAll('#selectedIngredientsEdit li');
    const ingredientIds = Array.from(selectedIngredients).map(item => item.dataset.id);
    document.getElementById('ingredientsInputEdit').value = ingredientIds.join(',');
}

// Add remove functionality to existing ingredients in the edit form
document.querySelectorAll('#selectedIngredientsEdit .remove-btn').forEach(button => {
    button.addEventListener('click', function() {
        this.parentElement.remove();
        updateIngredientsInputEdit();
    });
});

    </script>
</body>
</html>


>

<style>

body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
}

.container {
    width: 90%;
    max-width: 1200px;
    margin: 20px auto;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    display: flex;
    gap: 20px; /* Space between columns */
}

.column {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.column-left {
    flex: 1;
}

.column-middle {
    flex: 1;
    align-items: center;
}

.column-right {
    flex: 1;
}

h1 {
    text-align: center;
    margin-bottom: 20px;
}

form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

input[type="text"], input[type="file"], textarea {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

button {
    padding: 10px 20px;
    border: none;
    background-color: #4CAF50;
    color: #fff;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #45a049;
}

ul {
    list-style: none;
    padding: 0;
}

li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 10px;
    border: 1px solid #ddd;
    margin-bottom: 5px;
    border-radius: 4px;
    background-color: #fafafa;
}

.delete-btn {
    color: red;
    text-decoration: none;
    cursor: pointer;
}

.image-preview {
    max-width: 100%; /* Keep the image within the bounds of its container */
    border-radius: 4px;
    margin-bottom: 10px;
}

.image-upload {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: center;
}

textarea {
    resize: vertical;
    min-height: 100px;
}

</style>