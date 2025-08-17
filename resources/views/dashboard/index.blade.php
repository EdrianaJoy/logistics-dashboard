<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proximity Alert System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .header { 
            background: rgba(255, 255, 255, 0.1); 
            backdrop-filter: blur(10px);
            color: white; 
            padding: 1rem 0; 
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        .nav { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        
        .nav h1 { 
            font-size: 1.5rem; 
            font-weight: 700;
        }
        
        .nav-links { display: flex; gap: 20px; }
        .nav-links a { 
            color: white; 
            text-decoration: none; 
            padding: 8px 16px; 
            border-radius: 10px; 
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .nav-links a:hover { 
            background: rgba(255, 255, 255, 0.2); 
            transform: translateY(-2px);
        }
        
        .hero { 
            color: white; 
            padding: 60px 0; 
            text-align: center; 
        }
        
        .hero h2 { 
            font-size: 2.5rem; 
            margin-bottom: 1rem; 
            font-weight: 700;
        }
        
        .hero p { 
            font-size: 1.2rem; 
            margin-bottom: 2rem; 
            opacity: 0.9;
        }
        
        .features { 
            padding: 60px 20px; 
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }
        
        .features-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); 
            gap: 30px; 
            margin-top: 40px; 
        }
        
        .feature-card { 
            background: white; 
            padding: 40px 30px; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            text-align: center; 
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card:hover { 
            transform: translateY(-10px); 
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .feature-icon { 
            font-size: 3.5rem; 
            margin-bottom: 1.5rem; 
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .feature-card h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 15px;
        }
        
        .feature-card p {
            color: #636e72;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        
        .btn { 
            display: inline-block; 
            padding: 12px 24px; 
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            color: white; 
            text-decoration: none; 
            border-radius: 10px; 
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .btn:hover { 
            background: linear-gradient(135deg, #0984e3, #74b9ff);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-success { 
            background: linear-gradient(135deg, #00b894, #00cec9);
        }
        .btn-success:hover { 
            background: linear-gradient(135deg, #00cec9, #00b894);
        }
        
        .btn-warning { 
            background: linear-gradient(135deg, #fdcb6e, #e17055);
        }
        .btn-warning:hover { 
            background: linear-gradient(135deg, #e17055, #fdcb6e);
        }
        
        .btn-danger { 
            background: linear-gradient(135deg, #e17055, #d63031);
        }
        .btn-danger:hover { 
            background: linear-gradient(135deg, #d63031, #e17055);
        }
        
        .stats { 
            background: rgba(255, 255, 255, 0.1); 
            backdrop-filter: blur(10px);
            padding: 40px 20px; 
        }
        
        .stats h2 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
        }
        
        .stat-card { 
            background: white; 
            padding: 25px 20px; 
            border-radius: 15px; 
            text-align: center; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.1); 
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number { 
            font-size: 2.5rem; 
            font-weight: 700; 
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 5px;
        }
        
        .stat-card div:last-child {
            color: #636e72;
            font-weight: 500;
        }
        
        .footer { 
            background: rgba(0, 0, 0, 0.2); 
            color: white; 
            padding: 40px 20px; 
            text-align: center; 
        }
        
        .footer p {
            opacity: 0.8;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <h1>🚚 Proximity Alert System</h1>
                <div class="nav-links">
                    <a href="#features">Features</a>
                    <a href="#stats">Statistics</a>
                    <a href="/dashboard/map">Dashboard</a>
                </div>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <h2>Advanced Warehouse Delivery Proximity System</h2>
            <p>Real-time tracking, intelligent alerts, and comprehensive analytics for your delivery operations</p>
            <a href="/dashboard/map" class="btn">Get Started</a>
        </div>
    </section>

    <section class="features" id="features">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 20px; color: white; font-weight: 600;">Enhanced Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">�</div>
                    <h3>Interactive Maps</h3>
                    <p>Visualize warehouse and delivery locations with real-time proximity zones using Leaflet.js</p>
                    <a href="/dashboard/map" class="btn btn-success">View Map Dashboard</a>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🔥</div>
                    <h3>Distance Heatmaps</h3>
                    <p>Analyze delivery patterns with advanced heatmap visualization using Turf.js</p>
                    <a href="/dashboard/heatmap" class="btn btn-warning">View Heatmap</a>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🔔</div>
                    <h3>Smart Notifications</h3>
                    <p>Automated alerts via email and database with Laravel Notifications</p>
                    <a href="/api/notifications/history" class="btn">View Notifications</a>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Alert Logging</h3>
                    <p>Comprehensive logging and analytics with Laravel DB + Eloquent</p>
                    <a href="/api/stats/alerts" class="btn">View Analytics</a>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Real-time Tracking</h3>
                    <p>Live delivery tracking with Pusher/Laravel Echo broadcasting</p>
                    <a href="/dashboard/realtime" class="btn btn-danger">Live Dashboard</a>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3>Proximity Detection</h3>
                    <p>Accurate distance calculation using Flask + GeoPy backend service</p>
                    <a href="/proximity-form" class="btn">Test Proximity</a>
                </div>
            </div>
        </div>
    </section>

    <section class="stats" id="stats">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 30px;">System Statistics</h2>
            <div class="stats-grid" id="statsGrid">
                <div class="stat-card">
                    <div class="stat-number" id="totalAlerts">--</div>
                    <div>Total Alerts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="inRangeAlerts">--</div>
                    <div>In Range</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="outOfRangeAlerts">--</div>
                    <div>Out of Range</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="avgDistance">--</div>
                    <div>Avg Distance (m)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="successRate">--</div>
                    <div>Success Rate</div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Warehouse Delivery Proximity System. Built with Laravel + Flask.</p>
            <p>Features: Interactive Maps | Heatmaps | Notifications | Real-time Tracking | Analytics</p>
        </div>
    </footer>

    <script>
        // Load statistics on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
        });

        async function loadStatistics() {
            try {
                const response = await fetch('/api/stats/alerts?days=7');
                const data = await response.json();
                
                if (data.success) {
                    const stats = data.stats;
                    
                    document.getElementById('totalAlerts').textContent = stats.total_alerts || 0;
                    document.getElementById('inRangeAlerts').textContent = stats.within_range || 0;
                    document.getElementById('outOfRangeAlerts').textContent = stats.out_of_range || 0;
                    document.getElementById('avgDistance').textContent = stats.average_distance ? Math.round(stats.average_distance) : 0;
                    
                    const successRate = stats.total_alerts > 0 ? 
                        Math.round((stats.successful_sends / stats.total_alerts) * 100) : 0;
                    document.getElementById('successRate').textContent = successRate + '%';
                }
            } catch (error) {
                console.error('Failed to load statistics:', error);
                // Set default values
                document.querySelectorAll('.stat-number').forEach(el => {
                    if (el.textContent === '--') el.textContent = '0';
                });
            }
        }
    </script>
</body>
</html>
