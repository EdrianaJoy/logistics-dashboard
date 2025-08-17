<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proximity Alert System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: url('/dashboard.png') center/cover no-repeat;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .top-header {
            background: #fda4af;
            padding: 20px 40px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .top-header-text {
            color: white;
            font-size: 2rem;
            font-weight: 600;
            text-align: left;
            margin: 0;
        }
        
        .main-content {
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }
        
        .card-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin: 0 auto 25px;
            position: relative;
        }
        
        .interactive-map .card-icon {
            background: white;
            background-image: url('/interactive.png');
            background-size: 50px 50px;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .heat-map .card-icon {
            background: white;
            background-image: url('/heat.png');
            background-size: 50px 50px;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .proximity-map .card-icon {
            background: white;
            background-image: url('/proximity.png');
            background-size: 50px 50px;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .card-title {
            font-size: 1.6rem;
            font-weight: 600;
            color: #e91e63;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Top Header Bar -->
    <div class="top-header">
        <h1 class="top-header-text">Advance Warehouse Delivery System</h1>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="header">
            </div>
        
        <!-- Dashboard Cards Section -->
        <div class="dashboard-grid">
            <div class="dashboard-card heat-map" onclick="window.location.href='/dashboard/heatmap'">
                <div class="card-icon"></div>
                <div class="card-title">Heat Map</div>
            </div>
            
            <div class="dashboard-card proximity-map" onclick="window.location.href='/dashboard/map'">
                <div class="card-icon"></div>
                <div class="card-title">Proximity Map</div>
            </div>
        </div>
        </div>
    </div>
</body>
</html>
