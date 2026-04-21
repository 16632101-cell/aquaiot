<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Aquarium IoT Control Panel</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; display: flex; }
        .sidebar { width: 260px; background-color: #2c3e50; color: white; height: 100vh; padding: 20px; box-sizing: border-box; overflow-y: auto;}
        .sidebar button { cursor: pointer; border: none; border-radius: 4px; color: white; transition: 0.3s;}
        .device-item { display: flex; justify-content: space-between; align-items: center; background: #34495e; padding: 5px; margin-bottom: 8px; border-radius: 4px; }
        .device-btn { flex: 1; background: transparent; text-align: left; padding: 8px; font-size: 14px; }
        .device-btn:hover { background: #3498db; }
        .action-btn { padding: 6px; margin-left: 2px; font-size: 12px; font-weight: bold;}
        .main-content { flex: 1; padding: 20px; height: 100vh; overflow-y: auto; }
        .header { background-color: #34495e; padding: 15px; color: white; display: flex; justify-content: space-between; border-radius: 8px; margin-bottom: 20px;}
        .card-container { display: flex; gap: 20px; flex-wrap: wrap; }
        .card { background-color: white; color: #333; padding: 30px; border-radius: 8px; flex: 1; min-width: 250px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-top: 5px solid #3498db; }
        .value { font-size: 3em; font-weight: bold; margin-top: 15px; color: #2980b9; }
        .status-alert { color: #e74c3c; animation: blinker 1s linear infinite; }
        @keyframes blinker { 50% { opacity: 0; } }
        .controls { margin-top: 20px; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .btn { padding: 10px 20px; margin-right: 10px; cursor: pointer; border: none; border-radius: 4px; font-weight: bold; color: white; }
        .btn-open { background-color: #2ecc71; }
        .btn-close { background-color: #e74c3c; }
        .alert-box { padding: 10px; background-color: #2ecc71; color: white; border-radius: 5px; margin-bottom: 15px; text-align: center;}
        .mode-badge { padding: 4px 8px; border-radius: 4px; font-size: 14px; color: white; vertical-align: middle; margin-left: 10px;}
        .mode-auto { background-color: #3498db; }
        .mode-manual { background-color: #f39c12; }
        .no-device-screen { text-align: center; padding: 50px; background: white; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-top: 20px;}
        
        /* สไตล์สำหรับตารางแจ้งเตือน */
        .log-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px;}
        .log-table th, .log-table td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        .log-table th { background-color: #fdf2f2; color: #e74c3c; }
        .text-danger { color: #e74c3c; font-weight: bold; }
    </style>
</head>
<body>
    <audio id="alertSound" src="/sounds/my-alert.mp3" preload="auto" loop></audio>

    <div class="sidebar">
        <h3 style="text-align: center; margin-bottom: 20px;">รายการอุปกรณ์</h3>
        
        @foreach($devices as $device)
            <div class="device-item">
                <button class="device-btn" onclick="loadDevice({{ $device->device_id }}, '{{ $device->device_name }}')">
                    ID: {{ $device->device_id }} | {{ $device->device_name }}
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
                        <button type="submit" class="action-btn" title="ลบอุปกรณ์" style="background-color: #e74c3c;">X</button>
                    </form>
                @endif
            </div>
        @endforeach

        @if(Auth::user()->role == 'admin')
            <hr style="border-color: #555; margin: 20px 0;">
            <h4 style="text-align: center; margin-bottom: 10px; color: #2ecc71;">+ เพิ่มอุปกรณ์ใหม่</h4>
            <form action="/add-device" method="POST" style="display: flex; flex-direction: column; gap: 10px;">
                @csrf
                <input type="text" name="device_name" placeholder="ตั้งชื่ออุปกรณ์" required style="padding: 8px; border-radius: 4px; border: none;">
                <button type="submit" style="background-color: #2ecc71; padding: 8px;">บันทึกอุปกรณ์</button>
            </form>
        @endif

        <hr style="border-color: #555; margin: 20px 0;">
        <form action="/logout" method="POST">
            @csrf
            <button type="submit" style="background-color: #e74c3c; width: 100%; padding: 10px;">ออกจากระบบ</button>
        </form>
    </div>

    <div class="main-content">
        <div class="header">
            <h2 style="margin: 0;">Aquarium IoT Control Panel</h2>
            <div style="margin-top: 6px;">User: {{ Auth::user()->username }} | Role: {{ Auth::user()->role }}</div>
        </div>

        @if(session('success')) 
            <div class="alert-box">{{ session('success') }}</div> 
        @endif
        @if($errors->any()) 
            <div class="alert-box" style="background-color: #e74c3c;">{{ $errors->first() }}</div> 
        @endif

        @if($devices->isEmpty())
            <div class="no-device-screen">
                <h2 style="color: #7f8c8d;">ยังไม่มีอุปกรณ์ในระบบ</h2>
                <p>กรุณาเพิ่มอุปกรณ์ใหม่ที่แถบเมนูด้านซ้ายเพื่อเริ่มต้นใช้งาน</p>
            </div>
        @else
            <h3 id="alert-report" class="status-alert" style="display: none; background: #ffebee; padding: 10px; border-radius: 5px;">
                ⚠️ แจ้งเตือน: ค่าเซ็นเซอร์ผิดปกติ!
            </h3>

            <div class="card-container">
                <div class="card">
                    <h3 style="margin: 0; color: #7f8c8d;">Water pH / Distance</h3>
                    <div class="value" id="ph-val">--</div>
                </div>
                <div class="card">
                    <h3 style="margin: 0; color: #7f8c8d;">Temperature</h3>
                    <div class="value" id="temp-val">-- °C</div>
                </div>
                <div class="card">
                    <h3 style="margin: 0; color: #7f8c8d;">Water Turbidity</h3>
                    <div class="value" id="turb-val">-- NTU</div>
                </div>

                <div class="card" style="flex: 100%; padding-bottom: 15px;">
                    <h3 style="margin: 0 0 15px 0; color: #34495e;">📈 กราฟแสดงค่าน้ำย้อนหลัง</h3>
                    <div style="height: 300px; width: 100%;">
                        <canvas id="waterChart"></canvas>
                    </div>
                </div>

                <div class="card" style="flex: 100%; border-top-color: #e74c3c;">
                    <h3 style="margin: 0; color: #e74c3c;">⚠️ ประวัติการแจ้งเตือนผิดปกติ (5 รายการล่าสุด)</h3>
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

            <div class="controls">
                <h3 style="color: #333;">
                    สถานะและการควบคุม 
                    <span id="current-device-display" style="font-size:16px; color:#3498db;"></span>
                    <span id="current-mode-display" class="mode-badge mode-auto">โหมด: --</span>
                </h3>
                
                @if(Auth::user()->role == 'admin')
                    <div style="margin-bottom: 20px;">
                        <button class="btn btn-open" onclick="sendCommand('OPEN', 'MANUAL')">เปิดปั๊ม/อุปกรณ์ (Manual)</button>
                        <button class="btn btn-close" onclick="sendCommand('CLOSE', 'MANUAL')">ปิดปั๊ม/อุปกรณ์ (Manual)</button>
                        <button class="btn" onclick="sendCommand('NONE', 'AUTO')" style="background-color: #3498db; color:white;">สลับเป็นโหมด AUTO</button>
                    </div>

                    <div style="border-top: 1px solid #eee; padding-top: 15px;">
                        <h4 style="color: #2c3e50; margin-bottom: 10px;">⚙️ ตั้งค่าเกณฑ์แจ้งเตือน (อุปกรณ์นี้)</h4>
                        <form id="threshold-form" onsubmit="saveThresholds(event)" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                            <div>
                                <label style="font-size: 14px; color: #555;">pH ต่ำสุด:</label><br>
                                <input type="number" step="0.1" name="ph_min" id="input-ph-min" style="width: 80px; padding: 5px;" required>
                            </div>
                            <div>
                                <label style="font-size: 14px; color: #555;">pH สูงสุด:</label><br>
                                <input type="number" step="0.1" name="ph_max" id="input-ph-max" style="width: 80px; padding: 5px;" required>
                            </div>
                            <div>
                                <label style="font-size: 14px; color: #555;">ความขุ่นสูงสุด:</label><br>
                                <input type="number" step="0.1" name="turb_max" id="input-turb-max" style="width: 80px; padding: 5px;" required>
                            </div>
                            <button type="submit" class="btn btn-open" style="padding: 7px 15px; background-color: #f39c12;">💾 บันทึกเกณฑ์</button>
                        </form>
                    </div>
                @else
                    <p style="color: #f39c12; font-weight: bold;">* สิทธิ์การสั่งการและตั้งค่าอุปกรณ์สงวนไว้สำหรับ Admin</p>
                @endif
            </div>
        @endif
    </div>

    @if($devices->isNotEmpty())
    <script>
        let currentDevice = {{ $devices->first()->device_id }};
        let currentDeviceName = "{{ $devices->first()->device_name }}";
        let isThresholdLoaded = false; 
        let waterChart = null; // ตัวแปรกราฟ

        // 🌟 สร้างกราฟเปล่าๆ เตรียมไว้ก่อน
        function initChart() {
            const ctx = document.getElementById('waterChart').getContext('2d');
            waterChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        { label: 'pH Value', data: [], borderColor: '#2ecc71', backgroundColor: '#2ecc71', tension: 0.3, yAxisID: 'y' },
                        { label: 'Turbidity (NTU)', data: [], borderColor: '#3498db', backgroundColor: '#3498db', tension: 0.3, yAxisID: 'y1' }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        y: { type: 'linear', display: true, position: 'left', title: {display: true, text: 'pH'} },
                        y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false }, title: {display: true, text: 'Turbidity'} }
                    }
                }
            });
        }

        function loadDevice(id, name) { 
            currentDevice = id; 
            currentDeviceName = name;
            isThresholdLoaded = false; 
            
            document.getElementById('current-device-display').innerText = `(กำลังดู: ${name})`;
            document.getElementById('ph-val').innerText = '--';
            document.getElementById('temp-val').innerText = '-- °C';
            document.getElementById('turb-val').innerText = '-- NTU';
            document.getElementById('alert-log-body').innerHTML = '<tr><td colspan="4" style="text-align: center; color: #7f8c8d;">กำลังโหลดข้อมูล...</td></tr>';
            fetchData(); 
        }

        function fetchData() {
            fetch(`/api/get-latest-data/${currentDevice}`)
                .then(res => res.json())
                .then(data => {
                    if(!data) return;
                    
                    const modeBadge = document.getElementById('current-mode-display');
                    modeBadge.innerText = "โหมด: " + data.current_mode;
                    modeBadge.className = data.current_mode === 'AUTO' ? "mode-badge mode-auto" : "mode-badge mode-manual";

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
                        checkAlerts(data.ph_value, data.turbidity, data.ph_min, data.ph_max, data.turb_max);
                    }

                    // 🌟 อัปเดตกราฟ
                    if (data.history && waterChart) {
                        waterChart.data.labels = data.history.map(item => {
                            let d = new Date(item.created_at);
                            return d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0');
                        });
                        waterChart.data.datasets[0].data = data.history.map(item => item.ph_value);
                        waterChart.data.datasets[1].data = data.history.map(item => item.turbidity);
                        waterChart.update();
                    }

                    // 🌟 อัปเดตตารางประวัติการแจ้งเตือน
                    if (data.alerts) {
                        let logHtml = '';
                        if (data.alerts.length === 0) {
                            logHtml = '<tr><td colspan="4" style="text-align:center; color: #2ecc71;">✅ ปกติดี ไม่มีรายงานน้ำเสีย</td></tr>';
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

        function checkAlerts(ph, turbidity, ph_min, ph_max, turb_max) {
            const report = document.getElementById('alert-report');
            const sound = document.getElementById('alertSound');
            
            if (ph < ph_min || ph > ph_max || turbidity > turb_max) {
                report.style.display = 'block';
                sound.play().catch(() => {}); 
            } else {
                report.style.display = 'none';
                sound.pause();
                sound.currentTime = 0;
            }
        }

        function saveThresholds(event) {
            event.preventDefault(); 
            let ph_min = document.getElementById('input-ph-min').value;
            let ph_max = document.getElementById('input-ph-max').value;
            let turb_max = document.getElementById('input-turb-max').value;

            fetch(`/update-thresholds/${currentDevice}`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ph_min: ph_min, ph_max: ph_max, turb_max: turb_max })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    let errorMsg = "❌ ตั้งค่าไม่สำเร็จ:\n";
                    if(data.errors) {
                        for(let key in data.errors) errorMsg += `- ${data.errors[key][0]}\n`;
                    } else {
                        errorMsg += data.message;
                    }
                    alert(errorMsg); 
                } else {
                    alert('✅ ' + data.message);
                    isThresholdLoaded = false; 
                    fetchData(); 
                }
            })
            .catch(err => alert('❌ เกิดข้อผิดพลาดในการส่งข้อมูล'));
        }

        function sendCommand(action, mode) {
            fetch('/send-command', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ device_id: currentDevice, command_action: action, operating_mode: mode })
            }).then(res => {
                if(res.ok) {
                    alert(`ส่งคำสั่งเรียบร้อยแล้ว`);
                    fetchData(); 
                } else {
                    alert('เกิดข้อผิดพลาดในการส่งคำสั่ง');
                }
            }).catch(err => console.log(err));
        }

        document.getElementById('current-device-display').innerText = `(กำลังดู: ${currentDeviceName})`;
        initChart(); // เรียกใช้กราฟครั้งแรก
        setInterval(fetchData, 2000); 
        fetchData();
    </script>
    @endif
</body>
</html>