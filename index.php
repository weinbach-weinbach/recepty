<?php include('includes/header.php'); ?>
<?php include('db.php'); ?>

<main>
    <div id="selected-ingredients"></div>
    <div class="search-container">
        <input type="text" id="search-bar" onkeyup="showSuggestions(this.value)" placeholder="Čo máte v chladničke?">
        <div id="suggestions"></div>
        <input type="hidden" id="selected-ingredients-input" name="ingredients">
        
        <form id="search-form" method="GET" action="search.php">
            <!-- Container for dropdown and button -->
            <div class="time-button-container">
                <!-- Dropdown menu for preparation time -->
                <div class="time-dropdown">
                    <label for="prep-time">Čas varenia: 0 až </label>
                    <select id="prep-time" name="prep-time">
                        <option value="10" <?php echo isset($_GET['prep-time']) && $_GET['prep-time'] == '10' ? 'selected' : ''; ?>>10 minút</option>
                        <option value="15" <?php echo isset($_GET['prep-time']) && $_GET['prep-time'] == '15' ? 'selected' : ''; ?>>15 minút</option>
                        <option value="30" <?php echo !isset($_GET['prep-time']) || $_GET['prep-time'] == '30' ? 'selected' : ''; ?>>30 minút</option>
                        <option value="45" <?php echo isset($_GET['prep-time']) && $_GET['prep-time'] == '45' ? 'selected' : ''; ?>>45 minút</option>
                        <option value="60" <?php echo isset($_GET['prep-time']) && $_GET['prep-time'] == '60' ? 'selected' : ''; ?>>60 minút</option>
                        <option value="90" <?php echo isset($_GET['prep-time']) && $_GET['prep-time'] == '90' ? 'selected' : ''; ?>>90 minút</option>
                        <option value="120" <?php echo isset($_GET['prep-time']) && $_GET['prep-time'] == '120' ? 'selected' : ''; ?>>120 minút</option>
                    </select>
                </div>
                
                <!-- Button to trigger search -->
                <button type="button" onclick="performSearch()" class="cook-button">Variť!</button>
            </div>
            
             <!-- More Filters Button now appears after the time-button-container -->
    <div class="more-filters-container">
        <button type="button" id="toggle-button" onclick="toggleFilters()" class="more-filters-button">Viac filtrov</button>
    </div>

    <!-- Hidden Filter Section -->
    <div id="more-filters" class="hidden-filters" style="display: none;">
        <label for="min-rating">Minimálne hodnotenie: </label>
        <select id="min-rating" name="min-rating">
            <option value="">Nezáleží</option>
            <option value="0.5">0.5 ★</option>
            <option value="1">1 ★</option>
            <option value="1.5">1.5 ★</option>
            <option value="2">2 ★</option>
            <option value="2.5">2.5 ★</option>
            <option value="3">3 ★</option>
            <option value="3.5">3.5 ★</option>
            <option value="4">4 ★</option>
            <option value="4.5">4.5 ★</option>
            <option value="5">5 ★</option>
        </select>
    </div>


            
            <!-- Hidden input for ingredients -->
            <input type="hidden" id="selected-ingredients-input" name="ingredients">
        </form>

    </div>
</main>


<style>

    
/* General mobile-first styles */
.main-container {
    padding: 16px; /* Adequate padding for mobile devices */
}

.search-container {
    display: flex;
    flex-direction: column; /* Stack elements vertically on mobile */
    gap: 12px; /* Space between input and other elements */
}

/* Container for dropdown and button */
.time-button-container {
    display: flex;
    flex-direction: column; /* Stack items vertically on mobile */
    align-items: stretch; /* Make items fill available width */
    gap: 8px; /* Space between dropdown and button */
}

/* Style for the dropdown container */
.time-dropdown {
    display: flex;
    flex-direction: row; /* Keep label and dropdown in a row */
    align-items: center; /* Vertically centers label and dropdown */
    gap: 5px; /* Adds slight gap between label and dropdown */
    width: 100%; /* Use full width on mobile */
}

/* Style for the button */
.cook-button {
    width: 100%; /* Full width button on mobile */
    padding: 12px; /* Increase padding for touch targets */
    background-color: #f39c12;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

/* Optional: Additional hover effect for the button */
.cook-button:hover {
    background-color: #e67e22;
}

/* Input and button styling */
#search-bar {
    width: 100%; /* Full width for better usability */
    padding: 10px; /* Adequate padding for mobile */
    border: 1px solid #ccc; /* Define border */
    border-radius: 4px; /* Rounded corners */
    font-size: 16px; /* Adequate font size for readability */
}

