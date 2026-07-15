<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Aquarium IoT Control Panel</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
    
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f0f2f5; margin: 0; display: flex; color: #333; }
        
        /* Sidebar Styling */
        .sidebar { width: 260px; background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%); color: white; height: 100vh; padding: 20px; box-sizing: border-box; overflow-y: auto; box-shadow: 4px 0 10px rgba(0,0,0,0.1); z-index: 10;}
        .sidebar h3 { font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .sidebar button { cursor: pointer; border: none; border-radius: 6px; color: white; transition: all 0.3s ease;}
        .device-item { display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.1); padding: 5px; margin-bottom: 10px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); transition: 0.3s; }
        .device-item:hover { background: rgba(255,255,255,0.2); transform: translateX(5px); }
        .device-btn { flex: 1; background: transparent; text-align: left; padding: 10px; font-size: 14px; font-family: 'Prompt', sans-serif;}
        .action-btn { padding: 8px; margin-left: 4px; font-size: 12px; font-weight: bold; border-radius: 4px;}
        
        /* Main Content */
        .main-content { flex: 1; padding: 30px; height: 100vh; overflow-y: auto; box-sizing: border-box; }
        .header { background-color: white; padding: 20px 25px; color: #2c3e50; display: flex; justify-content: space-between; align-items: center; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);}
        .header h2 { margin: 0; font-weight: 600; color: #1e3c72; }
        
        /* Cards */
        .card-container { display: flex; gap: 25px; flex-wrap: wrap; margin-bottom: 25px;}
        .card { background-color: white; padding: 25px; border-radius: 12px; flex: 1; min-width: 280px; box-shadow: 0 8px 20px rgba(0,0,0,0.04); border-top: 5px solid #3498db; transition: transform 0.3s ease; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.08); }
        .value { font-size: 2.8em; font-weight: 600; margin-top: 15px; color: #2980b9; }
        
        /* Controls & Buttons */
        .controls { margin-top: 25px; background-color: white; padding: 30px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.04); }
        .btn { padding: 12px 24px; margin-right: 15px; cursor: pointer; border: none; border-radius: 8px; font-weight: 600; font-family: 'Prompt', sans-serif; color: white; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.15); display: inline-flex; align-items: center; gap: 8px;}
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.2); filter: brightness(1.1); }
        .btn:active { transform: translateY(1px); box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn-open { background: linear-gradient(135deg, #2ecc71, #27ae60); }
        .btn-close { background: linear-gradient(135deg, #9b59b6, #8e44ad); }
        .btn-off { background: linear-gradient(135deg, #7f8c8d, #34495e); } /* เพิ่มสีปุ่มปิดไฟ */
        
        /* Toggle Switch */
        .mode-container { display: flex; align-items: center; gap: 15px; background: #f8f9fa; padding: 10px 20px; border-radius: 50px; border: 1px solid #e9ecef;}
        .switch { position: relative; display: inline-block; width: 60px; height: 34px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #f39c12; transition: .4s; border-radius: 34px; box-shadow: inset 0 2px 5px rgba(0,0,0,0.2);}
        .slider:before { position: absolute; content: ""; height: 26px; width: 26px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.2);}
        input:checked + .slider { background-color: #3498db; }
        input:checked + .slider:before { transform: translateX(26px); }
        
        .mode-text { font-size: 18px; font-weight: 600; }
        .text-auto { color: #3498db; }
        .text-manual { color: #f39c12; }

        /* Tables & Alerts */
        .status-alert { color: #e74c3c; animation: blinker 1.5s linear infinite; box-shadow: 0 4px 15px rgba(231, 76, 60, 0.2);}
        @keyframes blinker { 50% { opacity: 0.5; } }
        .log-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 15px; border-radius: 8px; overflow: hidden;}
        .log-table th, .log-table td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        .log-table th { background-color: #fdf2f2; color: #e74c3c; font-weight: 600; }
        .log-table tr:hover { background-color: #f9f9f9; }
        .text-danger { color: #e74c3c; font-weight: bold; }
        
        /* Inputs */
        input[type=number], input[type=text] { padding: 10px; border-radius: 6px; border: 1px solid #ddd; font-family: 'Prompt', sans-serif; transition: 0.3s; }
        input[type=number]:focus, input[type=text]:focus { border-color: #3498db; outline: none; box-shadow: 0 0 5px rgba(52, 152, 219, 0.3); }
    </style>
</head>
<body>
    <audio id="alertSound" src="/sounds/my-alert.mp3" preload="auto" loop></audio>

    <div class="sidebar">
        <h3 style="text-align: center; margin-bottom: 25px;">🎛️ รายการอุปกรณ์</h3>
        
        @foreach($devices as $device)
            <div class="device-item">
                <button class="device-btn" id="sidebar-btn-{{ $device->device_id }}" onclick="loadDevice({{ $device->device_id }}, '{{ $device->device_name }}')">
                    <span style="font-size: 16px;">🐟</span> {{ $device->device_name }}
                    <span id="sidebar-alert-{{ $device->device_id }}" class="sidebar-alert" style="display: none; color: #ffcccc; font-weight: bold; animation: blinker 1.5s linear infinite; margin-left: 5px;">⚠️</span>
                </button>
                
                @if(Auth::user()->role == 'admin')
                    <form action="/toggle-device/{{ $device->device_id }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="action-btn" title="เปิด/ปิด การทำงาน" style="background-color: {{ $device->device_status == 'online' ? '#2ecc71' : '#95a5a6' }};">
                            {{ $device->device_status == 'online' ? 'ON' : 'OFF' }}
                        </button>
                    </form>
                    <form action="/delete-device/{{ $device->device_id }}" method="POST" style="margin:0;" onsubmit="return confirm('ยืนยันการลบอุปกรณ์นี้?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn" title="ลบอุปกรณ์" style="background-color: #e74c3c;">X</button>
                    </form>
                @endif
            </div>
        @endforeach

        @if(Auth::user()->role == 'admin')
            <hr style="border-color: rgba(255,255,255,0.2); margin: 25px 0;">
            <h4 style="text-align: center; margin-bottom: 15px; color: #a8ff78;">+ เพิ่มอุปกรณ์ใหม่</h4>
            <form action="/add-device" method="POST" style="display: flex; flex-direction: column; gap: 12px;">
                @csrf
                <input type="text" name="device_name" placeholder="ตั้งชื่อตู้ปลา/อุปกรณ์" required>
                <button type="submit" class="btn btn-open" style="width: 100%; justify-content: center;">บันทึกอุปกรณ์</button>
            </form>
        @endif

        <hr style="border-color: rgba(255,255,255,0.2); margin: 25px 0;">
        <form action="/logout" method="POST">
            @csrf
            <button type="submit" class="btn" style="background-color: #e74c3c; width: 100%; justify-content: center;">🚪 ออกจากระบบ</button>
        </form>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>💧 Aquarium Smart Dashboard</h2>
            <div style="font-weight: 500; color: #7f8c8d;">
                👤 {{ Auth::user()->username }} | 🛡️ {{ Auth::user()->role }}
            </div>
        </div>

        @if($devices->isEmpty())
            <div style="text-align: center; padding: 80px; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h2 style="color: #95a5a6;">ยังไม่มีอุปกรณ์ในระบบ</h2>
                <p style="color: #7f8c8d;">กรุณาเพิ่มอุปกรณ์ใหม่ที่แถบเมนูด้านซ้ายเพื่อเริ่มต้นใช้งาน</p>
            </div>
        @else
            <h3 id="alert-report" class="status-alert" style="display: none; background: #ffebee; padding: 15px; border-radius: 8px; border-left: 5px solid #e74c3c;">
                ⚠️ แจ้งเตือนฉุกเฉิน: ตรวจพบคุณภาพน้ำผิดปกติเกินเกณฑ์ที่กำหนด!
            </h3>

            <div class="card-container">
                <div class="card" style="border-top-color: #2ecc71;">
                    <h3 style="margin: 0; color: #7f8c8d;">🟢 ค่าความกรด-ด่าง (pH)</h3>
                    <div class="value" id="ph-val" style="color: #2ecc71;">--</div>
                </div>
                <div class="card" style="border-top-color: #f39c12;">
                    <h3 style="margin: 0; color: #7f8c8d;">🟡 ความขุ่นของน้ำ (NTU)</h3>
                    <div class="value" id="turb-val" style="color: #f39c12;">-- NTU</div>
                </div>
                <div class="card" style="border-top-color: #e74c3c;">
                    <h3 style="margin: 0; color: #7f8c8d;">🌡️ อุณหภูมิน้ำ (°C)</h3>
                    <div class="value" id="temp-val" style="color: #e74c3c;">-- °C</div>
                </div>
            </div>

            <div class="card-container">
                <div class="card" style="border-top-color: #2ecc71; flex: 1; min-width: 300px;">
                    <h4 style="margin: 0 0 15px 0; color: #2ecc71;">📈 กราฟแนวโน้ม pH</h4>
                    <div style="height: 250px; width: 100%;"><canvas id="phChart"></canvas></div>
                </div>
                <div class="card" style="border-top-color: #f39c12; flex: 1; min-width: 300px;">
                    <h4 style="margin: 0 0 15px 0; color: #f39c12;">📈 กราฟแนวโน้มความขุ่น</h4>
                    <div style="height: 250px; width: 100%;"><canvas id="turbChart"></canvas></div>
                </div>
                <div class="card" style="border-top-color: #e74c3c; flex: 1; min-width: 300px;">
                    <h4 style="margin: 0 0 15px 0; color: #e74c3c;">📈 กราฟแนวโน้มอุณหภูมิ</h4>
                    <div style="height: 250px; width: 100%;"><canvas id="tempChart"></canvas></div>
                </div>
            </div>

            <div class="controls">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px dashed #eee; padding-bottom: 20px; margin-bottom: 20px; flex-wrap: wrap; gap: 20px;">
                    <div>
                        <h3 style="margin: 0; color: #2c3e50;">
                            🎮 แผงควบคุมอุปกรณ์ <span id="current-device-display" style="font-size:16px; color:#7f8c8d; font-weight: 400;"></span>
                        </h3>
                    </div>
                    
                    <div class="mode-container" id="mode-container-box">
                        <span class="mode-text text-manual" id="label-manual">MANUAL</span>
                        <label class="switch">
                            <input type="checkbox" id="modeToggle" onchange="toggleSystemMode()">
                            <span class="slider round"></span>
                        </label>
                        <span class="mode-text text-auto" id="label-auto" style="opacity: 0.4;">AUTO</span>
                    </div>
                </div>
                
                @if(Auth::user()->role == 'admin')
                    <div id="manual-controls" style="margin-bottom: 25px; display: flex; gap: 15px; flex-wrap: wrap;">
                        <!-- 🌟 ปุ่มถูกอัปเกรดให้เรียกฟังก์ชันแบบแยกประเภท -->
                        <button class="btn btn-open" onclick="sendDeviceCommand('SERVO', 'OPEN')">
                            🧪 ปล่อยสารบำบัด
                        </button>
                        <button class="btn btn-close" onclick="sendDeviceCommand('UV', 'ON')">
                            💡 เปิดไฟ UV
                        </button>
                    </div>

                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef;">
                        <h4 style="color: #2c3e50; margin: 0 0 15px 0;">⚙️ ตั้งค่าเกณฑ์แจ้งเตือนและทำงานอัตโนมัติ (สำหรับอุปกรณ์นี้)</h4>
                        <form id="threshold-form" onsubmit="saveThresholds(event)" style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
                            <div>
                                <label style="font-size: 14px; color: #555; font-weight: 600;">pH ต่ำสุด:</label><br>
                                <input type="number" step="0.1" name="ph_min" id="input-ph-min" style="width: 100px;" required>
                            </div>
                            <div>
                                <label style="font-size: 14px; color: #555; font-weight: 600;">pH สูงสุด:</label><br>
                                <input type="number" step="0.1" name="ph_max" id="input-ph-max" style="width: 100px;" required>
                            </div>
                            <div>
                                <label style="font-size: 14px; color: #555; font-weight: 600;">ความขุ่นสูงสุด (NTU):</label><br>
                                <input type="number" step="0.1" name="turb_max" id="input-turb-max" style="width: 100px;" required>
                            </div>
                            <button type="submit" class="btn" style="background-color: #34495e; padding: 10px 20px;">💾 บันทึกเกณฑ์</button>
                        </form>
                    </div>
                @else
                    <div class="alert-box" style="background-color: #f39c12; color: white;">
                        ⚠️ สิทธิ์การสั่งการและตั้งค่าอุปกรณ์สงวนไว้สำหรับ Admin เท่านั้น
                    </div>
                @endif
            </div>

            <div class="card-container" style="margin-top: 25px;">
                <div class="card" style="flex: 100%; border-top-color: #e74c3c;">
                    <h3 style="margin: 0 0 15px 0; color: #e74c3c;">📋 ประวัติคุณภาพน้ำผิดปกติ (5 รายการล่าสุด)</h3>
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th>วัน-เวลา</th>
                                <th>ค่า pH</th>
                                <th>ความขุ่น (NTU)</th>
                                <th>อุณหภูมิ (°C)</th>
                            </tr>
                        </thead>
                        <tbody id="alert-log-body">
                            <tr><td colspan="4" style="text-align: center; color: #7f8c8d;">กำลังโหลดข้อมูล...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    @if($devices->isNotEmpty())
    <script>
        let currentDevice = {{ $devices->first()->device_id }};
        let currentDeviceName = "{{ $devices->first()->device_name }}";
        let isThresholdLoaded = false; 
        
        let phChart = null; 
        let turbChart = null; 
        let tempChart = null;

        function createChart(ctxId, labelName, lineColor) {
            const ctx = document.getElementById(ctxId).getContext('2d');
            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{ 
                        label: labelName, data: [], 
                        borderColor: lineColor, backgroundColor: lineColor + '33', 
                        fill: true, tension: 0.4, pointRadius: 2, borderWidth: 2 
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: { x: { ticks: { maxTicksLimit: 8 } }, y: { display: true } },
                    plugins: { legend: { display: false } }
                }
            });
        }

        function initCharts() {
            phChart = createChart('phChart', 'pH Value', '#2ecc71');
            turbChart = createChart('turbChart', 'Turbidity (NTU)', '#f39c12');
            tempChart = createChart('tempChart', 'Temperature (°C)', '#e74c3c');
        }

        function loadDevice(id, name) { 
            currentDevice = id; 
            currentDeviceName = name;
            isThresholdLoaded = false; 
            
            document.getElementById('current-device-display').innerText = `(ตู้: ${name})`;
            document.getElementById('ph-val').innerText = '--';
            document.getElementById('temp-val').innerText = '-- °C';
            document.getElementById('turb-val').innerText = '-- NTU';
            
            if(phChart) phChart.resetZoom();
            if(turbChart) turbChart.resetZoom();
            if(tempChart) tempChart.resetZoom();

            fetchData(); 
        }

        function fetchSidebarAlerts() {
            fetch('/api/get-alerts-summary')
                .then(res => res.json())
                .then(data => {
                    if (data && data.alerting_devices) {
                        document.querySelectorAll('.sidebar-alert').forEach(el => el.style.display = 'none');
                        data.alerting_devices.forEach(id => {
                            let icon = document.getElementById(`sidebar-alert-${id}`);
                            if(icon) { icon.style.display = 'inline-block'; }
                        });
                    }
                }).catch(err => console.log(err));
        }

        function fetchData() {
            fetch(`/api/get-latest-data/${currentDevice}`)
                .then(res => res.json())
                .then(data => {
                    if(!data) return;

                    if (data.device_status === 'offline') {
                        document.getElementById('ph-val').innerText = 'OFFLINE';
                        document.getElementById('ph-val').style.color = '#95a5a6';
                        document.getElementById('temp-val').innerText = 'OFFLINE';
                        document.getElementById('temp-val').style.color = '#95a5a6';
                        document.getElementById('turb-val').innerText = 'OFFLINE';
                        document.getElementById('turb-val').style.color = '#95a5a6';

                        if(phChart) { phChart.data.labels = []; phChart.data.datasets[0].data = []; phChart.update(); }
                        if(turbChart) { turbChart.data.labels = []; turbChart.data.datasets[0].data = []; turbChart.update(); }
                        if(tempChart) { tempChart.data.labels = []; tempChart.data.datasets[0].data = []; tempChart.update(); }

                        document.getElementById('alert-log-body').innerHTML = '<tr><td colspan="4" style="text-align:center; color: #95a5a6; padding: 20px;">💤 อุปกรณ์อยู่ในโหมดพักการทำงาน (Offline)</td></tr>';
                        document.getElementById('mode-container-box').style.display = 'none';
                        const mc = document.getElementById('manual-controls');
                        if (mc) mc.style.display = 'none';

                        checkAlerts(0, 0, 0, 0, 0, true); 
                        return; 
                    }

                    document.getElementById('ph-val').style.color = '#2ecc71';
                    document.getElementById('temp-val').style.color = '#e74c3c';
                    document.getElementById('turb-val').style.color = '#f39c12';
                    document.getElementById('mode-container-box').style.display = 'flex';
                    
                    const toggle = document.getElementById('modeToggle');
                    const labelAuto = document.getElementById('label-auto');
                    const labelManual = document.getElementById('label-manual');
                    const manualControls = document.getElementById('manual-controls');

                    if(toggle) {
                        if(data.current_mode === 'AUTO') {
                            toggle.checked = true;
                            labelAuto.style.opacity = '1';
                            labelManual.style.opacity = '0.4';
                            if(manualControls) manualControls.style.display = 'none'; 
                        } else {
                            toggle.checked = false;
                            labelAuto.style.opacity = '0.4';
                            labelManual.style.opacity = '1';
                            if(manualControls) manualControls.style.display = 'flex'; 
                        }
                    }

                    const form = document.getElementById('threshold-form');
                    if(form && !isThresholdLoaded) {
                        document.getElementById('input-ph-min').value = data.ph_min === null ? '' : data.ph_min;
                        document.getElementById('input-ph-max').value = data.ph_max === null ? '' : data.ph_max;
                        document.getElementById('input-turb-max').value = data.turb_max === null ? '' : data.turb_max;
                        isThresholdLoaded = true; 
                    }

                    if(data.ph_value !== null) {
                        document.getElementById('ph-val').innerText = parseFloat(data.ph_value).toFixed(2);
                        document.getElementById('temp-val').innerText = parseFloat(data.temperature).toFixed(1) + ' °C';
                        document.getElementById('turb-val').innerText = parseFloat(data.turbidity).toFixed(2) + ' NTU';
                        checkAlerts(data.ph_value, data.turbidity, data.ph_min, data.ph_max, data.turb_max, false);
                    }

                    if (data.history && phChart && turbChart && tempChart) {
                        let timeLabels = data.history.map(item => {
                            let d = new Date(item.created_at);
                            return d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0');
                        });
                        phChart.data.labels = timeLabels;
                        phChart.data.datasets[0].data = data.history.map(item => item.ph_value);
                        phChart.update('none');

                        turbChart.data.labels = timeLabels;
                        turbChart.data.datasets[0].data = data.history.map(item => item.turbidity);
                        turbChart.update('none');

                        tempChart.data.labels = timeLabels;
                        tempChart.data.datasets[0].data = data.history.map(item => item.temperature);
                        tempChart.update('none');
                    }

                    if (data.alerts) {
                        let logHtml = '';
                        if (data.alerts.length === 0) {
                            logHtml = '<tr><td colspan="4" style="text-align:center; color: #2ecc71; font-weight:bold; padding: 20px;">✅ ปกติดีเยี่ยม ไม่มีรายงานน้ำเสีย</td></tr>';
                        } else {
                            data.alerts.forEach(item => {
                                let dt = new Date(item.created_at).toLocaleString('th-TH');
                                let isPhBad = item.ph_value < data.ph_min || item.ph_value > data.ph_max;
                                let isTurbBad = item.turbidity > data.turb_max;
                                logHtml += `<tr>
                                    <td>${dt}</td>
                                    <td class="${isPhBad ? 'text-danger' : ''}">${parseFloat(item.ph_value).toFixed(2)}</td>
                                    <td class="${isTurbBad ? 'text-danger' : ''}">${parseFloat(item.turbidity).toFixed(2)}</td>
                                    <td>${parseFloat(item.temperature).toFixed(1)}</td>
                                </tr>`;
                            });
                        }
                        document.getElementById('alert-log-body').innerHTML = logHtml;
                    }
                }).catch(err => console.log(err));
        }

        function checkAlerts(ph, turbidity, ph_min, ph_max, turb_max, isOffline) {
            const report = document.getElementById('alert-report');
            const sound = document.getElementById('alertSound');
            
            if (isOffline) {
                report.style.display = 'none';
                sound.pause();
                sound.currentTime = 0;
                return;
            }

            if (ph < ph_min || ph > ph_max || turbidity > turb_max) {
                report.style.display = 'block';
                let playPromise = sound.play();
                
                if (playPromise !== undefined) {
                    playPromise.catch(error => {
                        report.innerHTML = '⚠️ แจ้งเตือนฉุกเฉิน: ตรวจพบคุณภาพน้ำผิดปกติ!<br><span style="font-size: 16px; font-weight: bold; cursor: pointer; text-decoration: underline; color: #c0392b;">🔊 คลิกที่ข้อความนี้เพื่อเปิดเสียงแจ้งเตือน!</span>';
                        report.onclick = function() {
                            sound.play();
                            report.innerHTML = '⚠️ แจ้งเตือนฉุกเฉิน: ตรวจพบคุณภาพน้ำผิดปกติเกินเกณฑ์ที่กำหนด!';
                            report.onclick = null; 
                        };
                    });
                }
            } else {
                report.style.display = 'none';
                sound.pause();
                sound.currentTime = 0;
                report.innerHTML = '⚠️ แจ้งเตือนฉุกเฉิน: ตรวจพบคุณภาพน้ำผิดปกติเกินเกณฑ์ที่กำหนด!';
                report.onclick = null;
            }
        }

        function saveThresholds(event) {
            event.preventDefault(); 
            fetch(`/update-thresholds/${currentDevice}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ 
                    ph_min: document.getElementById('input-ph-min').value, 
                    ph_max: document.getElementById('input-ph-max').value, 
                    turb_max: document.getElementById('input-turb-max').value 
                })
            }).then(async res => {
                const data = await res.json();
                if (!res.ok) alert("❌ ตั้งค่าไม่สำเร็จ");
                else { alert('✅ ' + data.message); isThresholdLoaded = false; fetchData(); }
            }).catch(err => alert('❌ เกิดข้อผิดพลาดในการส่งข้อมูล'));
        }

        // 🌟 ฟังก์ชันสลับโหมด อัปเดตใหม่ ไม่เตะคำสั่งเดิมทิ้ง ส่งแค่โหมดอย่างเดียว!
        function toggleSystemMode() {
            const isAuto = document.getElementById('modeToggle').checked;
            const targetMode = isAuto ? 'AUTO' : 'MANUAL';
            
            fetch('/send-command', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ device_id: currentDevice, operating_mode: targetMode })
            }).then(res => {
                if(res.ok) fetchData(); 
            });
        }

        // 🌟 ฟังก์ชันสั่งงานแยกประเภท (ฉลาดขึ้น!)
        function sendDeviceCommand(type, value) {
            let payload = { device_id: currentDevice, operating_mode: 'MANUAL' };
            
            // แยกคีย์ส่งไปหา Controller ให้ตรงตามฐานข้อมูลที่เราเพิ่งแก้
            if (type === 'SERVO') { payload.command_action = value; }
            if (type === 'UV')    { payload.uv_status = value; }

            fetch('/send-command', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(payload)
            }).then(res => {
                if(res.ok) alert(`📡 ส่งคำสั่งเรียบร้อยแล้ว`);
                else alert('❌ เกิดข้อผิดพลาดในการส่งคำสั่ง');
            });
        }

        document.getElementById('current-device-display').innerText = `(ตู้: ${currentDeviceName})`;
        initCharts(); 
        
        setInterval(() => {
            fetchData();
            fetchSidebarAlerts();
        }, 2000); 
        
        fetchData();
        fetchSidebarAlerts();
    </script>
    @endif
</body>
</html>