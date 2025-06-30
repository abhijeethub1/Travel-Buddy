from flask import Flask, render_template, jsonify
import requests
from flask_cors import CORS
import time

app = Flask(__name__)
CORS(app, resources={
    r"/weather/*": {"origins": "*"},
    r"/flights/*": {"origins": "*"},
    r"/trains/*": {"origins": "*"},
    r"/hotels/*": {"origins": "*"},
    r"/attractions/*": {"origins": "*"}
})

# API Keys
OPENWEATHER_API_KEY = '4507e48d561dbb6802fda1a6f3b3de05'
AVIATION_API_KEY = 'be32469503865af7264edcfbd86e4f17'
RAPIDAPI_KEY = 'fab52c61abmshafa0e245c7e8269p1e51a4jsn100a22708aad'
OPENTRIPMAP_API_KEY = '5ae2e3f221c38a28845f05b60acb4b74543659b2b5e2d8193d8ed4a3'

@app.route('/weather/<city>')
def get_weather(city):
    try:
        url = f'https://api.openweathermap.org/data/2.5/weather?q={city}&appid={OPENWEATHER_API_KEY}'
        response = requests.get(url)
        response.raise_for_status()
        data = response.json()
        return render_template('weather.html', data=data)
    except Exception as e:
        return jsonify({'error': f'Failed to fetch weather: {str(e)}'})

@app.route('/flights/<source>/<destination>')
def get_flights(source, destination):
    try:
        # Map city names to IATA codes for Indian cities
        iata_codes = {
            'delhi': 'DEL',
            'mumbai': 'BOM',
            'bangalore': 'BLR',
            'kolkata': 'CCU',
            'chennai': 'MAA'
        }
        source_iata = iata_codes.get(source.lower(), 'DEL')
        dest_iata = iata_codes.get(destination.lower(), 'BOM')
        url = f'http://api.aviationstack.com/v1/flights?access_key={AVIATION_API_KEY}&dep_iata={source_iata}&arr_iata={dest_iata}'
        response = requests.get(url)
        response.raise_for_status()
        data = response.json()
        return render_template('flights.html', flights=data.get('data', []))
    except Exception as e:
        return jsonify({'error': f'Failed to fetch flights: {str(e)}'})

@app.route('/trains/<train_number>')
def get_trains(train_number):
    try:
        url = f'https://indian-railway-irctc.p.rapidapi.com/api/trains-search/v1/train/{train_number}?isH5=true&client=web'
        headers = {
            'x-rapidapi-host': 'indian-railway-irctc.p.rapidapi.com',
            'x-rapidapi-key': RAPIDAPI_KEY,
            'x-rapid-api': 'rapid-api-database'  # Added missing header
        }
        response = requests.get(url, headers=headers, timeout=10)  # Added timeout
        response.raise_for_status()
        data = response.json()
        
        # Process the complex response structure
        trains = []
        if data.get('body'):
            for item in data['body']:
                if 'trains' in item:
                    trains.extend(item['trains'])
        
        return render_template('trains.html', trains=trains[:3])  # Limit to 3 trains
    except Exception as e:
        print(f"Train API Error: {str(e)}")
        return jsonify({'error': f'Failed to fetch trains: {str(e)}'})