#suggestions {
    margin-top: 4px; /* Space suggestions below input */
    max-height: 150px; /* Limit height for scroll */
    overflow-y: auto; /* Enable scrolling for long lists */
    border: 1px solid #ccc; /* Match input styling */
    border-radius: 4px; /* Rounded corners */
}
    
/* Style for the dropdown to blend with the label */
#prep-time {
    border: none; /* Remove border for a smooth look */
    border-bottom: 2px solid #333; /* Add bottom border to match text styling */
    background-color: transparent; /* Transparent background */
    font-size: 16px; /* Same font size as the label */
    padding: 5px; /* Add padding for better click area */
    color: #333; /* Text color to match the label */
    cursor: pointer; /* Pointer cursor for interactivity */
    appearance: none; /* Remove default dropdown arrow */
    -webkit-appearance: none; /* Remove dropdown arrow for Safari */
    -moz-appearance: none; /* Remove dropdown arrow for Firefox */
}

#min-rating {
    border: none; /* Remove border for a smooth look */
    border-bottom: 2px solid #333; /* Add bottom border to match text styling */
    background-color: transparent; /* Transparent background */
    font-size: 16px; /* Same font size as the label */
    padding: 5px; /* Add padding for better click area */
    color: #333; /* Text color to match the label */
    cursor: pointer; /* Pointer cursor for interactivity */
    appearance: none; /* Remove default dropdown arrow */
    -webkit-appearance: none; /* Remove dropdown arrow for Safari */
    -moz-appearance: none; /* Remove dropdown arrow for Firefox */
}

/* Media Query for larger screens */
@media (min-width: 768px) {
    .main-container {
        padding: 32px; /* Increase padding for larger screens */
        max-width: 800px; /* Limit the maximum width to prevent it from being too wide */
        margin: 0 auto; /* Center the content */
    }

    .search-container {
        flex-direction: row; /* Align elements horizontally */
        gap: 20px; /* Increase space between items */
        align-items: center; /* Center vertically */
    }

    .time-button-container {
        flex-direction: row; /* Horizontal layout on larger screens */
        justify-content: space-between; /* Spread elements across the container */
        gap: 10px; /* Space between dropdown and button */
        width: auto; /* Adjust width to fit content */
    }

    /* Dropdown and label alignment for larger screens */
    .time-dropdown {
        width: auto; /* Adjust width for desktop */
    }

    /* Button styling on larger screens */
    .cook-button {
        width: auto; /* Do not stretch button on desktop */
        padding: 10px 20px; /* Increase padding for a better desktop look */
    }

    #search-bar {
        max-width: 300px; /* Limit the width for readability */
        margin-right: 20px; /* Add space to the right */
    }
}


</style>
    

    <!-- Featured recipes display code -->
           <div class="search-tip">
            <p>Zadajte potraviny, ktoré máte doma a nechajte nás zostaviť pre Vás najlepšiu ponuku jedál na základe ingrediencií, ktoré už máte.</p>
        </div>
       
       <div class="featured-recipes">
        <h2>Najnovšie recepty</h2> 
        
        <!-- New div for the text content below the title -->
        <div class="featured-recipes-text">
            <p>Objavte našu najnovšiu zbierku chutných a ľahko pripraviteľných receptov. Od polievok po jedlá z cestovín - nájdite dokonalé jedlo, ktoré uspokojí Vaše chute.</p>
        </div>

        <?php
        $sql = "SELECT * FROM recipes ORDER BY date_created DESC LIMIT 6";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo '<div class="recipe-card">
                        <img src="images/' . $row['image'] . '" alt="Recipe Image">
                        <h3>' . $row['title'] . '</h3>
                        <p>' . substr($row['description'], 0, 50) . '...</p>
                        <a href="recipe.php?id=' . $row['id'] . '">Pozrieť recept</a>
                      </div>';
            }
        } else {
            echo "No recipes found.";
        }
        ?>
    </div>

<?php include('includes/footer.php'); ?>


<style>

.search-tip {
text-align: center
    }
    
    

</style>


<script>

// Function to toggle visibility of the more filters section
function toggleFilters() {
    var filters = document.getElementById('more-filters');
    var button = document.getElementById('toggle-button');
    
    if (filters.style.display === 'none' || filters.style.display === '') {
        filters.style.display = 'block';  // Zobraz filtre
        button.textContent = 'Skryť filtre';  // Zmeniť text tlačidla
    } else {
        filters.style.display = 'none';  // Skry filtre
        button.textContent = 'Viac filtrov';  // Zmeniť text tlačidla späť
    }
}


</script>