<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

// Check if user is owner or admin
if (!$auth->hasRole('owner') && !$auth->hasRole('admin')) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Use logo-alt.png which is verified to work
$logoFile = 'logo-alt.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-app-capable" content="yes">
    <meta name="theme-color" content="#1e1b4b">
    <title>Owner Dashboard - Narayana Monitoring</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Mobile-First Responsive Design */
        * {
            -webkit-tap-highlight-color: transparent;
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: var(--bg-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            overflow-x: hidden;
        }
        
        .mobile-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: linear-gradient(135deg, #1e1b4b, #4338ca);
            padding: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }
        
        .header-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
        }
        
        .header-subtitle {
            font-size: 0.75rem;
            opacity: 0.9;
            margin-top: 0.25rem;
        }
        
        .refresh-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            padding: 0.5rem;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .refresh-btn:active {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0.95);
        }
        
        .branch-selector {
            background: white;
            margin: -0.5rem 1rem 1rem;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .branch-select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            background: white;
            cursor: pointer;
        }
        
        .content-wrapper {
            padding: 0 1rem 5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1;
        }
        
        .stat-change {
            font-size: 0.75rem;
            margin-top: 0.5rem;
        }
        
        .section-card {
            background: white;
            padding: 1.25rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 1rem;
        }
        
        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .occupancy-bar {
            height: 40px;
            background: #f3f4f6;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            margin-bottom: 0.5rem;
        }
        
        .occupancy-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.875rem;
            transition: width 0.5s ease;
        }
        
        .occupancy-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            color: #6b7280;
        }
        
        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .transaction-item:last-child {
            border-bottom: none;
        }
        
        .transaction-info {
            flex: 1;
        }
        
        .transaction-desc {
            font-size: 0.875rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.25rem;
        }
        
        .transaction-meta {
            font-size: 0.75rem;
            color: #6b7280;
        }
        
        .transaction-amount {
            font-size: 1rem;
            font-weight: 700;
            white-space: nowrap;
        }
        
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-around;
            padding: 0.75rem 0;
            box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.1);
        }
        
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            color: #6b7280;
            font-size: 0.75rem;
            text-decoration: none;
            padding: 0.25rem 1rem;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .nav-item.active {
            color: #4338ca;
            background: #eef2ff;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: #6b7280;
        }
        
        .empty-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            opacity: 0.5;
        }
        
        /* Chart Container */
        .chart-container {
            position: relative;
            height: 280px;
            margin-top: 1rem;
        }        
        .period-selector {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            background: #f3f4f6;
            padding: 0.25rem;
            border-radius: 8px;
        }
        
        .period-btn {
            flex: 1;
            padding: 0.5rem;
            border: none;
            background: transparent;
            border-radius: 6px;
            font-size: 0.813rem;
            font-weight: 600;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .period-btn.active {
            background: white;
            color: #4338ca;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }        
        /* Pull to Refresh */
        .pull-to-refresh {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4338ca;
            font-size: 0.875rem;
            transform: translateY(-100%);
            transition: transform 0.3s;
        }
        
        .pull-to-refresh.visible {
            transform: translateY(0);
        }
        
        /* Tablet and Desktop */
        @media (min-width: 768px) {
            .content-wrapper {
                max-width: 1200px;
                margin: 0 auto;
                padding: 1rem 2rem 2rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            
            .bottom-nav {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="pull-to-refresh" id="pullToRefresh">
        <div class="loading-spinner"></div>
        <span style="margin-left: 0.5rem;">Menarik untuk refresh...</span>
    </div>
    
    <div class="mobile-header">
        <div class="header-content">
            <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                <!-- Logo Hotel -->
                <img src="../../uploads/logos/logo-alt.png" 
                     alt="Narayana Hotel" 
                     id="headerLogo"
                     style="height: 45px; width: auto; object-fit: contain; background: white; padding: 0.5rem; border-radius: 8px;"
                     onerror="console.error('Logo error:', this.src); this.style.display='none'; document.getElementById('logoFallback').style.display='flex';">
                <div id="logoFallback" style="width: 45px; height: 45px; background: white; border-radius: 8px; display: none; align-items: center; justify-content: center; font-size: 1.5rem;">
                    🏨
                </div>
                <div>
                    <div class="header-title">Narayana Hotel</div>
                    <div class="header-subtitle" id="currentTime">Loading...</div>
                </div>
            </div>
            <button class="refresh-btn" onclick="refreshData()" id="refreshBtn">
                <i data-feather="refresh-cw" style="width: 20px; height: 20px;"></i>
            </button>
        </div>
    </div>
    
    <div class="branch-selector">
        <label style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.5rem; display: block;">
            <i data-feather="map-pin" style="width: 14px; height: 14px;"></i>
            Select Branch
        </label>
        <select class="branch-select" id="branchSelect" onchange="loadBranchData()">
            <option value="">Loading branches...</option>
        </select>
    </div>
    
    <div class="content-wrapper">
        <!-- Chart Section - MOVED TO TOP -->
        <div class="section-card">
            <div class="section-title">
                <i data-feather="bar-chart-2" style="width: 20px; height: 20px; color: #4338ca;"></i>
                <span id="chartTitle">Trend Chart</span>
            </div>
            <div class="period-selector">
                <button class="period-btn active" onclick="changePeriod('7days')" data-period="7days">
                    7 Days
                </button>
                <button class="period-btn" onclick="changePeriod('30days')" data-period="30days">
                    This Month
                </button>
                <button class="period-btn" onclick="changePeriod('12months')" data-period="12months">
                    This Year
                </button>
            </div>
            <div class="chart-container">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">
                    <i data-feather="trending-up" style="width: 14px; height: 14px;"></i>
                    Today's Income
                </div>
                <div class="stat-value" style="color: #10b981;" id="todayIncome">Rp 0</div>
                <div class="stat-change" style="color: #6b7280;" id="todayIncomeCount">0 transactions</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">
                    <i data-feather="trending-down" style="width: 14px; height: 14px;"></i>
                    Today's Expense
                </div>
                <div class="stat-value" style="color: #ef4444;" id="todayExpense">Rp 0</div>
                <div class="stat-change" style="color: #6b7280;" id="todayExpenseCount">0 transactions</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">
                    <i data-feather="dollar-sign" style="width: 14px; height: 14px;"></i>
                    Monthly Income
                </div>
                <div class="stat-value" style="color: #10b981;" id="monthIncome">Rp 0</div>
                <div class="stat-change" style="color: #6b7280;" id="monthIncomeChange">0%</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">
                    <i data-feather="shopping-cart" style="width: 14px; height: 14px;"></i>
                    Monthly Expense
                </div>
                <div class="stat-value" style="color: #ef4444;" id="monthExpense">Rp 0</div>
                <div class="stat-change" style="color: #6b7280;" id="monthExpenseChange">0%</div>
            </div>
        </div>
        
        <!-- Occupancy Section -->
        <div class="section-card">
            <div class="section-title">
                <i data-feather="home" style="width: 20px; height: 20px; color: #4338ca;"></i>
                Room Occupancy
            </div>
            <div class="occupancy-bar">
                <div class="occupancy-fill" id="occupancyFill" style="width: 0%;">0%</div>
            </div>
            <div class="occupancy-info">
                <span>Occupied: <strong id="occupiedRooms">0</strong></span>
                <span>Total Rooms: <strong id="totalRooms">0</strong></span>
            </div>
        </div>
        
        <!-- Health Report Link -->
        <a href="health-report.php" style="text-decoration: none;">
            <div class="section-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; cursor: pointer; transition: transform 0.3s;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 1.125rem; font-weight: 700; margin-bottom: 0.5rem;">
                            🏥 Company Health Report
                        </div>
                        <div style="font-size: 0.875rem; opacity: 0.9;">
                            AI Analysis & Business Recommendations
                        </div>
                    </div>
                    <i data-feather="chevron-right" style="width: 28px; height: 28px;"></i>
                </div>
            </div>
        </a>
        
        <!-- Recent Transactions -->
        <div class="section-card">
            <div class="section-title">
                <i data-feather="list" style="width: 20px; height: 20px; color: #4338ca;"></i>
                Recent Transactions
            </div>
            <div id="recentTransactions">
                <div class="empty-state">
                    <div class="loading-spinner" style="width: 40px; height: 40px; border-width: 4px; border-color: #e5e7eb; border-top-color: #4338ca;"></div>
                    <p style="margin-top: 1rem;">Loading transactions...</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="bottom-nav">
        <a href="#" class="nav-item active">
            <i data-feather="home" style="width: 20px; height: 20px;"></i>
            Dashboard
        </a>
        <a href="health-report.php" class="nav-item">
            <i data-feather="activity" style="width: 20px; height: 20px;"></i>
            Health
        </a>
        <a href="#" class="nav-item" onclick="showOccupancy(); return false;">
            <i data-feather="grid" style="width: 20px; height: 20px;"></i>
            Occupancy
        </a>
        <a href="../../logout.php" class="nav-item">
            <i data-feather="log-out" style="width: 20px; height: 20px;"></i>
            Logout
        </a>
    </div>
    
    <script>
        let currentBranchId = null;
        let weeklyChart = null;
        let currentPeriod = '7days';
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();
            updateCurrentTime();
            setInterval(updateCurrentTime, 1000);
            
            // Initialize chart first, then load data
            initChart();
            loadBranches(); // This will trigger loadBranchData which loads chart data
            
            // Pull to refresh
            let startY = 0;
            let isPulling = false;
            
            document.addEventListener('touchstart', function(e) {
                if (window.pageYOffset === 0) {
                    startY = e.touches[0].pageY;
                }
            });
            
            document.addEventListener('touchmove', function(e) {
                if (window.pageYOffset === 0) {
                    let currentY = e.touches[0].pageY;
                    let pullDistance = currentY - startY;
                    
                    if (pullDistance > 60 && !isPulling) {
                        document.getElementById('pullToRefresh').classList.add('visible');
                        isPulling = true;
                    }
                }
            });
            
            document.addEventListener('touchend', function() {
                if (isPulling) {
                    refreshData();
                    setTimeout(() => {
                        document.getElementById('pullToRefresh').classList.remove('visible');
                        isPulling = false;
                    }, 1000);
                }
            });
        });
        
        function updateCurrentTime() {
            const now = new Date();
            const options = { 
                weekday: 'short', 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            document.getElementById('currentTime').textContent = now.toLocaleDateString('id-ID', options);
        }
        
        async function loadBranches() {
            try {
                const response = await fetch('../../api/owner-branches.php');
                const data = await response.json();
                
                if (data.success) {
                    const select = document.getElementById('branchSelect');
                    select.innerHTML = '<option value="">All Branches</option>';
                    
                    data.branches.forEach(branch => {
                        const option = document.createElement('option');
                        option.value = branch.id;
                        option.textContent = `${branch.branch_name} - ${branch.city}`;
                        select.appendChild(option);
                    });
                    
                    // Load data for first branch or all branches
                    if (data.branches.length > 0) {
                        select.selectedIndex = 1;
                        currentBranchId = data.branches[0].id;
                    } else {
                        currentBranchId = null; // All branches
                    }
                    
                    // Always load data after branches are loaded
                    loadBranchData();
                } else {
                    console.error('Failed to load branches:', data);
                    // Even if no branches, try to load data
                    loadBranchData();
                }
            } catch (error) {
                console.error('Error loading branches:', error);
                // Try to load data anyway
                loadBranchData();
            }
        }
        
        async function loadBranchData() {
            const select = document.getElementById('branchSelect');
            currentBranchId = select.value;
            
            await Promise.all([
                loadStats(),
                loadOccupancy(),
                loadChartData(),
                loadRecentTransactions()
            ]);
        }
        
        async function loadStats() {
            try {
                const url = currentBranchId 
                    ? `../../api/owner-stats.php?branch_id=${currentBranchId}`
                    : '../../api/owner-stats.php';
                
                console.log('Loading stats from:', url);
                    
                const response = await fetch(url);
                const data = await response.json();
                
                console.log('Stats response:', data);
                
                if (data.success) {
                    document.getElementById('todayIncome').textContent = formatRupiah(data.today.income);
                    document.getElementById('todayExpense').textContent = formatRupiah(data.today.expense);
                    document.getElementById('todayIncomeCount').textContent = `${data.today.income_count} transactions`;
                    document.getElementById('todayExpenseCount').textContent = `${data.today.expense_count} transactions`;
                    
                    document.getElementById('monthIncome').textContent = formatRupiah(data.month.income);
                    document.getElementById('monthExpense').textContent = formatRupiah(data.month.expense);
                    
                    // Calculate change percentages
                    const incomeChange = data.month.income_change || 0;
                    const expenseChange = data.month.expense_change || 0;
                    
                    document.getElementById('monthIncomeChange').textContent = 
                        `${incomeChange >= 0 ? '+' : ''}${incomeChange.toFixed(1)}% vs last month`;
                    document.getElementById('monthExpenseChange').textContent = 
                        `${expenseChange >= 0 ? '+' : ''}${expenseChange.toFixed(1)}% vs last month`;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }
        
        async function loadOccupancy() {
            try {
                const url = currentBranchId 
                    ? `../../api/owner-occupancy.php?branch_id=${currentBranchId}`
                    : '../../api/owner-occupancy.php';
                    
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success) {
                    const percentage = data.occupancy_rate || 0;
                    document.getElementById('occupancyFill').style.width = percentage + '%';
                    document.getElementById('occupancyFill').textContent = percentage.toFixed(1) + '%';
                    document.getElementById('occupiedRooms').textContent = data.occupied_rooms || 0;
                    document.getElementById('totalRooms').textContent = data.total_rooms || 0;
                }
            } catch (error) {
                console.error('Error loading occupancy:', error);
            }
        }
        
        function changePeriod(period) {
            currentPeriod = period;
            
            // Update button states
            document.querySelectorAll('.period-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-period="${period}"]`).classList.add('active');
            
            // Update chart title
            const titles = {
                '7days': '7-Day Trend',
                '30days': 'This Month Trend',
                '12months': 'This Year Trend'
            };
            document.getElementById('chartTitle').textContent = titles[period];
            
            // Reload chart data
            loadChartData();
        }
        
        async function loadChartData() {
            try {
                let url = `../../api/owner-chart-data.php?period=${currentPeriod}`;
                if (currentBranchId) {
                    url += `&branch_id=${currentBranchId}`;
                }
                
                console.log('Loading chart data from:', url);
                    
                const response = await fetch(url);
                const result = await response.json();
                
                console.log('Chart data response:', result);
                
                if (result.success && result.data && weeklyChart) {
                    const data = result.data;
                    weeklyChart.data.labels = data.labels;
                    weeklyChart.data.datasets[0].data = data.income;
                    weeklyChart.data.datasets[1].data = data.expense;
                    weeklyChart.update();
                } else {
                    console.error('Failed to load chart data:', result);
                }
            } catch (error) {
                console.error('Error loading chart:', error);
            }
        }
        
        async function loadRecentTransactions() {
            try {
                const url = currentBranchId 
                    ? `../../api/owner-recent-transactions.php?branch_id=${currentBranchId}`
                    : '../../api/owner-recent-transactions.php';
                    
                const response = await fetch(url);
                const data = await response.json();
                
                const container = document.getElementById('recentTransactions');
                
                if (data.success && data.transactions.length > 0) {
                    container.innerHTML = '';
                    data.transactions.forEach(trans => {
                        const item = document.createElement('div');
                        item.className = 'transaction-item';
                        
                        const isIncome = trans.transaction_type === 'income';
                        const color = isIncome ? '#10b981' : '#ef4444';
                        const icon = isIncome ? 'arrow-up' : 'arrow-down';
                        
                        item.innerHTML = `
                            <div class="transaction-info">
                                <div class="transaction-desc">
                                    <i data-feather="${icon}" style="width: 14px; height: 14px; color: ${color};"></i>
                                    ${trans.description || trans.category_name}
                                </div>
                                <div class="transaction-meta">
                                    ${trans.division_name} • ${formatDate(trans.transaction_date)} ${trans.transaction_time}
                                </div>
                            </div>
                            <div class="transaction-amount" style="color: ${color};">
                                ${isIncome ? '+' : '-'} ${formatRupiah(trans.amount)}
                            </div>
                        `;
                        container.appendChild(item);
                    });
                    feather.replace();
                } else {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i data-feather="inbox" class="empty-icon"></i>
                            <p>No transactions today</p>
                        </div>
                    `;
                    feather.replace();
                }
            } catch (error) {
                console.error('Error loading transactions:', error);
            }
        }
        
        function initChart() {
            const ctx = document.getElementById('weeklyChart').getContext('2d');
            weeklyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Income',
                            data: [],
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Expense',
                            data: [],
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: { size: 11 }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                                },
                                font: { size: 10 }
                            }
                        },
                        x: {
                            ticks: {
                                font: { size: 10 }
                            }
                        }
                    }
                }
            });
        }
        
        async function refreshData() {
            const btn = document.getElementById('refreshBtn');
            btn.innerHTML = '<div class="loading-spinner"></div>';
            btn.disabled = true;
            
            await loadBranchData();
            
            setTimeout(() => {
                btn.innerHTML = '<i data-feather="refresh-cw" style="width: 20px; height: 20px;"></i>';
                feather.replace();
                btn.disabled = false;
            }, 500);
        }
        
        function formatRupiah(number) {
            return 'Rp ' + Number(number).toLocaleString('id-ID');
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { day: 'numeric', month: 'short' };
            return date.toLocaleDateString('id-ID', options);
        }
        
        function showReports() {
            alert('Fitur laporan akan tersedia di update berikutnya');
        }
        
        function showOccupancy() {
            alert('Detailed occupancy feature will be available in the next update');
        }
        
        // Auto refresh every 2 minutes
        setInterval(refreshData, 120000);
    </script>
</body>
</html>
