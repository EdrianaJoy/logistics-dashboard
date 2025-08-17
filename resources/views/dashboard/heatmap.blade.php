<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distance Heatmap Dashboard</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            background: linear-gradient(135deg, #fce4ec 0%, #f8bbd9 50%, #f48fb1 100%);
            min-height: 100vh;
            font-family: Arial, sans-serif;
        }
        .container {
            padding: 20px;
        }
        h1 {
            color: #e91e63;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        #heatmap { height: 600px; width: 100%; }
        .heatmap-controls { 
            margin: 20px 0; 
            padding: 15px; 
            border: 1px solid #e1bee7; 
            border-radius: 5px; 
            background: rgba(255, 255, 255, 0.9);
        }
        .legend { 
            position: absolute; 
            bottom: 20px; 
            right: 20px; 
            background: white; 
            padding: 10px; 
            border-radius: 5px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        .legend-item { 
            display: flex; 
            align-items: center; 
            margin: 5px 0; 
        }
        .legend-color { 
            width: 20px; 
            height: 20px; 
            margin-right: 10px; 
            border: 1px solid #ccc; 
        }
        .zone-info { margin: 20px 0; }
        .btn { padding: 8px 16px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background-color: #e91e63; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Distance Heatmap Analysis</h1>
        
        <div class="heatmap-controls">
            <label for="maxDistance">Maximum Distance (km):</label>
            <select id="maxDistance">
                <option value="1">1 km</option>
                <option value="2" selected>2 km</option>
                <option value="5">5 km</option>
                <option value="10">10 km</option>
            </select>
            
            <label for="resolution">Grid Resolution:</label>
            <select id="resolution">
                <option value="0.01">High (0.01°)</option>
                <option value="0.02" selected>Medium (0.02°)</option>
                <option value="0.05">Low (0.05°)</option>
            </select>
            
            <button id="generateHeatmap" class="btn btn-primary">Generate Heatmap</button>
            <button id="addDeliveryPoints" class="btn btn-primary">Add Sample Deliveries</button>
        </div>

        <div id="heatmap"></div>
        
        <div class="legend">
            <h4>Distance Zones</h4>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #00ff00;"></div>
                <span>0-250m (Immediate)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #ffff00;"></div>
                <span>250-500m (Close)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #ffa500;"></div>
                <span>500m-1km (Medium)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #ff0000;"></div>
                <span>1km+ (Far)</span>
            </div>
        </div>

        <div class="zone-info">
            <h3>Zone Statistics</h3>
            <div id="zoneStats"></div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <script>
        class DistanceHeatmap {
            constructor() {
                this.map = null;
                this.warehouseCoords = [14.5995, 120.9842]; // Manila coordinates
                this.heatmapLayer = null;
                this.deliveryMarkers = [];
                this.init();
            }

            init() {
                this.initMap();
                this.addWarehouse();
                this.bindEvents();
            }

            initMap() {
                this.map = L.map('heatmap').setView(this.warehouseCoords, 12);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(this.map);
            }

            addWarehouse() {
                L.marker(this.warehouseCoords, {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).addTo(this.map).bindPopup('<b>Warehouse</b><br>Distribution Center');
            }

            generateHeatmap() {
                // Clear existing heatmap
                if (this.heatmapLayer) {
                    this.map.removeLayer(this.heatmapLayer);
                }

                const maxDistance = parseFloat(document.getElementById('maxDistance').value);
                const resolution = parseFloat(document.getElementById('resolution').value);
                
                // Create grid around warehouse
                const bounds = this.map.getBounds();
                const grid = this.createGrid(bounds, resolution);
                
                // Calculate distances and create heatmap
                const features = [];
                const zoneStats = { immediate: 0, close: 0, medium: 0, far: 0 };

                grid.forEach(point => {
                    const distance = this.calculateDistance(
                        this.warehouseCoords[0], this.warehouseCoords[1],
                        point.lat, point.lng
                    );

                    if (distance <= maxDistance * 1000) { // Convert km to meters
                        const color = this.getColorByDistance(distance);
                        const zone = this.getZoneByDistance(distance);
                        zoneStats[zone]++;

                        // Create circle for each grid point
                        const circle = L.circle([point.lat, point.lng], {
                            color: color,
                            fillColor: color,
                            fillOpacity: 0.6,
                            radius: 50, // Small radius for grid points
                            stroke: false
                        });

                        features.push(circle);
                    }
                });

                // Add all features to map
                this.heatmapLayer = L.layerGroup(features).addTo(this.map);
                
                // Update zone statistics
                this.updateZoneStats(zoneStats);
            }

            createGrid(bounds, resolution) {
                const grid = [];
                const north = bounds.getNorth();
                const south = bounds.getSouth();
                const east = bounds.getEast();
                const west = bounds.getWest();

                for (let lat = south; lat <= north; lat += resolution) {
                    for (let lng = west; lng <= east; lng += resolution) {
                        grid.push({ lat, lng });
                    }
                }

                return grid;
            }

            calculateDistance(lat1, lng1, lat2, lng2) {
                // Using Turf.js for more accurate distance calculation
                const from = turf.point([lng1, lat1]);
                const to = turf.point([lng2, lat2]);
                return turf.distance(from, to, { units: 'meters' });
            }

            getColorByDistance(distance) {
                if (distance <= 250) return '#00ff00';      // Green - Immediate
                if (distance <= 500) return '#ffff00';      // Yellow - Close
                if (distance <= 1000) return '#ffa500';     // Orange - Medium
                return '#ff0000';                            // Red - Far
            }

            getZoneByDistance(distance) {
                if (distance <= 250) return 'immediate';
                if (distance <= 500) return 'close';
                if (distance <= 1000) return 'medium';
                return 'far';
            }

            updateZoneStats(stats) {
                const total = Object.values(stats).reduce((sum, count) => sum + count, 0);
                
                document.getElementById('zoneStats').innerHTML = `
                    <div>Total Grid Points: ${total}</div>
                    <div>Immediate Zone (0-250m): ${stats.immediate} (${((stats.immediate/total)*100).toFixed(1)}%)</div>
                    <div>Close Zone (250-500m): ${stats.close} (${((stats.close/total)*100).toFixed(1)}%)</div>
                    <div>Medium Zone (500m-1km): ${stats.medium} (${((stats.medium/total)*100).toFixed(1)}%)</div>
                    <div>Far Zone (1km+): ${stats.far} (${((stats.far/total)*100).toFixed(1)}%)</div>
                `;
            }

            addSampleDeliveries() {
                // Clear existing delivery markers
                this.deliveryMarkers.forEach(marker => this.map.removeLayer(marker));
                this.deliveryMarkers = [];

                // Sample delivery locations around Manila
                const sampleDeliveries = [
                    { lat: 14.6042, lng: 120.9822, id: 1 }, // Quezon City
                    { lat: 14.5547, lng: 121.0244, id: 2 }, // Pasig
                    { lat: 14.5764, lng: 120.9772, id: 3 }, // Manila
                    { lat: 14.5243, lng: 121.0792, id: 4 }, // Antipolo
                    { lat: 14.6507, lng: 121.0494, id: 5 }, // Marikina
                ];

                sampleDeliveries.forEach(delivery => {
                    const distance = this.calculateDistance(
                        this.warehouseCoords[0], this.warehouseCoords[1],
                        delivery.lat, delivery.lng
                    );

                    const iconColor = distance <= 250 ? 'green' : 
                                     distance <= 500 ? 'yellow' : 
                                     distance <= 1000 ? 'orange' : 'red';

                    const marker = L.marker([delivery.lat, delivery.lng], {
                        icon: L.icon({
                            iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-${iconColor === 'yellow' ? 'gold' : iconColor}.png`,
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        })
                    }).addTo(this.map);

                    marker.bindPopup(`
                        <b>Delivery ${delivery.id}</b><br>
                        Distance: ${Math.round(distance)}m<br>
                        Zone: ${this.getZoneByDistance(distance)}
                    `);

                    this.deliveryMarkers.push(marker);
                });
            }

            bindEvents() {
                document.getElementById('generateHeatmap').addEventListener('click', () => {
                    this.generateHeatmap();
                });

                document.getElementById('addDeliveryPoints').addEventListener('click', () => {
                    this.addSampleDeliveries();
                });
            }
        }

        // Initialize heatmap when page loads
        document.addEventListener('DOMContentLoaded', function() {
            new DistanceHeatmap();
        });
    </script>
</body>
</html>
