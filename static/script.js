let tripData = {
    source: '',
    destination: '',
    transport_type: '',
    transport_details: '',
    hotel_name: '',
    hotel_booking_link: '',
    total_cost: 0
};

// Coordinates for Indian cities
const cityCoords = {
    'delhi': { lat: '28.6139', lon: '77.2090' },
    'mumbai': { lat: '19.0760', lon: '72.8777' },
    'bangalore': { lat: '12.9716', lon: '77.5946' },
    'kolkata': { lat: '22.5726', lon: '88.3639' },
    'chennai': { lat: '13.0827', lon: '80.2707' }
};

function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
  }

async function fetchTripData() {
    const source = document.getElementById('source').value.trim();
    const destination = document.getElementById('destination').value.trim();
    
    if (!source || !destination) {
        alert('Please enter both source and destination');
        return;
    }

    if (source === destination) {
        alert('Error: Source and destination cannot be same');
        return;
    }

    const indianCities = [
        'delhi', 'mumbai', 'bangalore', 'kolkata', 'chennai',
        'jaipur', 'hyderabad', 'pune', 'ahmedabad', 'goa',
        'lucknow', 'kanpur', 'nagpur', 'indore', 'bhopal',
        'patna', 'ranchi', 'guwahati', 'amritsar', 'chandigarh',
        'coimbatore', 'madurai', 'kochi', 'thiruvananthapuram',
        'visakhapatnam', 'vijayawada', 'surat', 'rajkot',
        'vadodara', 'nashik', 'aurangabad', 'meerut',
        'jodhpur', 'udaipur', 'varanasi', 'allahabad',
        'dehradun', 'shimla', 'panaji', 'gangtok',
        'shillong', 'aizawl', 'imphal', 'agartala',
        'kohima', 'itnagar', 'leh', 'srinagar',
        'jammu', 'bilaspur', 'dhanbad', 'tiruchirappalli',
        'mangaluru', 'mysuru', 'salem', 'warangal',
        'nellore', 'guntur', 'kurnool', 'raipur',
        'bhubaneswar', 'cuttack', 'siliguri', 'howrah',
        'noida', 'ghaziabad', 'faridabad', 'gurgaon'
      ];
      
if (!indianCities.includes(destination)) {
alert('Error: Foreign cities not allowed');
return;
}

    tripData.source = source;
    tripData.destination = destination;

    // Show loading states
    document.getElementById('weather').innerHTML = '<p>Loading weather...</p>';
    document.getElementById('flights').innerHTML = '<p>Loading flights...</p>';
    document.getElementById('trains').innerHTML = '<p>Loading trains...</p>';
    document.getElementById('hotels').innerHTML = '<p>Loading hotels...</p>';
    document.getElementById('attractions').innerHTML = '<p>Loading attractions...</p>';

    // Fetch weather (already working)
    await fetchWeather(destination);
    
    // Fetch flights
    await fetchFlights(source, destination);
    
    // Fetch trains (using sample train number)
    await fetchTrains('12051'); // Rajdhani Express as sample
    
    // Fetch hotels
    await fetchHotels(destination);
    
    // Fetch attractions
    const destCoords = cityCoords[destination.toLowerCase()] || cityCoords['delhi'];
    await fetchAttractions(destCoords.lat, destCoords.lon);
    
    // Calculate total cost
    updateTotalCost();
}

