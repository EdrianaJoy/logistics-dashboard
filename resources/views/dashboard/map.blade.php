<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proximity Alert Dashboard</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
    <style>
        body {
            background: linear-gradient(135deg, #fce4ec 0%, #f8bbd9 50%, #f48fb1 100%);
            min-height: 100vh;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #e91e63;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        #map { height: 500px; width: 100%; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .controls { 
            margin: 20px 0; 
            padding: 15px; 
            background: rgba(255, 255, 255, 0.9); 
            border-radius: 8px; 
            border: 1px solid #e1bee7;
        }
        .alert-panel { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .alert-success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-danger { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .delivery-list { 
            margin: 20px 0; 
            padding: 15px; 
            background: rgba(255, 255, 255, 0.9); 
            border-radius: 8px; 
            border: 1px solid #e1bee7;
        }
        .delivery-item { 
            padding: 10px; 
            margin: 5px 0; 
            border: 1px solid #e1bee7; 
            border-radius: 5px; 
            background: rgba(255, 255, 255, 0.8);
        }
        .btn { padding: 8px 16px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background-color: #e91e63; color: white; }
        .btn-success { background-color: #e91e63; color: white; }
        .btn-danger { background-color: #e91e63; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Warehouse Delivery Proximity Dashboard</h1>
        
        <div class="controls">
            <label for="radius">Alert Radius (meters):</label>
            <select id="radius">
                <option value="100">100m</option>
                <option value="250" selected>250m</option>
                <option value="500">500m</option>
                <option value="1000">1km</option>
            </select>
            
            <button id="addDelivery" class="btn btn-primary">Add Delivery Point</button>
            <button id="clearAll" class="btn btn-danger">Clear All</button>
            <button id="checkAllProximity" class="btn btn-success">Check All Proximity</button>
        </div>

        <div id="map"></div>

        <div id="alerts"></div>

        <div class="delivery-list">
            <h3>Delivery Points</h3>
            <div id="deliveryItems"></div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
    <script>
        class ProximityDashboard {
            constructor() {
                this.map = null;
                this.warehouseMarker = null;
                this.deliveries = [];
                this.warehouseCoords = [14.5995, 120.9842]; // Default Manila coordinates
                this.radiusCircle = null;
                this.init();
            }

            init() {
                this.initMap();
                this.addWarehouse();
                this.bindEvents();
            }

            initMap() {
                this.map = L.map('map').setView(this.warehouseCoords, 13);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(this.map);

                // Add click event to map for adding delivery points
                this.map.on('click', (e) => {
                    if (document.getElementById('addDelivery').classList.contains('active')) {
                        this.addDeliveryPoint(e.latlng.lat, e.latlng.lng);
                        document.getElementById('addDelivery').classList.remove('active');
                    }
                });
            }

            addWarehouse() {
                this.warehouseMarker = L.marker(this.warehouseCoords, {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).addTo(this.map);

                this.warehouseMarker.bindPopup('<b>Warehouse</b><br>Main Distribution Center');
                this.updateRadiusCircle();
            }

            updateRadiusCircle() {
                if (this.radiusCircle) {
                    this.map.removeLayer(this.radiusCircle);
                }

                const radius = parseInt(document.getElementById('radius').value);
                this.radiusCircle = L.circle(this.warehouseCoords, {
                    color: 'blue',
                    fillColor: '#30f',
                    fillOpacity: 0.1,
                    radius: radius
                }).addTo(this.map);
            }

            addDeliveryPoint(lat, lng) {
                const deliveryId = this.deliveries.length + 1;
                const marker = L.marker([lat, lng], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).addTo(this.map);

                marker.bindPopup(`<b>Delivery ${deliveryId}</b><br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`);

                const delivery = {
                    id: deliveryId,
                    lat: lat,
                    lng: lng,
                    marker: marker,
                    status: 'pending'
                };

                this.deliveries.push(delivery);
                this.updateDeliveryList();
                this.checkProximity(delivery);
            }

            async checkProximity(delivery) {
                const radius = parseInt(document.getElementById('radius').value);
                
                try {
                    const response = await fetch('/api/proximity/check', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            warehouse: this.warehouseCoords,
                            delivery: [delivery.lat, delivery.lng],
                            radius: radius
                        })
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        delivery.distance = result.data.distance;
                        delivery.withinRange = result.data.within_range;
                        delivery.status = result.data.within_range ? 'in-range' : 'out-of-range';
                        
                        // Update marker color based on proximity
                        const iconColor = result.data.within_range ? 'green' : 'orange';
                        delivery.marker.setIcon(L.icon({
                            iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-${iconColor}.png`,
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        }));

                        this.updateDeliveryList();
                        this.showAlert(delivery);
                    }
                } catch (error) {
                    console.error('Error checking proximity:', error);
                    this.showAlert(null, 'Error checking proximity: ' + error.message);
                }
            }

            async checkAllProximity() {
                if (this.deliveries.length === 0) {
                    this.showAlert(null, 'No delivery points to check');
                    return;
                }

                const radius = parseInt(document.getElementById('radius').value);
                const deliveryData = this.deliveries.map(d => ({
                    id: d.id,
                    coords: [d.lat, d.lng]
                }));

                try {
                    const response = await fetch('/api/proximity/batch', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            warehouse: this.warehouseCoords,
                            deliveries: deliveryData,
                            radius: radius
                        })
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        Object.keys(result.results).forEach(deliveryId => {
                            const delivery = this.deliveries.find(d => d.id == deliveryId);
                            const proximityData = result.results[deliveryId];
                            
                            if (delivery && proximityData && !proximityData.error) {
                                delivery.distance = proximityData.distance;
                                delivery.withinRange = proximityData.within_range;
                                delivery.status = proximityData.within_range ? 'in-range' : 'out-of-range';
                                
                                const iconColor = proximityData.within_range ? 'green' : 'orange';
                                delivery.marker.setIcon(L.icon({
                                    iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-${iconColor}.png`,
                                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                                    iconSize: [25, 41],
                                    iconAnchor: [12, 41],
                                    popupAnchor: [1, -34],
                                    shadowSize: [41, 41]
                                }));
                            }
                        });
                        
                        this.updateDeliveryList();
                        this.showAlert(null, 'Batch proximity check completed!');
                    }
                } catch (error) {
                    console.error('Error in batch proximity check:', error);
                    this.showAlert(null, 'Error in batch check: ' + error.message);
                }
            }

            showAlert(delivery, message = null) {
                const alertsDiv = document.getElementById('alerts');
                
                if (message) {
                    alertsDiv.innerHTML = `<div class="alert-panel alert-danger">${message}</div>`;
                    return;
                }

                if (delivery) {
                    const alertClass = delivery.withinRange ? 'alert-success' : 'alert-danger';
                    const alertText = delivery.withinRange 
                        ? `✅ Delivery ${delivery.id} is within range (${delivery.distance}m)`
                        : `⚠️ Delivery ${delivery.id} is out of range (${delivery.distance}m)`;
                    
                    alertsDiv.innerHTML = `<div class="alert-panel ${alertClass}">${alertText}</div>`;
                }
            }

            updateDeliveryList() {
                const deliveryItemsDiv = document.getElementById('deliveryItems');
                deliveryItemsDiv.innerHTML = '';

                this.deliveries.forEach(delivery => {
                    const statusText = delivery.status === 'pending' ? 'Checking...' :
                                     delivery.status === 'in-range' ? `In Range (${delivery.distance}m)` :
                                     delivery.status === 'out-of-range' ? `Out of Range (${delivery.distance}m)` :
                                     'Unknown';

                    const statusClass = delivery.status === 'in-range' ? 'alert-success' :
                                       delivery.status === 'out-of-range' ? 'alert-danger' :
                                       '';

                    deliveryItemsDiv.innerHTML += `
                        <div class="delivery-item ${statusClass}">
                            <strong>Delivery ${delivery.id}</strong><br>
                            Coordinates: ${delivery.lat.toFixed(6)}, ${delivery.lng.toFixed(6)}<br>
                            Status: ${statusText}
                            <button onclick="dashboard.removeDelivery(${delivery.id})" class="btn btn-danger" style="float: right;">Remove</button>
                        </div>
                    `;
                });
            }

            removeDelivery(deliveryId) {
                const deliveryIndex = this.deliveries.findIndex(d => d.id === deliveryId);
                if (deliveryIndex > -1) {
                    this.map.removeLayer(this.deliveries[deliveryIndex].marker);
                    this.deliveries.splice(deliveryIndex, 1);
                    this.updateDeliveryList();
                }
            }

            clearAll() {
                this.deliveries.forEach(delivery => {
                    this.map.removeLayer(delivery.marker);
                });
                this.deliveries = [];
                this.updateDeliveryList();
                document.getElementById('alerts').innerHTML = '';
            }

            bindEvents() {
                document.getElementById('addDelivery').addEventListener('click', function() {
                    this.classList.toggle('active');
                    if (this.classList.contains('active')) {
                        this.textContent = 'Click on map to add delivery';
                        this.style.backgroundColor = '#ffc107';
                    } else {
                        this.textContent = 'Add Delivery Point';
                        this.style.backgroundColor = '#007bff';
                    }
                });

                document.getElementById('clearAll').addEventListener('click', () => {
                    this.clearAll();
                });

                document.getElementById('checkAllProximity').addEventListener('click', () => {
                    this.checkAllProximity();
                });

                document.getElementById('radius').addEventListener('change', () => {
                    this.updateRadiusCircle();
                    // Re-check all deliveries with new radius
                    this.deliveries.forEach(delivery => {
                        this.checkProximity(delivery);
                    });
                });
            }
        }

        // Initialize dashboard when page loads
        let dashboard;
        document.addEventListener('DOMContentLoaded', function() {
            dashboard = new ProximityDashboard();
        });
    </script>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
</body>
</html>
