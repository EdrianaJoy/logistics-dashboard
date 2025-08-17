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
        
        .main-title {
            font-size: 3rem;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
            text-align: center;
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
            background: linear-gradient(135deg, #e91e63, #ad1457);
        }
        
        .interactive-map .card-icon::before {
            content: "";
            background: url('/interactive.png') center/contain no-repeat;
            width: 100%;
            height: 100%;
            display: block;
        }
        
        .heat-map .card-icon {
            background: linear-gradient(135deg, #ff5722, #d84315);
        }
        
        .heat-map .card-icon::before {
            content: "";
            background: url('/heat.png') center/contain no-repeat;
            width: 100%;
            height: 100%;
            display: block;
        }
        
        .proximity-map .card-icon {
            background: linear-gradient(135deg, #9c27b0, #6a1b9a);
        }
        
        .proximity-map .card-icon::before {
            content: "";
            background: url('/proximity.png') center/contain no-repeat;
            width: 100%;
            height: 100%;
            display: block;
        }
        
        .card-title {
            font-size: 1.6rem;
            font-weight: 600;
            color: #d63384;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="main-title">Advanced Warehouse Delivery System</h1>
        </div>
        
        <!-- Dashboard Cards Section -->
        <div class="dashboard-grid">
            <div class="dashboard-card interactive-map" onclick="window.location.href='/dashboard/map'">
                <div class="card-icon"></div>
                <div class="card-title">Interactive Map</div>
            </div>
            
            <div class="dashboard-card heat-map" onclick="window.location.href='/dashboard/heatmap'">
                <div class="card-icon"></div>
                <div class="card-title">Heat Map</div>
            </div>
            
            <div class="dashboard-card proximity-map">
                <div class="card-icon"></div>
                <div class="card-title">Proximity Map</div>
            </div>
        </div>
    </div>
</body>
</html>