// Individual API fetch functions
async function fetchWeather(city) {
    try {
        const response = await fetch(`http://localhost:5000/weather/${city}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.text();
        document.getElementById('weather').innerHTML = data;
        console.log("Weather data loaded successfully");
    } catch (error) {
        console.error("Failed to fetch weather:", error);
        document.getElementById('weather').innerHTML = '<p>Failed to load weather data</p>';
    }
}

async function fetchFlights(source, destination) {
    try {
        const response = await fetch(`http://localhost:5000/flights/${source}/${destination}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.text();
        document.getElementById('flights').innerHTML = data;
        console.log("Flight data loaded successfully");
        
        // Update trip data with first flight details
        tripData.transport_type = 'Flight';
        tripData.transport_details = `Flight from ${source} to ${destination}`;
    } catch (error) {
        console.error("Failed to fetch flights:", error);
        document.getElementById('flights').innerHTML = '<p>Failed to load flight data</p>';
    }
}

async function fetchTrains(trainNumber) {
    try {
        const response = await fetch(`http://localhost:5000/trains/${trainNumber}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.text();
        document.getElementById('trains').innerHTML = data;
        console.log("Train data loaded successfully");
        
        // Update trip data with train option
        if (!tripData.transport_type) {
            tripData.transport_type = 'Train';
            tripData.transport_details = `Train ${trainNumber}`;
        }
    } catch (error) {
        console.error("Failed to fetch trains:", error);
        document.getElementById('trains').innerHTML = '<p>Failed to load train data</p>';
    }
}

async function fetchHotels(destination) {
    try {
        const response = await fetch(`http://localhost:5000/hotels/${destination}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.text();
        document.getElementById('hotels').innerHTML = data;
        console.log("Hotel data loaded successfully");
        
        // Update trip data with hotel info
        tripData.hotel_name = `Hotel in ${destination}`;
        tripData.hotel_booking_link = 'http://example.com/book-hotel';
    } catch (error) {
        console.error("Failed to fetch hotels:", error);
        document.getElementById('hotels').innerHTML = '<p>Failed to load hotel data</p>';
    }
}

async function fetchAttractions(lat, lon) {
    const container = document.getElementById('attractions');
    container.innerHTML = '<div class="loading">Loading attractions...</div>';
    
    try {
        const response = await fetch(`http://localhost:5000/attractions/${lat}/${lon}`);
        
        // Debug: Log the response
        console.log("API Response:", {
            status: response.status,
            ok: response.ok,
            url: response.url
        });
        
        const html = await response.text();
        container.innerHTML = html;
        
        // Check if attractions were rendered
        const cards = container.querySelectorAll('.card');
        console.log(`Rendered ${cards.length} attraction cards`);
        
        if (cards.length === 0) {
            // Use the destination name for fallback
            const destination = container.getAttribute('data-destination');
            showFallbackAttractions(destination);
        }
        
    } catch (error) {
        console.error('Attractions fetch failed:', error);
        showFallbackAttractions();
    }
}

function showFallbackAttractions(destination = "Unknown") {
    const container = document.getElementById('attractions');
    const fallbackData = {
        mumbai: {
            title: "Popular Attractions in Mumbai",
            list: [
                "Gateway of India",
                "Chhatrapati Shivaji Terminus",
                "Marine Drive",
                "Elephanta Caves",
                "Haji Ali Dargah"
            ],
            link: "https://www.tripadvisor.com/Attractions-g304554-Activities-Mumbai_Maharashtra.html"
        },
        delhi: {
            title: "Popular Attractions in Delhi",
            list: [
                "Red Fort",
                "Qutub Minar",
                "India Gate",
                "Lotus Temple",
                "Humayun's Tomb"
            ],
            link: "https://www.tripadvisor.com/Attractions-g304551-Activities-New_Delhi_National_Capital_Territory_of_Delhi.html"
        },
        bangalore: {
            title: "Popular Attractions in Bangalore",
            list: [
                "Lalbagh Botanical Garden",
                "Bangalore Palace",
                "Cubbon Park",
                "ISKCON Temple",
                "Vidhana Soudha"
            ],
            link: "https://www.tripadvisor.com/Attractions-g297628-Activities-Bengaluru_Bangalore_District_Karnataka.html"
        },
        kolkata: {
            title: "Popular Attractions in Kolkata",
            list: [
                "Victoria Memorial",
                "Howrah Bridge",
                "Dakshineswar Kali Temple",
                "Indian Museum",
                "Marble Palace"
            ],
            link: "https://www.tripadvisor.com/Attractions-g304558-Activities-Kolkata_Calcutta_Kolkata_District_West_Bengal.html"
        },
        jodhpur: {
            title: "Popular Attractions in Jodhpur",
            list: [
                "Mehrangarh Fort",
                "Jaswant Thada",
                "Umaid Bhawan Palace",
                "Clock Tower",
                "Mandore Gardens"
            ],
            link: "https://www.tripadvisor.com/Attractions-g297668-Activities-Jodhpur_Jodhpur_District_Rajasthan.html"
        }
    };

    const city = destination.toLowerCase();  // Ensure city is lowercase
    const data = fallbackData[city];  // Match with lowercase keys

    if (data) {
        const listItems = data.list.map(item => `<li>${item}</li>`).join('');
        container.innerHTML = ` 
            <div class="fallback-attractions">
                <h3>${data.title}</h3>
                <ul>${listItems}</ul>
                <p>View more on <a href="${data.link}" target="_blank">TripAdvisor</a></p>
            </div>
        `;
    } else {
        container.innerHTML = `
            <div class="fallback-attractions">
                <h3>No fallback attractions available for "${destination}"</h3>
                <p>Please try another city or try again later.</p>
            </div>
        `;
    }
}

    

function updateTotalCost() {
    // Simple cost calculation - should be enhanced with real data
    const flightCost = getRandomInt(5000, 10000); // Sample flight cost
    const hotelCost = getRandomInt(3000, 6000); // Sample hotel cost per night
    
    tripData.total_cost = flightCost + hotelCost;
    document.getElementById('totalCost').innerHTML = `
        <div class="cost-card">
            <h3>Estimated Trip Cost</h3>
            <p>Transport: ₹${flightCost}</p>
            <p>Accommodation: ₹${hotelCost}</p>
            <p class="total">Total: ₹${tripData.total_cost}</p>
        </div>
    `;
}

async function saveTrip() {
    try {
        const response = await fetch('save_trip.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(tripData)
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            alert('Trip saved successfully!');
            window.location.reload();
        } else {
            console.error('Save trip error:', result);
            alert(`Failed to save trip: ${result.message}\n${result.mysql_error || ''}`);
        }
    } catch (error) {
        console.error('Network error:', error);
        alert('Network error while saving trip. See console for details.');
    }
}

// Add this to script.js
function validateDates() {
    const departure = document.querySelector('input[type="date"][placeholder="Departure"]').value;
    const returnDate = document.querySelector('input[type="date"][placeholder="Return"]').value;
    
    if (departure && returnDate) {
        const depDate = new Date(departure);
        const retDate = new Date(returnDate);
        
        // TC06: End date must be after start date
        if (retDate <= depDate) {
            alert('Error: End date must be after start date');
            return false;
        }
    }
    
    // TC16: Date format validation
    const dateRegex = /^\d{2}-\d{2}-\d{4}$/;
    if (departure && !departure.match(dateRegex)) {
        alert('Error: Invalid date format (DD-MM-YYYY required)');
        return false;
    }
    
    return true;
}

// Modify the search button onclick to call validateDates first
document.querySelector('.search-form button').onclick = function() {
    if (validateDates()) {
        fetchTripData();
    }
};

// Add this to script.js
function validatePassengers() {
    const passengerSelect = document.querySelector('.search-form select');
    const passengers = parseInt(passengerSelect.value);
    
    // TC05 and TC18: Passenger count validation
    if (isNaN(passengers) || passengers <= 0) {
        alert('Error: Number of passengers must be > 0');
        return false;
    }
    
    return true;
}

// Update the search button onclick to include passenger validation
document.querySelector('.search-form button').onclick = function() {
    if (validateDates() && validatePassengers()) {
        fetchTripData();
    }
};