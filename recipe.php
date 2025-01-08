<?php include('includes/header.php'); ?>
<?php include('db.php'); ?>

<?php
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM recipes WHERE id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Get the average rating directly from the column
        
$ratingCount = $row['rating_count']; // Number of ratings
$averageRating = $row['average_rating'] !== null ? round($row['average_rating'], 1) : null;

// Display the average rating and the number of ratings
$ratingDisplay = $averageRating !== null 
    ? "{$averageRating}/5 ★ ({$ratingCount})" 
    : 'Ohodnoťte tento recept ako prvý';

        echo '<div class="recipe-detail">
            <p><strong>Hodnotenie:</strong> ' . htmlspecialchars($ratingDisplay) . '</p>
            <h2>' . htmlspecialchars($row['title']) . '</h2>
            <img src="images/' . htmlspecialchars($row['image']) . '" alt="Recipe Image">
            <ul>
                <!-- Add more content if needed -->
            </ul>
            <h3>Recept:</h3>
            <p>' . nl2br(htmlspecialchars($row['description'])) . '</p>
            <!-- Display rating section -->
            <div class="rating" id="rating-system">
                <span data-value="1">★</span>
                <span data-value="2">★</span>
                <span data-value="3">★</span>
                <span data-value="4">★</span>
                <span data-value="5">★</span>
            </div>
            <!-- Thank you message placeholder -->
            <div id="thank-you-message" style="display:none;">Ďakujeme za Vaše hodnotenie!</div>
          </div>';
    } else {
        echo "Recipe not found.";
    }
} else {
    echo "Invalid recipe ID.";
}
?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const ratingSystem = document.getElementById('rating-system');
    const stars = Array.from(ratingSystem.querySelectorAll('span'));
    const recipeId = <?php echo $id; ?>;
    const localStorageKey = `rated_${recipeId}`;

    // Check if the user has already rated
    if (localStorage.getItem(localStorageKey)) {
        ratingSystem.style.display = 'none'; // Hide rating stars if already rated
        document.getElementById('thank-you-message').style.display = 'block';
        return;
    }

    stars.forEach((star, index) => {
        // Highlight stars on mouseover
        star.addEventListener('mouseover', () => {
            updateStarColors(index);
        });

        // Remove highlight when mouse leaves
        star.addEventListener('mouseout', resetStarColors);

        // Set active stars on click
        star.addEventListener('click', () => {
            setActiveStars(index + 1);
        });
    });

    function updateStarColors(index) {
        // Highlight stars from left to the hovered star
        stars.forEach((star, i) => {
            star.classList.toggle('hovered', i <= index);
        });
    }

    function resetStarColors() {
        // Remove hover effect from all stars
        stars.forEach(star => {
            star.classList.remove('hovered');
        });
    }

    function setActiveStars(rating) {
        // Set active class on stars up to the clicked one
        stars.forEach((star, index) => {
            star.classList.toggle('active', index < rating);
        });

        // Make AJAX request to submit rating
        fetch('rate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ recipeId: recipeId, stars: rating })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Store the rating in local storage
                localStorage.setItem(localStorageKey, true);
                ratingSystem.style.display = 'none'; // Hide stars
                document.getElementById('thank-you-message').style.display = 'block'; // Show thank you message
            } else {
                alert(data.message || 'An error occurred. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
});
</script>

<style>
/* Basic styling for the rating system container */
.rating {
    font-size: 2em;
    display: inline-flex;
    gap: 5px;
    cursor: pointer;
}

/* Default color for stars */
.rating > span {
    color: grey;
    transition: color 0.2s;
}

/* Hover effect: highlight stars up to the hovered one */
.rating > span.hovered,
.rating > span.active {
    color: gold;
}

/* For hovering, make sure all stars up to the hovered one are highlighted */
.rating > span:hover ~ span {
    color: grey;
}
</style>

<?php include('includes/footer.php'); ?>