@app.route('/hotels/<destination>')
def get_hotels(destination):
    try:
        url = "https://travel-advisor.p.rapidapi.com/hotels/list"
        headers = {
            "x-rapidapi-host": "travel-advisor.p.rapidapi.com",
            "x-rapidapi-key": RAPIDAPI_KEY
        }
        
        # Using coordinates for major Indian cities as fallback
        city_coords = {
            'delhi': '28.6139,77.2090',
            'mumbai': '19.0760,72.8777',
            'bangalore': '12.9716,77.5946',
            'kolkata': '22.5726,88.3639',
            'chennai': '13.0827,80.2707'
        }
        
        querystring = {
            "location_id": "1",  # Default location ID
            "adults": "1",
            "rooms": "1",
            "nights": "2",
            "offset": "0",
            "currency": "USD",
            "order": "asc",
            "limit": "5",  # Get top 5 hotels
            "sort": "recommended",
            "lang": "en_US"
        }
        
        # Try to use coordinates if destination is a known city
        if destination.lower() in city_coords:
            querystring["latitude"], querystring["longitude"] = city_coords[destination.lower()].split(',')
            del querystring["location_id"]  # Use coordinates instead of location_id
        
        response = requests.get(url, headers=headers, params=querystring, timeout=10)
        response.raise_for_status()
        data = response.json()
        
        hotels = []
        if isinstance(data, list):  # Check if response is a list
            for hotel in data[:5]:  # Limit to 5 hotels
                if isinstance(hotel, dict):  # Ensure each item is a dictionary
                    hotels.append({
                        'displayName': hotel.get('name', f'Hotel in {destination}'),
                        'rating': hotel.get('rating', '4.0'),
                        'bookingLink': hotel.get('web_url', 'https://www.tripadvisor.com/Hotels')
                    })
        
        return render_template('hotels.html', hotels=hotels or [{
            'displayName': f'Hotel in {destination}',
            'rating': '4.0',
            'bookingLink': 'https://www.tripadvisor.com/Hotels'
        }])
        
    except Exception as e:
        print(f"Hotel API Error: {str(e)}")
        # Return sample data if API fails
        return render_template('hotels.html', hotels=[{
            'displayName': f'Hotel in {destination}',
            'rating': '4.5',
            'bookingLink': 'https://www.tripadvisor.com/Hotels'
        }])
import requests

@app.route('/attractions/<lat>/<lon>')
def get_attractions(lat, lon):
    try:
        # Reverse geocoding to get city name
        city = "Unknown"
        try:
            geo_url = "https://nominatim.openstreetmap.org/reverse"
            geo_params = {
                'lat': lat,
                'lon': lon,
                'format': 'json',
                'zoom': 10,
                'addressdetails': 1
            }
            geo_headers = {
                'User-Agent': 'TravelBuddyApp (admin@123.com)'  # Use a valid user-agent
            }
            geo_response = requests.get(geo_url, params=geo_params, headers=geo_headers, timeout=5)
            geo_data = geo_response.json()
            city = geo_data.get('address', {}).get('city') or \
                   geo_data.get('address', {}).get('town') or \
                   geo_data.get('address', {}).get('state_district') or \
                   geo_data.get('address', {}).get('state') or "Unknown"
        except Exception as ge:
            print(f"Reverse geocoding failed: {ge}")

        # Fetch attractions from OpenTripMap
        url = "https://api.opentripmap.com/0.1/en/places/radius"
        params = {
            'lat': lat,
            'lon': lon,
            'radius': 10000,
            'apikey': '5ae2e3f221c38a28845f05b60acb4b74543659b2b5e2d8193d8ed4a3',
            'kinds': 'cultural,historic,architecture,religion,museums',
            'format': 'json',
            'limit': 20
        }

        response = requests.get(url, params=params, timeout=10)
        response.raise_for_status()
        data = response.json()

        attractions = []
        if data.get('features'):
            for feature in data['features']:
                if feature.get('properties', {}).get('name'):
                    attractions.append({
                        'id': feature.get('id'),
                        'name': feature['properties']['name'],
                        'distance': feature['properties'].get('dist', 0),
                        'kinds': feature['properties'].get('kinds', 'attraction'),
                        'coordinates': feature.get('geometry', {}).get('coordinates', []),
                        'rate': feature['properties'].get('rate', 0),
                        'osm': feature['properties'].get('osm')
                    })

        attractions = sorted(attractions, key=lambda x: (x['distance'], -x['rate']))[:10]

        return render_template('attractions.html', 
                               attractions=attractions,
                               location={'lat': lat, 'lon': lon},
                               destination=city)
    except Exception as e:
        print(f"Error fetching attractions: {str(e)}")
        return render_template('attractions.html', attractions=[], destination="Unknown")
if __name__ == '__main__':
    app.run(debug=True, port=5000)