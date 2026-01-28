<?php 
require 'db.php';
$sql = "SELECT l.*, a.type, a.config 
        FROM alerts_logs l 
        LEFT JOIN alerts a ON l.alert_id = a.id 
        ORDER BY l.created_at DESC LIMIT 15";
$stmt = $pdo->query($sql);
$logs = $stmt->fetchAll();

$total_alerts = $pdo->query("SELECT COUNT(*) FROM alerts")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P.A.D.IA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   
    <style>
        body { background-color: #f8f9fa; }
        .status-badge { font-size: 0.9em; min-width: 80px; }
        .console-box {
            background-color: #000;
            color: #0f0;
            font-family: 'Courier New', Courier, monospace;
            padding: 15px;
            border-radius: 5px;
            font-size: 0.9rem;
            border: 1px solid #333;
        }
        .console-line { margin-bottom: 5px; display: block; }
        .console-warning { color: #ffc107; }
        .console-danger { color: #ff3333; font-weight: bold; }
        .blink { animation: blinker 1s linear infinite; }
        @keyframes blinker { 50% { opacity: 0; } }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">P.A.D.<strong>AI</strong></a>
        </div>
    </nav>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-7">
                <h3>Monitor en Tiempo Real</h3>
                <p class="text-muted">Centro de mando: </p>
            </div>
            <div class="col-md-5 text-end">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCrear">
                    + Nueva Alerta
                </button>
                <button class="btn btn-danger ms-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalStress">
                    LANZAR ATAQUE IA
                </button>
                <button class="btn btn-warning ms-2" onclick="cargarAlertasParaBorrar()" data-bs-toggle="modal" data-bs-target="#modalGestion">
                    Gestionar
                </button>
                <div class="mt-2">
                    <span class="badge bg-info text-dark">Objetivos: <?php echo $total_alerts; ?></span>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Hora</th>
                            <th>Objetivo</th>
                            <th>Estado</th>
                            <th style="width: 40%;">Informe IA / Mensaje</th>
                            <th>Latencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): 
                            $config = json_decode($log['config'], true);
                            $url = $config['url'] ?? 'Eliminado';
                            $isError = $log['status'] === 'ERROR';
                            $isAIReport = strpos($log['message'], '🤖') !== false || strpos($log['message'], '✅') !== false;
                        ?>
                        <tr class="<?php echo $isAIReport ? 'table-success' : ''; ?>">
                            <td><?php echo date('H:i:s', strtotime($log['created_at'])); ?></td>
                            <td><a href="<?php echo htmlspecialchars($url); ?>" target="_blank" class="text-decoration-none fw-bold"><?php echo htmlspecialchars($url); ?></a></td>
                            <td>
                                <span class="badge rounded-pill <?php echo $isError ? 'bg-danger' : 'bg-success'; ?> status-badge">
                                    <?php echo $log['status']; ?>
                                </span>
                            </td>
                            <td class="<?php echo $isError ? 'text-danger fw-bold' : ($isAIReport ? 'text-success fw-bold' : 'text-muted'); ?>">
                                <?php echo htmlspecialchars($log['message']); ?>
                            </td>
                            <td><?php echo $log['response_time']; ?> ms</td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (count($logs) === 0): ?>
                            <tr><td colspan="5" class="text-center p-4">Sistema inactivo. Esperando amenazas</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php 
$parches = $pdo->query("SELECT * FROM active_defenses ORDER BY applied_at DESC LIMIT 5")->fetchAll();
if(count($parches) > 0): 
?>
<div class="card shadow-sm mt-4 border-success">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">🛡️ Contramedidas Generadas (Auto-Patching)</h5>
    </div>
    <div class="card-body bg-dark text-light p-0">
        <table class="table table-dark table-hover mb-0" style="font-family: monospace;">
            <thead>
                <tr>
                    <th style="width: 15%">Vector</th>
                    <th style="width: 20%">Hora</th>
                    <th>Parche de Código Aplicado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parches as $p): ?>
                <tr>
                    <td class="text-warning fw-bold">> <?php echo $p['threat_vector']; ?></td>
                    <td class="text-muted"><?php echo date('H:i:s', strtotime($p['applied_at'])); ?></td>
                    <td>
                        <div style="background: #1e1e1e; padding: 10px; border-radius: 4px; color: #d4d4d4;">
                            <code><?php echo htmlspecialchars($p['patch_code']); ?></code>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
    </div>

    <div class="modal fade" id="modalCrear" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Añadir Web a Vigilar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formAlerta">
                        <div class="mb-3">
                            <label class="form-label">URL de la Web</label>
                            <input type="url" class="form-control" id="inputUrl" placeholder="https://..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Método</label>
                            <select class="form-select" id="inputMethod">
                                <option value="GET">GET (Solo lectura)</option>
                                <option value="POST">POST (Envío datos)</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Guardar Alerta</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalStress" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">☠️ IA Red Team: Ataque Dirigido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light">
                    <p class="small text-muted">La IA escaneará el objetivo, detectará la tecnología y seleccionará el mejor vector de ataque.</p>
                    <form id="formStress">
                        <div class="mb-3">
                            <label class="fw-bold">Objetivo (URL)</label>
                            <input type="url" class="form-control" id="stressUrl" placeholder="http://localhost/..." required>
                        </div>
                        
                        <div id="stressResult" class="d-none mb-3">
                            <div class="console-box">
                                <span class="console-line">> INICIANDO SISTEMA DE ATAQUE v3.0...</span>
                                <div id="consoleOutput"></div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 fw-bold">
                            🔍 ESCANEAR Y DESTRUIR
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalGestion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Administrar Webs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group" id="listaAlertasBorrar">
                        <li class="list-group-item text-center">Cargando...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setInterval(() => {
            const modalAbierto = document.querySelector('.modal.show');
            if (!modalAbierto) {
                console.log("Refrescando monitor...");
                location.reload();
            } else {
                console.log("Usuario ocupado, posponiendo refresco.");
            }
        }, 10000);

        document.getElementById('formAlerta').addEventListener('submit', function(e) {
            e.preventDefault(); 
            const datos = {
                url: document.getElementById('inputUrl').value,
                method: document.getElementById('inputMethod').value
            };
            fetch('api-crear-alerta.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            }).then(response => response.json()).then(data => {
                if(data.status === 'success') {
                    alert('Alerta creada'); location.reload();
                } else { alert('Error: ' + data.message); }
            });
        });

        document.getElementById('formStress').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button');
            const box = document.getElementById('stressResult');
            const output = document.getElementById('consoleOutput');
            const urlVal = document.getElementById('stressUrl').value;
            
   
            const webhookUrl = 'http://localhost:5678/webhook-test/ataque-inteligente'; 

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> IA: ESCANEANDO TECNOLOGÍA...';
            box.classList.remove('d-none');
            output.innerHTML = '';

            const ejecutarVisuales = (vector, analisis) => {
                output.innerHTML += `<span class='console-line console-warning'>> ANÁLISIS IA: ${analisis}</span>`;
                setTimeout(() => {
                    output.innerHTML += `<span class='console-line'>> SELECCIONANDO VECTOR...</span>`;
                    setTimeout(() => {
                        output.innerHTML += `<span class='console-line console-danger blink'>> EJECUTANDO: ${vector} 💀</span>`;
                        btn.className = 'btn btn-danger w-100 fw-bold';
                        btn.innerHTML = '⚠️ ATAQUE MASIVO EN CURSO';
                        
                        setTimeout(() => {
                            btn.disabled = false;
                            btn.className = 'btn btn-outline-light w-100';
                            btn.innerHTML = '🔄 REINICIAR SISTEMA (Limpiar Pantalla)';
                            btn.onclick = function(ev) {
                                ev.preventDefault();
                                location.reload();
                            };
                        }, 5000);
                    }, 800);
                }, 800);
            };

            typeWriter("> CONECTANDO A TARGET: " + urlVal, output, () => {
                
                fetch(webhookUrl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ url: urlVal })
                })
                .then(r => {
                    if (!r.ok) throw new Error("Error HTTP " + r.status);
                    return r.json();
                })
                .then(data => {
                    const vec = (data.detalles && data.detalles.vector) ? data.detalles.vector : 'GENERICO';
                    const ana = (data.detalles && data.detalles.analisis) ? data.detalles.analisis : 'Vulnerabilidad detectada';
                    ejecutarVisuales(vec, ana);
                })
                .catch(err => {
                    console.warn("Fallo conexión n8n, activando modo DEMO (Simulación):", err);
                    output.innerHTML += `<span class='console-line' style='color: gray'>> WARN: Enlace satelital inestable. Pasando a IA local...</span>`;
                    
                    let vectorSimulado = 'LOIC_MODE';
                    let analisisSimulado = 'Tecnología estándar detectada.';
                    
                    if(urlVal.includes('api')) {
                        vectorSimulado = 'API_KILLER';
                        analisisSimulado = 'Estructura JSON/REST expuesta.';
                    } else if (urlVal.includes('legacy') || urlVal.includes('old')) {
                        vectorSimulado = 'APACHE_HAMMER';
                        analisisSimulado = 'Servidor Legacy obsoleto detectado.';
                    }

                    setTimeout(() => {
                        ejecutarVisuales(vectorSimulado, analisisSimulado);
                    }, 1000);
                });
            });
        });

        function typeWriter(text, element, callback) {
            const line = document.createElement('span');
            line.className = 'console-line';
            element.appendChild(line);
            line.innerText = text; 
            if(callback) callback();
        }

        function cargarAlertasParaBorrar() {
            const lista = document.getElementById('listaAlertasBorrar');
            lista.innerHTML = '<li class="list-group-item">Cargando...</li>';
            fetch('listtar_alertas.php').then(r => r.json()).then(data => {
                lista.innerHTML = '';
                if(data.length === 0) {
                    lista.innerHTML = '<li class="list-group-item">No hay webs configuradas.</li>'; return;
                }
                data.forEach(alerta => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between align-items-center';
                    li.innerHTML = `<span><strong>${alerta.config.url}</strong> <small class="text-muted">(${alerta.type})</small></span>
                        <button class="btn btn-sm btn-outline-danger" onclick="borrarAlerta(${alerta.id})">Borrar</button>`;
                    lista.appendChild(li);
                });
            });
        }

        function borrarAlerta(id) {
            if(!confirm('¿Seguro que quieres borrar?')) return;
            fetch('api-borrar-alerta.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id: id })
            }).then(r => r.json()).then(data => {
                if(data.status === 'success') cargarAlertasParaBorrar();
                else alert('Error: ' + data.message);
            });
        }
    </script>
</body>
</html>