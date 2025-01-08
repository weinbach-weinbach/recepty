function showSuggestions(value) {
    if (value.length === 0) {
        document.getElementById('suggestions').innerHTML = '';
        return;
    }

    fetch(`getSuggestions.php?query=${value}`)
        .then(response => response.json())
        .then(data => {
            // Create a collator for Slovak language
            const collator = new Intl.Collator('sk-SK', { sensitivity: 'base' });
            
            // Sort the suggestions according to Slovak alphabetical order
            data.sort((a, b) => collator.compare(a, b));

            let suggestions = data.map(item => `<li onclick="addIngredient('${item}')">${item}</li>`).join('');
            document.getElementById('suggestions').innerHTML = `<ul>${suggestions}</ul>`;
        })
        .catch(error => console.error('Error fetching suggestions:', error));
}


function addIngredient(ingredient) {
    let searchBar = document.getElementById('search-bar');
    let selectedIngredientsInput = document.getElementById('selected-ingredients-input');
    let currentIngredients = selectedIngredientsInput.value.split(',').filter(Boolean);

    if (!currentIngredients.includes(ingredient)) {
        currentIngredients.push(ingredient);
    }

    searchBar.value = '';
    document.getElementById('suggestions').innerHTML = '';
    renderSelectedIngredients(currentIngredients);

    selectedIngredientsInput.value = currentIngredients.join(',');
}

function renderSelectedIngredients(ingredients) {
    let container = document.getElementById('selected-ingredients');
    container.innerHTML = '';

    ingredients.forEach(ingredient => {
        let ingredientElement = document.createElement('div');
        ingredientElement.classList.add('selected-ingredient');

        let ingredientText = document.createTextNode(ingredient);
        
        let closeButton = document.createElement('span');
        closeButton.classList.add('close-btn');
        closeButton.textContent = 'x';
        
        ingredientElement.appendChild(ingredientText);
        ingredientElement.appendChild(closeButton);
        
        container.appendChild(ingredientElement);
        
        closeButton.addEventListener('click', function() {
            removeIngredient(ingredient);
        });
    });

    document.getElementById('selected-ingredients-input').value = ingredients.join(',');
}

function removeIngredient(ingredient) {
    let currentIngredients = document.getElementById('selected-ingredients-input').value.split(',').filter(Boolean);

    currentIngredients = currentIngredients.filter(item => item !== ingredient);

    renderSelectedIngredients(currentIngredients);
}

function performSearch() {
    // Get values from form inputs
    var ingredients = document.getElementById('selected-ingredients-input').value;
    var prepTime = document.getElementById('prep-time').value;
    var minRating = document.getElementById('min-rating').value;
    
    // Build the query string with both parameters
    const queryString = `search.php?ingredients=${encodeURIComponent(ingredients)}&prep-time=${encodeURIComponent(prepTime)}&min-rating=${encodeURIComponent(minRating)}`;
    
    // Redirect to the search page with the constructed query string
    window.location.href = queryString;
}














