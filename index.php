<?php
include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Management Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
        }

        .dashboard-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 24px;
        }

        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
            border-radius: 24px;
            padding: 32px;
            margin-bottom: 32px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(50px, -50px);
        }

        .welcome-content {
            position: relative;
            z-index: 2;
        }

        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .welcome-subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 24px;
        }

        .welcome-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
        }

        .welcome-stat {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
        }

        .welcome-stat-value {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .welcome-stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Main Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        /* Asset Cards */
        .asset-category-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .asset-category-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .asset-category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-color);
        }

        .asset-category-card.laptops {
            --accent-color: linear-gradient(90deg, #3b82f6, #1d4ed8);
        }

        .asset-category-card.vehicles {
            --accent-color: linear-gradient(90deg, #10b981, #059669);
        }

        .asset-category-card.equipment {
            --accent-color: linear-gradient(90deg, #f59e0b, #d97706);
        }

        .category-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .category-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            background: var(--accent-color);
        }

        .category-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
        }

        .category-count {
            font-size: 32px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .category-subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 16px;
        }

        .category-breakdown {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .breakdown-item {
            background: #f8fafc;
            border-radius: 12px;
            padding: 12px;
            text-align: center;
        }

        .breakdown-value {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
        }

        .breakdown-label {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Content Sections */
        .content-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        .content-card {
            background: white;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: between;
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
        }

        .card-action {
            background: #eff6ff;
            color: #2563eb;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .card-action:hover {
            background: #dbeafe;
        }

        /* Asset List */
        .asset-list {
            display: grid;
            gap: 16px;
        }

        .asset-item {
            display: grid;
            grid-template-columns: auto 1fr auto auto;
            gap: 16px;
            align-items: center;
            padding: 16px;
            background: #f8fafc;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .asset-item:hover {
            background: #f1f5f9;
            transform: translateX(4px);
        }

        .asset-avatar {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            font-weight: 600;
        }

        .asset-avatar.laptop { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .asset-avatar.vehicle { background: linear-gradient(135deg, #10b981, #059669); }
        .asset-avatar.equipment { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .asset-avatar.furniture { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

        .asset-info h4 {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .asset-info p {
            color: #64748b;
            font-size: 14px;
        }

        .asset-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .asset-status.available {
            background: #dcfce7;
            color: #166534;
        }

        .asset-status.assigned {
            background: #fef3c7;
            color: #92400e;
        }

        .asset-status.maintenance {
            background: #fef2f2;
            color: #dc2626;
        }

        .asset-menu {
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .asset-menu:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        /* Quick Actions */
        .quick-actions-grid {
            display: grid;
            gap: 16px;
        }

        .action-button {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-decoration: none;
            color: #1e293b;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .action-button:hover {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1d4ed8;
            transform: translateY(-2px);
        }

        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }

        /* Recent Activity */
        .activity-list {
            display: grid;
            gap: 12px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 10px;
            transition: background 0.3s ease;
        }

        .activity-item:hover {
            background: #f8fafc;
        }

        .activity-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .activity-dot.checkout { background: #f59e0b; }
        .activity-dot.checkin { background: #10b981; }
        .activity-dot.maintenance { background: #ef4444; }
        .activity-dot.added { background: #3b82f6; }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            font-size: 14px;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .activity-time {
            font-size: 12px;
            color: #64748b;
        }

        /* Search and Filters */
        .search-section {
            background: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .search-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 16px;
            align-items: center;
        }

        .search-input {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            background: #f8fafc;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
        }

        .filter-select {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            cursor: pointer;
            font-size: 14px;
        }

        .filter-select:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
        }

        .search-button {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .content-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 16px;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .welcome-stats {
                grid-template-columns: 1fr 1fr;
            }
            
            .search-grid {
                grid-template-columns: 1fr;
            }
            
            .asset-item {
                grid-template-columns: auto 1fr;
                gap: 12px;
            }
            
            .asset-status,
            .asset-menu {
                grid-column: 1 / -1;
                justify-self: start;
                margin-top: 8px;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .asset-category-card,
        .content-card,
        .search-section {
            animation: fadeInUp 0.6s ease forwards;
        }

        .asset-category-card:nth-child(2) { animation-delay: 0.1s; }
        .asset-category-card:nth-child(3) { animation-delay: 0.2s; }

        /* Loading States */
        .loading {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body>
    <?php
    // Asset management data
    $total_assets = 1247;
    $available_assets = 892;
    $assigned_assets = 298;
    $maintenance_assets = 57;
    
    $asset_categories = [
        'laptops' => ['total' => 425, 'available' => 312, 'assigned' => 98, 'maintenance' => 15],
        'vehicles' => ['total' => 48, 'available' => 32, 'assigned' => 14, 'maintenance' => 2],
        'equipment' => ['total' => 774, 'available' => 548, 'assigned' => 186, 'maintenance' => 40]
    ];
    
    $recent_assets = [
        [
            'id' => 'LT001',
            'name' => 'MacBook Pro 16" M3',
            'type' => 'laptop',
            'serial' => 'MBP2024001',
            'status' => 'available',
            'assignee' => '',
            'location' => 'IT Storage'
        ],
        [
            'id' => 'VH001',
            'name' => 'Tesla Model Y',
            'type' => 'vehicle',
            'serial' => 'TMY2024001',
            'status' => 'assigned',
            'assignee' => 'John Smith',
            'location' => 'Parking Lot A'
        ],
        [
            'id' => 'EQ001',
            'name' => 'Dell UltraSharp 32"',
            'type' => 'equipment',
            'serial' => 'DU32001',
            'status' => 'assigned',
            'assignee' => 'Sarah Johnson',
            'location' => 'Office Floor 3'
        ],
        [
            'id' => 'LT002',
            'name' => 'ThinkPad X1 Carbon',
            'type' => 'laptop',
            'serial' => 'TPX1002',
            'status' => 'maintenance',
            'assignee' => '',
            'location' => 'Repair Center'
        ],
        [
            'id' => 'FU001',
            'name' => 'Herman Miller Aeron',
            'type' => 'furniture',
            'serial' => 'HMA001',
            'status' => 'available',
            'assignee' => '',
            'location' => 'Storage Room B'
        ]
    ];
    
    $recent_activities = [
        ['type' => 'checkout', 'text' => 'MacBook Pro assigned to Alex Chen', 'time' => '2 minutes ago'],
        ['type' => 'checkin', 'text' => 'iPad Pro returned by Maria Garcia', 'time' => '15 minutes ago'],
        ['type' => 'maintenance', 'text' => 'ThinkPad X1 sent for repair', 'time' => '1 hour ago'],
        ['type' => 'added', 'text' => 'New Dell Monitor added to inventory', 'time' => '2 hours ago'],
        ['type' => 'checkout', 'text' => 'Tesla Model 3 assigned to David Kim', 'time' => '3 hours ago']
    ];
    ?>

    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-content">
                <h1 class="welcome-title">Asset Management Dashboard</h1>
                <p class="welcome-subtitle">Monitor and manage your organization's assets in real-time</p>
                
                <div class="welcome-stats">
                    <div class="welcome-stat">
                        <div class="welcome-stat-value"><?php echo number_format($total_assets); ?></div>
                        <div class="welcome-stat-label">Total Assets</div>
                    </div>
                    <div class="welcome-stat">
                        <div class="welcome-stat-value"><?php echo number_format($available_assets); ?></div>
                        <div class="welcome-stat-label">Available</div>
                    </div>
                    <div class="welcome-stat">
                        <div class="welcome-stat-value"><?php echo number_format($assigned_assets); ?></div>
                        <div class="welcome-stat-label">Assigned</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asset Categories -->
        <div class="dashboard-grid">
            <div class="asset-category-card laptops">
                <div class="category-header">
                    <div class="category-icon">💻</div>
                </div>
                <div class="category-title">Laptops & Computers</div>
                <div class="category-count"><?php echo $asset_categories['laptops']['total']; ?></div>
                <div class="category-subtitle">Desktop and mobile computing devices</div>
                <div class="category-breakdown">
                    <div class="breakdown-item">
                        <div class="breakdown-value"><?php echo $asset_categories['laptops']['available']; ?></div>
                        <div class="breakdown-label">Available</div>
                    </div>
                    <div class="breakdown-item">
                        <div class="breakdown-value"><?php echo $asset_categories['laptops']['assigned']; ?></div>
                        <div class="breakdown-label">Assigned</div>
                    </div>
                </div>
            </div>

            <div class="asset-category-card vehicles">
                <div class="category-header">
                    <div class="category-icon">🚗</div>
                </div>
                <div class="category-title">Vehicles & Fleet</div>
                <div class="category-count"><?php echo $asset_categories['vehicles']['total']; ?></div>
                <div class="category-subtitle">Company cars and transportation</div>
                <div class="category-breakdown">
                    <div class="breakdown-item">
                        <div class="breakdown-value"><?php echo $asset_categories['vehicles']['available']; ?></div>
                        <div class="breakdown-label">Available</div>
                    </div>
                    <div class="breakdown-item">
                        <div class="breakdown-value"><?php echo $asset_categories['vehicles']['assigned']; ?></div>
                        <div class="breakdown-label">In Use</div>
                    </div>
                </div>
            </div>

            <div class="asset-category-card equipment">
                <div class="category-header">
                    <div class="category-icon">🖥️</div>
                </div>
                <div class="category-title">Equipment & Tools</div>
                <div class="category-count"><?php echo $asset_categories['equipment']['total']; ?></div>
                <div class="category-subtitle">Monitors, printers, and office equipment</div>
                <div class="category-breakdown">
                    <div class="breakdown-item">
                        <div class="breakdown-value"><?php echo $asset_categories['equipment']['available']; ?></div>
                        <div class="breakdown-label">Available</div>
                    </div>
                    <div class="breakdown-item">
                        <div class="breakdown-value"><?php echo $asset_categories['equipment']['assigned']; ?></div>
                        <div class="breakdown-label">Deployed</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="search-section">
            <div class="search-grid">
                <input type="text" class="search-input" placeholder="Search assets by name, ID, or serial number..." id="assetSearch">
                <select class="filter-select" id="categoryFilter">
                    <option value="">All Categories</option>
                    <option value="laptop">Laptops</option>
                    <option value="vehicle">Vehicles</option>
                    <option value="equipment">Equipment</option>
                    <option value="furniture">Furniture</option>
                </select>
                <select class="filter-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="available">Available</option>
                    <option value="assigned">Assigned</option>
                    <option value="maintenance">Maintenance</option>
                </select>
                <button class="search-button" onclick="performSearch()">🔍 Search</button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-row">
            <!-- Recent Assets -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Recent Assets</h2>
                    <button class="card-action" onclick="viewAllAssets()">View All</button>
                </div>
                
                <div class="asset-list" id="assetList">
                    <?php foreach($recent_assets as $asset): ?>
                    <div class="asset-item" data-type="<?php echo $asset['type']; ?>" data-status="<?php echo $asset['status']; ?>">
                        <div class="asset-avatar <?php echo $asset['type']; ?>">
                            <?php 
                            $icons = ['laptop' => '💻', 'vehicle' => '🚗', 'equipment' => 'Stuff'];
                            echo $icons[$asset['type']] ?? '📦';
                            ?>
                        </div>
                        <div class="asset-info">
                            <h4><?php echo $asset['name']; ?></h4>
                            <p><?php echo $asset['id']; ?> • <?php echo $asset['serial']; ?></p>
                            <?php if($asset['assignee']): ?>
                            <p>Assigned to: <?php echo $asset['assignee']; ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="asset-status <?php echo $asset['status']; ?>">
                            <?php echo ucfirst($asset['status']); ?>
                        </div>
                        <button class="asset-menu" onclick="showAssetMenu('<?php echo $asset['id']; ?>')">⋮</button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div style="display: grid; gap: 24px;">
                <!-- Quick Actions -->
                <div class="content-card">
                    <h3 class="card-title">Quick Actions</h3>
                    <div class="quick-actions-grid">
                        <div class="action-button" onclick="addNewAsset()">
                            <div class="action-icon">➕</div>
                            <div>
                                <div style="font-weight: 600;">Add Asset</div>
                                <div style="font-size: 12px; color: #64748b;">Register new equipment</div>
                            </div>
                        </div>
                        <div class="action-button" onclick="checkoutAsset()">
                            <div class="action-icon">📤</div>
                            <div>
                                <div style="font-weight: 600;">Check Out</div>
                                <div style="font-size: 12px; color: #64748b;">Assign to employee</div>
                            </div>
                        </div>
                        <div class="action-button" onclick="checkinAsset()">
                            <div class="action-icon">📥</div>
                            <div>
                                <div style="font-weight: 600;">Check In</div>
                                <div style="font-size: 12px; color: #64748b;">Return to inventory</div>
                            </div>
                        </div>
                        <div class="action-button" onclick="generateReport()">
                            <div class="action-icon">📊</div>
                            <div>
                                <div style="font-weight: 600;">Reports</div>
                                <div style="font-size: 12px; color: #64748b;">Generate analytics</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="content-card">
                    <h3 class="card-title">Recent Activity</h3>
                    <div class="activity-list">
                        <?php foreach($recent_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-dot <?php echo $activity['type']; ?>"></div>
                            <div class="activity-content">
                                <div class="activity-text"><?php echo $activity['text']; ?></div>
                                <div class="activity-time"><?php echo $activity['time']; ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // JavaScript functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            const searchInput = document.getElementById('assetSearch');
            const categoryFilter = document.getElementById('categoryFilter');
            const statusFilter = document.getElementById('statusFilter');

            function filterAssets() {
                const searchTerm = searchInput.value.toLowerCase();
                const categoryValue = categoryFilter.value;
                const statusValue = statusFilter.value;
                const assetItems = document.querySelectorAll('.asset-item');

                assetItems.forEach(item => {
                    const assetName = item.querySelector('.asset-info h4').textContent.toLowerCase();
                    const assetId = item.querySelector('.asset-info p').textContent.toLowerCase();
                    const assetType = item.dataset.type;
                    const assetStatus = item.dataset.status;

                    const matchesSearch = assetName.includes(searchTerm) || assetId.includes(searchTerm);
                    const matchesCategory = !categoryValue || assetType === categoryValue;
                    const matchesStatus = !statusValue || assetStatus === statusValue;

                    if (matchesSearch && matchesCategory && matchesStatus) {
                        item.style.display = 'grid';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }

            searchInput.addEventListener('input', filterAssets);
            categoryFilter.addEventListener('change', filterAssets);
            statusFilter.addEventListener('change', filterAssets);

            // Animate counters
            animateCounters();
        });

        function performSearch() {
            const searchTerm = document.getElementById('assetSearch').value;
            if (searchTerm.trim()) {
                console.log('Searching for:', searchTerm);
                // In a real app, this would trigger a server search
                alert('Searching for: ' + searchTerm);
            }
        }

        function addNewAsset() {
            alert('Add New Asset\n\nThis would open a form to register a new asset with fields for:\n- Asset name and category\n- Serial number\n- Purchase information\n- Location details');
        }

        function checkoutAsset() {
            alert('Check Out Asset\n\nThis would open a form to:\n- Select available asset\n- Choose employee\n- Set checkout date\n- Add notes');
        }

        function checkinAsset() {
            alert('Check In Asset\n\nThis would open a form to:\n- Select assigned asset\n- Record condition\n- Update location\n- Add return notes');
        }

        function generateReport() {
            alert('Generate Reports\n\nAvailable reports:\n- Asset utilization\n- Maintenance schedules\n- Cost analysis\n- Assignment history');
        }

        function viewAllAssets() {
            alert('View All Assets\n\nThis would navigate to a comprehensive asset list with:\n- Advanced filtering\n- Bulk operations\n- Export capabilities\n- Detailed asset information');
        }

        function showAssetMenu(assetId) {
            alert('Asset Menu for: ' + assetId + '\n\nOptions:\n- View Details\n- Edit Asset\n- Check Out/In\n- Schedule Maintenance\n- View History');
        }

        function animateCounters() {
            const counters = document.querySelectorAll('.welcome-stat-value, .category-count');
            
            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace(/,/g, ''));
                let current = 0;
                const increment = target / 60;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    counter.textContent = Math.floor(current).toLocaleString();
                }, 25);
            });
        }

        // Real-time updates simulation
        setInterval(() => {
            const activityTimes = document.querySelectorAll('.activity-time');
            activityTimes.forEach((time, index) => {
                const minutes = (index + 1) * 2;
                if (minutes < 60) {
                    time.textContent = minutes + ' minutes ago';
                } else {
                    const hours = Math.floor(minutes / 60);
                    time.textContent = hours + ' hour' + (hours > 1 ? 's' : '') + ' ago';
                }
            });
        }, 60000); // Update every minute
    </script>
</body>
</html>